<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignRender extends Model
{
    //
    protected $guarded = [];

    public function design()
    {
        return $this->belongsTo(Design::class);
    }
}
