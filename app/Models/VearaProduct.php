<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VearaProduct extends Model
{
    protected $guarded = [];

    protected $casts = [
        'style_tags' => 'array',
        'subject_matter' => 'array',
        'placement' => 'array',
        'target_audience_guess' => 'array',
        'seasonal_fit' => 'array',
        'color_palette' => 'array',
        'design_labels' => 'array',
        'complexity_score' => 'integer',
        'text_present' => 'boolean',
        'pet_relevance_score' => 'float',
        'label_confidence' => 'float',
        'vectorized' => 'boolean',
        'labeled_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    public function labels()
    {
        return $this->belongsToMany(DesignLabel::class, 'veara_product_labels')
            ->withTimestamps();
    }
}
