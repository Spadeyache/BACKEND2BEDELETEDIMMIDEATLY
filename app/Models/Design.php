<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'print_files' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designImages()
    {
        return $this->hasMany(DesignRender::class, 'design_id');
    }
}
