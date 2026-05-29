<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Design\DesignRenderGetResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignPageGetResource extends JsonResource
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
            'user_id'               => $this->user_id,
            'user_name'             => $this->user->first_name . ' ' . $this->user->last_name,
            'user_email'            => $this->user->email,
            'printify_product_id'   => $this->printify_product_id,
            // 'printify_variant_id'   => $this->printify_variant_id,
            // 'product_name'          => $this->product_name,
            // 'product_size'          => $this->product_size,
            // 'product_color'         => $this->product_color,
            'veara_product_id'      => $this->veara_product_id,
            'mockup_image'          => $this->mockup_image,
            'created_at'            => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'            => $this->updated_at->format('Y-m-d H:i:s'),
            'design_images'         => DesignRenderGetResource::collection($this->designImages),
        ];
    }
}
