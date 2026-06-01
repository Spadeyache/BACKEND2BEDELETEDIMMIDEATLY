<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'design_id',
        'veara_product_id',
        'garment_variant_id',
        'printify_product_id',
        'printify_variant_id',
        'quantity',
        'price',
        'image',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
        'veara_product_id' => 'integer',
        'garment_variant_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function garmentVariant()
    {
        return $this->belongsTo(GarmentVariant::class, 'garment_variant_id');
    }
}
