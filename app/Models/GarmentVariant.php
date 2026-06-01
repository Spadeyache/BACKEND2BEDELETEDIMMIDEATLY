<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarmentVariant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price_cents' => 'integer',
        'is_enabled' => 'boolean',
        'display_order' => 'integer',
    ];

    public function garment()
    {
        return $this->belongsTo(Garment::class, 'garment_id');
    }
}
