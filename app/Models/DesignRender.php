<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignRender extends Model
{
    protected $guarded = [];

    protected $casts = [
        'created_by' => 'integer',
    ];

    public function design()
    {
        return $this->belongsTo(Design::class);
    }
}
