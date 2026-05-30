<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VearaProducts extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'float',
        'complexity_score' => 'integer',
        'pet_relevance_score' => 'float',
        'color_palette' => 'array',
        'labeled_at' => 'datetime',
    ];
}
