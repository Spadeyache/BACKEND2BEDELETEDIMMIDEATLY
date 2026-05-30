<?php

namespace App\Http\Resources\Api\V1\Garment;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GarmentGetResource extends JsonResource
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
            'display_order' => $this->display_order,
            // 'print_area_specs' => $this->print_area_specs,
            'starting_price_cents' => $this->starting_price_cents !== null 
                ? (int) $this->starting_price_cents 
                : null,
            'blank_mockup_url' => $this->garmentVariants->map(function ($variant) {
                return $variant->blank_mockup_url ? helpers::generateTempURL($variant->blank_mockup_url, config('app.file_system')) : null;
            })->filter()->values()->all(),
        ];
    }
}
