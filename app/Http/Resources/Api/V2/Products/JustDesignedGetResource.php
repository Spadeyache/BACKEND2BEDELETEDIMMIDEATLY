<?php

namespace App\Http\Resources\Api\V2\Products;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JustDesignedGetResource extends JsonResource
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
            'product_id'            => $this->product_id,
            'title'                 => $this->title,
            'price'                 => $this->price,
            'description'           => $this->description,
            'veara_front'           => $this->veara_front ? helpers::generateTempURL($this->veara_front,config('app.file_system')) : null,
            'veara_back'            => $this->veara_back ? helpers::generateTempURL($this->veara_back,config('app.file_system')) : null,
            'front_mockup'          => $this->front_mockup ? helpers::generateTempURL($this->front_mockup,config('app.file_system')) : null,
            'back_mockup'           => $this->back_mockup ? helpers::generateTempURL($this->back_mockup,config('app.file_system')) : null,
            'style_tags'            => $this->style_tags,
            'color_palette'         => $this->color_palette,
            'design_type'           => $this->design_type,
            'category'              => $this->category,
            'subject_matter'        => $this->subject_matter,
            'mood'                  => $this->mood,
            'complexity_score'      => $this->complexity_score,
            'pet_relevance_score'   => $this->pet_relevance_score,
            'target_audience_guess' => $this->target_audience_guess,
            'seasonal_fit'          => $this->seasonal_fit,
            'embedding_model'       => $this->embedding_model,
            'labeled_at'            => $this->labeled_at,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
