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
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    use ApiResponse;
    //
    public function addToCart(CartProductInfoRequest $request, PrintifyGetOneProduct $pgop)
    {
        try {
            $user_id = auth()->id();
            $data = $request->validated();

            $color_size = explode('/', $data['variant_title']);
            $color = trim($color_size[0]);
            $size = trim($color_size[1]);
            
            DB::beginTransaction();
            
            $cart = Cart::firstOrCreate(
                ['user_id' => auth()->id(), 'status' => 'active'], // match both
                ['user_id' => auth()->id(), 'status' => 'active']  // create with these if not found
            );
            
            if(!$request->filled('front_image')) {
                $printify_product = $pgop->oneProduct($data['printify_product_id']);
                $printify_front_image = $printify_product['images'][0]['src'];
            }

            $cart_items = CartItem::create([
                'cart_id'               => $cart->id,
                'design_id'             => $data['design_id'] ?? null,
                'printify_product_id'   => $data['printify_product_id'],
                'printify_variant_id'   => $data['printify_variant_id'],
                'quantity'              => $data['quantity'],
                'price'                 => $data['variant_price'],
                'product_name'          => $data['product_name'],
                'product_size'          => $size,
                'product_color'         => $color,
                'product_front_image'   => $data['front_image'] ?? $printify_front_image,
                'created_by'            => $user_id
            ]);

            DB::commit();

            $datas = [
                'cart' => new CartGetResource($cart),
                'cart_items' => new CartItemGetResource($cart_items)
            ];
            
            return $this->sendResponse($datas,'Product added to cart',200);
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
