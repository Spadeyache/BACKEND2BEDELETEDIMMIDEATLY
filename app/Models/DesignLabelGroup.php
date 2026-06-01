<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignLabelGroup extends Model
{
    protected $guarded = [];

    public function labels()
    {
        return $this->hasMany(DesignLabel::class);
    }
}
