<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Garment\GarmentGetResource;
use App\Http\Resources\Api\V2\Products\JustDesignedGetResource;
use App\Http\Resources\Api\V2\Products\ProductDetailsGetResource;
use App\Http\Resources\Api\V2\Products\ProductGetResource;
use App\Models\Garment;
use App\Models\GarmentVariant;
use App\Models\VearaProducts;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Get all products from the veara_products table with pagination (12 per page).
     */
    public function index(Request $request)
    {
        try {
            $query = VearaProducts::query();

            if ($request->has('product_type')) {
                $query->where('design_type', $request->product_type);
            }

            $all_products = $query->paginate(12);

            $request->merge(['auth_user_id' => auth()->id()]);

            $pagination = [
                'current_page'  => $all_products->currentPage(),
                'last_page'     => $all_products->lastPage(),
                'per_page'      => $all_products->perPage(),
                'total'         => $all_products->total(),
                'next_page_url' => $all_products->nextPageUrl(),
                'prev_page_url' => $all_products->previousPageUrl()
            ];

            $product_data = [
                'products'   => ProductGetResource::collection($all_products),
                'pagination' => $pagination
            ];

            return $this->sendResponse($product_data, 'All products fetched.', 200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Get a single product's full details from veara_products.
     */
    public function productDetail(string $id)
    {
        try {
            $product = VearaProducts::findOrFail($id);

            $category = $product->category;

            $query = Garment::query()
                ->where('is_active', true)
                ->with(['garmentVariants' => function ($q) {
                    $q->where('is_enabled', true);
                }])
                ->withMin(['garmentVariants as starting_price_cents' => function ($q) {
                    $q->where('is_enabled', true);
                }], 'price_cents')
                ->orderBy('display_order', 'asc');

            if ($category !== null && $category !== '') {
                $query->whereRaw('LOWER(category) = ?', [strtolower($category)]);
            }

            $garments = $query->get([
                'id',
                'name',
                'description',
                'category',
                'display_order',
                'print_area_specs'
            ]);

            $garmentVariants = GarmentVariant::whereIn(
                'garment_id',
                Garment::query()
                    ->where('is_active', true)
                    ->when($category !== null && $category !== '', function ($q) use ($category) {
                        $q->whereRaw('LOWER(category) = ?', [strtolower($category)]);
                    })
                    ->pluck('id')
            )
            ->where('is_enabled', true)
            ->get(['id', 'size', 'color', 'color_hex']);

            $data = [
                'product'           => new ProductDetailsGetResource($product),
                'garments'          => GarmentGetResource::collection($garments),
                'garment_variants'  => $garmentVariants
            ];

            return $this->sendResponse($data, 'Individual product details fetched.', 200);
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('Product not found.', [], 404);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Get a just-designed product's detail from veara_products.
     */
    public function justDesignedProduct(string $id)
    {
        try {
            $product = VearaProducts::findOrFail($id);

            $category = $product->category;

            $query = Garment::query()
                ->where('is_active', true)
                ->with(['garmentVariants' => function ($q) {
                    $q->where('is_enabled', true);
                }])
                ->withMin(['garmentVariants as starting_price_cents' => function ($q) {
                    $q->where('is_enabled', true);
                }], 'price_cents')
                ->orderBy('display_order', 'asc');

            if ($category !== null && $category !== '') {
                $query->whereRaw('LOWER(category) = ?', [strtolower($category)]);
            }

            $garments = $query->get([
                'id',
                'name',
                'description',
                'category',
                'display_order',
                'print_area_specs'
            ]);

            $data = [
                'product'  => new JustDesignedGetResource($product),
                'garments' => GarmentGetResource::collection($garments)
            ];

            return $this->sendResponse($data, 'Individual product details fetched.', 200);
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('Product not found.', [], 404);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
