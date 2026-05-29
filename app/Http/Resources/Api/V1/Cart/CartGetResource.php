<?php

namespace App\Http\Resources\Api\V1\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartGetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 'session_id' => $this->session_id,
            'session_id' => $this->session_id,
            'status' => $this->status,
            // 'created_at' => $this->created_at
        ];
    }
}
