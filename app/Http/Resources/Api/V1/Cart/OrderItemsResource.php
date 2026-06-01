<?php

namespace App\Http\Resources\Api\V1\Cart;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "order_id" => $this->order_id,
            "design_id" => $this->design_id,
            "quantity" => $this->quantity,
            "price" => $this->price,
            "image" => $this->image  ? helpers::generateTempURL($this->image,config('app.file_system')) : null,
            "veara_product_id" => $this->veara_product_id,
            "garment_variant_id" => $this->garment_variant_id,
            "created_at" => $this->created_at->format('M d, Y'),
            "updated_at" => $this->updated_at->format('M d, Y')
        ];
    }
}
