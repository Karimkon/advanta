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
        'total_price',
        'has_vat',
        'vat_rate'
    ];

        protected $casts = [
        'has_vat' => 'boolean',
        'vat_rate' => 'decimal:2'
    ];

    // Add this accessor to provide 'name' using 'description'
    public function getNameAttribute()
    {
        return $this->description;
    }

     // Calculate VAT amount for this item
    public function getVatAmountAttribute()
    {
        if (!$this->has_vat) {
            return 0;
        }
        return $this->total_price * ($this->vat_rate / 100);
    }

    // Get total including VAT
    public function getTotalWithVatAttribute()
    {
        return $this->total_price + $this->vat_amount;
    }

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }
}