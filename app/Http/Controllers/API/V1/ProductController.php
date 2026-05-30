<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\PrintifyGetAllProducts;
use App\Helpers\PrintifyGetOneProduct;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Products\JustDesignedGetResource;
use App\Http\Resources\Api\V1\Products\ProductDetailsGetResource;
use App\Http\Resources\Api\V1\Products\ProductGetResource;
use App\Models\Design;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;
    //
    public function index(Request $request, PrintifyGetAllProducts $printify_live) 
    {
        try {
            // $type = $request->type; // tshirt or hoodie
            $page = $request->get('page', 1);

            if ($request->has('product_type')){
                $all_products = $printify_live->allProducts($page, $request->product_type);
            } else {
                $all_products = $printify_live->allProducts($page);
            }

            // $filtered = $all_products->filter(function ($product) use ($type) {
            //     return str_contains(strtolower($product['title']), strtolower($type));
            // });

            // $all_products->values()
            $request->merge(['auth_user_id' => auth()->id()]);
            $product_data = [
                'products' => ProductGetResource::collection(collect($all_products['data'])),
                'pagination' => [
                    'current_page' => $all_products['current_page'],
                    'last_page' => $all_products['last_page'],
                    'per_page' => $all_products['per_page'] ?? 50,
                ]
            ];
            return $this->sendResponse($product_data,'All products from printify fetched.',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function productDetail(string $id, PrintifyGetOneProduct $printify_live)
    {
        try {
            $product_details = $printify_live->oneProduct($id);
            
            return $this->sendResponse(new ProductDetailsGetResource($product_details),'Individual product details fetched.',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function justDesignedProduct(string $id, PrintifyGetOneProduct $printify_live)
    {
        try {
            $product_details = $printify_live->oneProduct($id);
            
            return $this->sendResponse(new JustDesignedGetResource($product_details),'Individual product details fetched.',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
    
    public function productTags()
    {
        try {
            // $product_details = $printify->allProducts();
            $tags = [
                'All Products'  => null,
                'T Shirt'       => 't-shirt',
                'Shirt'         => 'shirt',
                'Hoodie'        => 'hoodies',
                'Sweatshirts'   => 'Sweatshirts'
            ];

            $data = collect($tags)->map(function ($value, $key) {
                return ['name' => $key, 'value' => $value];
            })->values()->all();

            return $this->sendResponse($data, 'Catalog Details Sent', 200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
