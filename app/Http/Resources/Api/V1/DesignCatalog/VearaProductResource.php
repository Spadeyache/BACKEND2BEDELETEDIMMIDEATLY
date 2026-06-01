<?php

namespace App\Http\Resources\Api\V1\DesignCatalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VearaProductResource extends JsonResource
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
            'source_product_id' => $this->source_product_id,
            'source_labeled_product_id' => $this->source_labeled_product_id,
            'title' => $this->title,
            'source_url' => $this->source_url,
            'source_domain' => $this->source_domain,
            'front_image_url' => $this->front_image_url,
            'back_image_url' => $this->back_image_url,
            'front_mockup_url' => $this->front_mockup_url,
            'back_mockup_url' => $this->back_mockup_url,
            'price_range' => $this->price_range,
            'design_type' => $this->design_type,
            'mood' => $this->mood,
            'style_tags' => $this->style_tags ?? [],
            'subject_matter' => $this->subject_matter ?? [],
            'placement' => $this->placement ?? [],
            'target_audience_guess' => $this->target_audience_guess ?? [],
            'seasonal_fit' => $this->seasonal_fit ?? [],
            'color_palette' => $this->color_palette,
            'design_labels' => $this->design_labels,
            'complexity_score' => $this->complexity_score,
            'text_present' => $this->text_present,
            'pet_relevance_score' => $this->pet_relevance_score,
            'label_confidence' => $this->label_confidence,
            'review_source' => $this->review_source,
            'vectorized' => $this->vectorized,
            'embedding_model' => $this->embedding_model,
            'status' => $this->status,
            'labels' => DesignLabelResource::collection($this->whenLoaded('labels')),
            'labeled_at' => $this->labeled_at?->format('Y-m-d H:i:s'),
            'imported_at' => $this->imported_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
