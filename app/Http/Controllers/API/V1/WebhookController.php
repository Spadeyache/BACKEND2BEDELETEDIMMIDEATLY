<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\PrintifyService;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    use ApiResponse;
    // , PrintifyService $printify
    public function stripe(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;
            $userId = $session->metadata->user_id ?? null;

            if (!$orderId) {
                return response()->json(['error' => 'Missing order_id in metadata'], 400);
            }

            $order = Order::find($orderId);

            if (!$order || $order->status !== 'pending') {
                return response()->json(['status' => 'skipped']);
            }

            $shipping        = cache()->get("order_shipping_{$order->id}");
            // $printifyOrderId = $printify->createOrder($order, $shipping);

            DB::beginTransaction();

            try {
                $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();
                $cart->update([
                    'status' => 'completed'
                ]);

                $order->update([
                    'stripe_payment_id' => $session->id,
                    // 'stripe_payment_id' => $session->payment_intent, // Stripe still gives you this
                    // 'printify_order_id' => $printifyOrderId,
                    'status'            => 'paid',
                ]);

                cache()->forget("order_shipping_{$order->id}");

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function printify(Request $request)
    {
        $type     = $request->input('type');
        $resource = $request->input('resource');

        if ($type === 'order:status:changed') {
            $printifyOrderId = $resource['id'] ?? null;
            $newStatus       = $resource['status'] ?? null;

            if ($printifyOrderId && $newStatus) {
                $order = Order::where('printify_order_id', $printifyOrderId)->first();
                $order->update(['status' => $newStatus]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
