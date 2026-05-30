<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Garment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'print_area_specs' => 'array',
    ];

    public function garmentVariants()
    {
        return $this->hasMany(GarmentVariant::class, 'garment_id');
    }
}
