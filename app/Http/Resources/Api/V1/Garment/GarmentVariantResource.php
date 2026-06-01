<?php

namespace App\Http\Resources\Api\V1\Garment;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GarmentVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'color' => $this->color,
            'color_hex' => $this->color_hex,
            'blank_mockup_url' => $this->blank_mockup_url ? helpers::generateTempURL($this->blank_mockup_url,config('app.file_system')) : null,
            'price_cents' => $this->price_cents,
        ];
    }
}
