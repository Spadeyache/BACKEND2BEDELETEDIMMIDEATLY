<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignElements extends Model
{
    //
    protected $guarded = [];

    public function design_render()
    {
        return $this->belongsTo(DesignRender::class, 'design_render_id');
    }
}
