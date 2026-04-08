<?php

namespace App\Http\Resources\Api\V1\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemGetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'cart_id'               => $this->cart_id,
            'design_id'             => $this->design_id,
            'printify_product_id'   => $this->printify_product_id,
            'printify_variant_id'   => $this->printify_variant_id,
            'quantity'              => $this->quantity,
            'price'                 => $this->price,
            'product_name'          => $this->product_name,
            'product_size'          => $this->product_size,
            'product_color'         => $this->product_color,
            'product_front_image'   => $this->product_front_image,
            'created_by'            => $this->created_by
        ];
    }
}
