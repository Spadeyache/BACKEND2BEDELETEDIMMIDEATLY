<?php
namespace App\Helpers;

use App\Models\Design;
use App\Models\GarmentVariant;
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
        $this->shopId   = config('services.printify.shop_id');
        $this->apiToken = config('services.printify.token');
    }

    /**
     * Upload an image URL to Printify and return its Printify image ID.
     */
    public function uploadImage(string $url, string $fileName = 'design.png'): string
    {
        $response = Http::withToken($this->apiToken)
            ->post("{$this->baseUrl}/uploads/images.json", [
                'file_name' => $fileName,
                'url'       => $url,
            ]);

        if ($response->failed()) {
            throw new \Exception('Printify image upload failed: ' . $response->body());
        }

        return $response->json('id');
    }

    /**
     * Create a Printify product for a given design + garment variant.
     * Returns the Printify product ID.
     */
    public function createProduct(Design $design, GarmentVariant $variant): string
    {
        $garment    = $variant->garment;
        $variantId  = (int) $variant->printify_variant_id;
        $priceCents = $variant->price_cents;

        // Build print_areas from design renders
        $printAreas = [];
        $renders    = $design->designImages; // DesignRender collection

        foreach ($renders as $render) {
            $imageId = $this->uploadImage($render->image_url, "{$render->area_name}_design.png");

            $printAreas[] = [
                'variant_ids'  => [$variantId],
                'placeholders' => [
                    [
                        'position' => $render->area_name, // "front" or "back"
                        'images'   => [
                            [
                                'id'    => $imageId,
                                'x'     => 0.5,
                                'y'     => 0.5,
                                'scale' => 1,
                                'angle' => 0,
                            ],
                        ],
                    ],
                ],
            ];
        }

        $payload = [
            'title'             => "Custom Design #{$design->id}",
            'description'       => '',
            'blueprint_id'      => $garment->blueprint_id,
            'print_provider_id' => $garment->print_provider_id,
            'variants'          => [
                [
                    'id'         => $variantId,
                    'price'      => $priceCents,
                    'is_enabled' => true,
                ],
            ],
            'print_areas' => $printAreas,
        ];

        $response = Http::withToken($this->apiToken)
            ->post("{$this->baseUrl}/shops/{$this->shopId}/products.json", $payload);

        if ($response->failed()) {
            throw new \Exception('Printify product creation failed: ' . $response->body());
        }

        return $response->json('id');
    }

    /**
     * Submit an order to Printify after Stripe payment is confirmed.
     */
    public function createOrder(Order $order, ?array $shipping): string
    {
        $items = $order->order_item->map(fn($item) => [
            'product_id'        => $item->printify_product_id,
            'variant_id'        => (int) $item->printify_variant_id,
            'quantity'          => $item->quantity,
        ])->toArray();

        $payload = [
            'external_id'                => (string) $order->id,
            'label'                      => "Order #{$order->id}",
            'line_items'                 => $items,
            'shipping_method'            => 1,
            'send_shipping_notification' => false,
            'address_to'                 => [
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

        \Log::info('Printify order payload', ['payload' => $payload]);

        $response = Http::withToken($this->apiToken)
            ->post("{$this->baseUrl}/shops/{$this->shopId}/orders.json", $payload);

        \Log::info('Printify order response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->failed()) {
            throw new \Exception('Printify order creation failed: ' . $response->body());
        }

        return $response->json('id');
    }
}
