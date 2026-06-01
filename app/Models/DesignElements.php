<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignElements extends Model
{
    protected $guarded = [];

    protected $casts = [
        'design_labels' => 'array',
        'x_position' => 'float',
        'y_position' => 'float',
        'width' => 'float',
        'height' => 'float',
        'scale' => 'float',
        'angle' => 'float',
        'font_size' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function design_render()
    {
        return $this->belongsTo(DesignRender::class, 'design_render_id');
    }
}
