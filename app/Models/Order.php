<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'total_price' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order_item()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
