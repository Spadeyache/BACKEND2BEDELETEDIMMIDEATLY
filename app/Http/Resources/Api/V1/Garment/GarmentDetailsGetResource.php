<?php

namespace App\Http\Resources\Api\V1\Garment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GarmentDetailsGetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            // 'blueprint_id' => $this->blueprint_id,
            // 'print_provider_id' => $this->print_provider_id,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            // 'print_area_specs' => $this->print_area_specs,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'garment_variants' => GarmentVariantResource::collection($this->whenLoaded('garmentVariants')),
        ];
    }
}
