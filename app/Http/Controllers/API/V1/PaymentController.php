<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\PrintifyService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payment\CheckoutRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Http\Resources\Api\V1\Payment\CartDetailsResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Design;
use App\Models\GarmentVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    use ApiResponse;
    //
    public function index(string $id)
    {
        try {
            $user = auth()->user();
            $cart_items = CartItem::where('cart_id', $id)->get();
            $cart = Cart::select('shipping_cost')->findOrFail($id);

            $sub_total = 0;
            foreach ($cart_items as $cart_item) {
                $sub_total += ($cart_item->quantity * $cart_item->price);
            }

            $total = $sub_total + $cart->shipping_cost;
            
            $data = [
                'cart_items' => CartDetailsResource::collection($cart_items),
                'subtotal' => $sub_total,
                'shipping_cost' => $cart->shipping_cost,
                'total' => $total,
                'user' => new UserResource($user)
            ];

            return $this->sendResponse($data, 'Proceed to checkout page with data retrieved successfully',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function checkout(CheckoutRequest $request)
    {
        try {
            $user_id = auth()->id();
            $data    = $request->validated();
            $items   = $data['items'];

            $totalPrice = collect($items)->sum(fn($i) => $i['price'] * $i['quantity']);
            $stripe     = new StripeClient(config('services.stripe.secret'));
            $printify   = new PrintifyService();

            // Build Stripe line items
            $lineItems = collect($items)->map(fn($i) => [
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => (int) round($i['price'] * 100),
                    'product_data' => ['name' => $i['name']],
                ],
                'quantity' => $i['quantity'],
            ])->toArray();

            DB::beginTransaction();

            $order = Order::create([
                'user_id'           => $user_id,
                'stripe_payment_id' => '',
                'total_price'       => $totalPrice,
                'status'            => 'pending',
                'created_by'        => $user_id,
            ]);

            foreach ($items as $item) {
                $design  = isset($item['design_id']) ? Design::findOrFail($item['design_id']) : null;
                $variant = GarmentVariant::with('garment')->findOrFail($item['garment_variant_id']);

                $printifyProductId = null;
                $printifyVariantId = $variant->printify_variant_id;

                if ($design) {
                    // If the design doesn't have a Printify product yet, create one now
                    if (empty($design->printify_product_id)) {
                        $printifyProductId = $printify->createProduct($design, $variant);
                        $design->update(['printify_product_id' => $printifyProductId]);
                    } else {
                        $printifyProductId = $design->printify_product_id;
                    }
                }

                OrderItem::create([
                    'order_id'            => $order->id,
                    'design_id'           => $item['design_id'] ?? null,
                    'veara_product_id'    => $item['veara_product_id'] ?? null,
                    'garment_variant_id'  => $item['garment_variant_id'],
                    'printify_product_id' => $printifyProductId,
                    'printify_variant_id' => $printifyVariantId,
                    'quantity'            => $item['quantity'],
                    'price'               => $item['price'],
                    'image'               => $item['image'] ?? null,
                    'created_by'          => $user_id,
                ]);
            }

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items'           => $lineItems,
                'mode'                 => 'payment',
                'success_url'          => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'           => route('payment.cancel'),
                'metadata'             => ['order_id' => $order->id, 'user_id' => $user_id],
            ]);

            $order->update(['stripe_session_id' => $session->id]);

            // Cache shipping for use in the Stripe webhook
            cache()->put("order_shipping_{$order->id}", $request->input('shipping'), now()->addHours(2));

            DB::commit();

            return $this->sendResponse([
                'checkout_url' => $session->url,
                'order_id'     => $order->id,
            ], 'Order created successfully', 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            abort(404);
        }

        try {
            // Retrieve Stripe session
            $session = Session::retrieve($sessionId);

            $orderId = $session->metadata->order_id ?? null;
            // $packageId = $session->metadata->package_id ?? null;

            // if (!$orderId || !$packageId) {
            //     abort(404);
            // }

            // Get promotion history
            $history = Order::with('order_item')->findOrFail($orderId);

            if (!$history) {
                abort(404);
            }

            // Get package info
            // $package = PromotedPackage::find($packageId);

            return view('backend.layout.stripe.success', [
                'history' => $history,
                // 'package' => $package,
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe success page error', [
                'message' => $e->getMessage()
            ]);

            return view('backend.layout.stripe.success', [
                'history' => null,
                'package' => null,
                'error' => 'Unable to verify payment.'
            ]);
        }
    }

    public function cancel()
    {
        return view('backend.layout.stripe.cancel');
    }
}
