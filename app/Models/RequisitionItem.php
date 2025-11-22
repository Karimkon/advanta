<?php
// app/Models/RequisitionItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'product_catalog_id', // NEW: Link to product catalog
        'name',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'notes',
        'from_store'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'from_store' => 'boolean',
    ];

    // Relationships
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function productCatalog()
    {
        return $this->belongsTo(ProductCatalog::class);
    }

    // Accessor for backward compatibility
    public function getNameAttribute($value)
    {
        return $value ?? $this->productCatalog?->name;
    }

    public function getUnitAttribute($value)
    {
        return $value ?? $this->productCatalog?->unit;
    }

    // Automatically set name and unit from product catalog
    public function setProductCatalogIdAttribute($value)
    {
        $this->attributes['product_catalog_id'] = $value;
        if ($value && $product = ProductCatalog::find($value)) {
            $this->attributes['name'] = $product->name;
            $this->attributes['unit'] = $product->unit;
        }
    }
}