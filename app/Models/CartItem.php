<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'cart_id',
        'design_id',
        'veara_product_id',
        'garment_variant_id',
        'printify_product_id',
        'quantity',
        'price',
        'product_name',
        'product_size',
        'product_color',
        'product_front_image',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
        'veara_product_id' => 'integer',
        'garment_variant_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function garmentVariant()
    {
        return $this->belongsTo(GarmentVariant::class, 'garment_variant_id');
    }

    public function vearaProduct()
    {
        return $this->belongsTo(VearaProducts::class, 'veara_product_id');
    }

    public function getPrintifyVariantIdAttribute()
    {
        return $this->garmentVariant?->printify_variant_id;
    }
}
