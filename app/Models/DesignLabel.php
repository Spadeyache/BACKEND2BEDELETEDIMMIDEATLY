<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignLabel extends Model
{
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(DesignLabelGroup::class, 'design_label_group_id');
    }

    public function vearaProducts()
    {
        return $this->belongsToMany(DesignCatalogProduct::class, 'design_catalog_product_labels');
    }
}
