<?php

namespace App\Http\Resources\Api\V1\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyOrdersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'order_id'  => $this->id,
            'date'      => $this->created_at->format('M d, Y'),
            'status'    => $this->status,
            'amount'    => $this->total_price
        ];
    }
}
