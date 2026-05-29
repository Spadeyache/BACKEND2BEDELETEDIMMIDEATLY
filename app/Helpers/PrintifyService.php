<?php
namespace App\Helpers;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrintifyService
{
    protected string $baseUrl = 'https://api.printify.com/v1';
    protected string $shopId;
    protected string $apiToken;

    public function __construct()
    {
        $this->shopId = config('services.printify.shop_id');
        $this->apiToken = config('services.printify.token');
    }

    public function createOrder(Order $order, ?array $shipping): string
    {
        $items = $order->order_item->map(fn($item) => [
            'product_id' => $item->printify_product_id,
            'variant_id' => (int) $item->printify_variant_id,
            'quantity'   => $item->quantity,
            "external_id"=> "line-item-abc-001",
            'print_provider_id' => 1
        ])->toArray();/////////////////

        $payload = [
            'external_id' => (string) $order->id,  // your internal order ID
            'label'       => "Order #{$order->id}",
            'line_items'  => $items,
            'shipping_method' => 1,  // standard shipping — adjust as needed
            'send_shipping_notification' => false,
            'address_to'  => [
                'first_name' => $shipping['first_name'] ?? '',
                'last_name'  => $shipping['last_name'] ?? '',
                'address1'   => $shipping['address1'] ?? '',
                'country'    => $shipping['country'] ?? '',
                'region'     => $shipping['region'] ?? '',
                'city'       => $shipping['city'] ?? '',
                'zip'        => $shipping['zip'] ?? '',
                'email'      => $order->user->email,
                'phone'      => $order->user->phone,
            ],
        ];
        

        $response = Http::withToken($this->apiToken)
            ->post("{$this->baseUrl}/shops/{$this->shopId}/orders.json", $payload);

        if ($response->failed()) {
            // Log::error('Printify failed!' . $response);
            throw new \Exception('Printify order creation failed: ' . $response->body());
        }

        return $response->json('id');
    }
}
