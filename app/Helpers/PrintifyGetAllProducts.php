<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class PrintifyGetAllProducts
{
    /**
     * Stock market price API
     */
    protected string $base_url = 'https://api.printify.com/v1/shops/21494572/products.json';

    public function allProducts($page = 1, $type = null): array
    {
        $response = Http::withToken(config('services.printify.token'))->get($this->base_url, ['page' => $page]);

        $data = $response->json();

        $products = $data['data'] ?? [];

        $filteredProducts = array_values(array_filter($products, function ($product) {
            return !$product['is_deleted'];
        }));
        
        if ($type != null) {
            $filteredProducts = array_values(array_filter($filteredProducts, function ($product) use ($type) {
                $tags = is_array($product['tags'])
                    ? implode(',', $product['tags'])
                    : ($product['tags'] ?? '');

                return str_contains(strtolower($tags), strtolower($type));
            }));
        }

        $data['data'] = $filteredProducts;

        return $data;

        // return $response->json();
    }
}

// class PrintifyService
// {
//     protected string $baseUrl = 'https://api.printify.com/v1';

//     protected function client()
//     {
//         return Http::withToken(config('services.printify.token'));
//     }

//     public function products($shopId)
//     {
//         return $this->client()
//             ->get("$this->baseUrl/shops/$shopId/products.json")
//             ->json();
//     }
// }
