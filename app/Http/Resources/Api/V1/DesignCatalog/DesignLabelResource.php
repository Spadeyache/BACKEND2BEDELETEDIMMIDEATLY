<?php

namespace App\Http\Resources\Api\V1\DesignCatalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignLabelResource extends JsonResource
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
            'key' => $this->key,
            'value' => $this->value,
            'name' => $this->name,
            'group' => [
                'id' => $this->group?->id,
                'key' => $this->group?->key,
                'name' => $this->group?->name,
                'kind' => $this->group?->kind,
            ],
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
