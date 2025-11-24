<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LpoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpo_id',
        'product_catalog_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'notes',
        'has_vat'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'has_vat' => 'boolean',
    ];

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }

    public function productCatalog()
    {
        return $this->belongsTo(ProductCatalog::class);
    }

    public function receivedItems()
    {
        return $this->hasMany(LpoReceivedItem::class, 'lpo_item_id');
    }

    // Get total quantity received for this item
    public function getTotalReceivedAttribute()
    {
        return $this->receivedItems()->sum('quantity_received');
    }

    // Check if item is fully received
    public function getIsFullyReceivedAttribute()
    {
        return $this->total_received >= $this->quantity;
    }
}