<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignCatalogProduct extends Model
{
    protected $table = 'design_catalog_products';

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
        return $this->belongsToMany(DesignLabel::class, 'design_catalog_product_labels')
            ->withPivot(['group_key', 'label_key'])
            ->withTimestamps();
    }
}
