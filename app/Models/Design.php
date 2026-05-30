<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'print_files' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designImages()
    {
        return $this->hasMany(DesignRender::class, 'design_id');
    }

    public function vearaProduct()
    {
        return $this->belongsTo(VearaProducts::class, 'veara_product_id');
    }
}
