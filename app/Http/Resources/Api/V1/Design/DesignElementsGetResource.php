<?php

namespace App\Http\Resources\Api\V1\Design;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignElementsGetResource extends JsonResource
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
            'design_render_id' => $this->design_render_id,
            'type' => $this->type,
            'content' => $this->content,
            'x_position' => $this->x_position,
            'y_position' => $this->y_position,
            'width' => $this->width,
            'height' => $this->height,
            'font_family' => $this->font_family,
            'font_size' => $this->font_size,
            'color' => $this->color,
            'status' => $this->status,
        ];
    }
}
