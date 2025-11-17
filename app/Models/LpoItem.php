<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LpoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpo_id',
        'inventory_item_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total_price'
    ];

    // Add this accessor to provide 'name' using 'description'
    public function getNameAttribute()
    {
        return $this->description;
    }

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }
}