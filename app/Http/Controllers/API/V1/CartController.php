<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\helpers;
use App\Helpers\PrintifyGetOneProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\CartProductInfoRequest;
use App\Http\Requests\Api\V1\Cart\UpdateQuantityRequest;
use App\Http\Resources\Api\V1\Cart\CartDetailsResource;
use App\Http\Resources\Api\V1\Cart\CartGetResource;
use App\Http\Resources\Api\V1\Cart\CartItemGetResource;
use App\Http\Resources\Api\V1\Cart\MyOrdersResource;
use App\Http\Resources\Api\V1\Design\DesignElementsGetResource;
use App\Http\Resources\Api\V1\Design\DesignGetResource;
use App\Http\Resources\Api\V1\Design\DesignRenderGetResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Design;
use App\Models\DesignElements;
use App\Models\DesignRender;
use App\Models\Order;
use App\Models\VearaProducts;
use App\Models\GarmentVariant;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    use ApiResponse;
    //
    public function addToCart(CartProductInfoRequest $request)
    {
        try {
            $user_id = auth()->id();
            $data = $request->validated();

            $veara_product_id = $data['veara_product_id'];
            $garment_variant_id = $data['garment_variant_id'];
            $quantity = $data['quantity'];

            // 2. Look up the garment_variant by garment_variant_id
            $garmentVariant = GarmentVariant::find($garment_variant_id);
            if (!$garmentVariant || !$garmentVariant->is_enabled) {
                return $this->sendError('Garment variant not found or disabled.', [], 404);
            }

            // 3. Look up the veara_product by veara_product_id
            $vearaProduct = VearaProducts::find($veara_product_id);
            if (!$vearaProduct) {
                return $this->sendError('Veara product not found.', [], 404);
            }

            DB::beginTransaction();

            $cart = Cart::firstOrCreate(
                ['user_id' => $user_id, 'status' => 'active'],
                ['user_id' => $user_id, 'status' => 'active']
            );

            // 4. Check if a cart_item already exists in that cart with the same veara_product_id AND garment_variant_id
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('veara_product_id', $veara_product_id)
                ->where('garment_variant_id', $garment_variant_id)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                // Find latest Design of user for this Veara Product
                $design = Design::where('user_id', $user_id)
                    ->where('veara_product_id', $veara_product_id)
                    ->latest()
                    ->first();

                $productFrontImage = null;
                if ($design) {
                    $designRender = DesignRender::where('design_id', $design->id)
                        ->where('area_name', 'front')
                        ->first();
                    if ($designRender) {
                        $productFrontImage = $designRender->image_url;
                    }
                }

                // If not found, use front_mockup or veara_front from veara product
                if (!$productFrontImage) {
                    $productFrontImage = $vearaProduct->veara_front ?? $vearaProduct->front_mockup;
                }

                $cartItem = CartItem::create([
                    'cart_id'             => $cart->id,
                    'design_id'           => $design ? $design->id : null,
                    'veara_product_id'    => $veara_product_id,
                    'garment_variant_id'  => $garment_variant_id,
                    'quantity'            => $quantity,
                    'price'               => $garmentVariant->price_cents / 100,
                    'product_name'        => $vearaProduct->title,
                    'product_size'        => $garmentVariant->size,
                    'product_color'       => $garmentVariant->color,
                    'product_front_image' => $productFrontImage,
                    'printify_product_id' => null,
                    'created_by'          => $user_id,
                ]);
            }

            DB::commit();

            $datas = [
                'cart' => new CartGetResource($cart),
                'cart_items' => new CartItemGetResource($cartItem)
            ];

            return $this->sendResponse($datas, 'Product added to cart', 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function myOrders()
    {
        try {
            $user_id = auth()->id();

            $my_orders = Order::select('id', 'created_at', 'status', 'total_price')->where('user_id', $user_id)->latest()->get();

            return $this->sendResponse(MyOrdersResource::collection($my_orders), 'Order details retrieved successfully',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function cartDetails()
    {
        try {
            $user_id = auth()->id();

            $cart = Cart::where('user_id', $user_id)->where('status', 'active')->latest()->first();

            if (!$cart) {
                return $this->sendResponse([], 'You currently dont have an active cart',200);
            }

            $cart_items = CartItem::where('cart_id', $cart->id)->get();

            if (!isset($cart_items) || $cart_items->isEmpty()) {
                return $this->sendResponse([], 'Your cart is currently empty',200);
            }

            $subtotal = 0;
            foreach ($cart_items as $cart_item) {
                $subtotal += ($cart_item->quantity * $cart_item->price);
            }

            $total = $subtotal + $cart->shipping_cost;

            $data = [
                'cart_items' => CartDetailsResource::collection($cart_items),
                'subtotal' => $subtotal,
                'shipping_cost' => $cart->shipping_cost,
                'total' => $total
            ];

            return $this->sendResponse($data, 'Cart details retrieved successfully',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function removeFromCart(string $id)
    {
        try {
            $user_id = auth()->id();
            $cart_item = CartItem::findOrFail($id);
            
            $cart_item->update([
                'deleted_by' => $user_id
            ]);

            $cart_item->delete();

            return $this->sendResponse(new CartDetailsResource($cart_item), 'Cart item removed successfully',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function updateQuantity(UpdateQuantityRequest $request)
    {
        try {
            $cartIds   = $request->cart_id;
            $quantities = $request->quantity;

            foreach ($cartIds as $index => $cartItemId) {
                CartItem::where('id', $cartItemId)->update([
                    'quantity' => $quantities[$index],
                ]);
            }

            return $this->sendResponse([], 'Cart item quantity updated',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
