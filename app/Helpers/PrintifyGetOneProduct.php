<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class PrintifyGetOneProduct
{
    /**
     * Stock market price API
     */
    protected string $base_url = 'https://api.printify.com/v1/shops/27482335/products';

    public function oneProduct($product_id): array
    {
        $response = Http::withToken(config('services.printify.token'))->get("{$this->base_url}/{$product_id}.json");

        return $response->json();
    }
}
