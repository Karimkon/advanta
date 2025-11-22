<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'category', 'unit_price', 'unit', 
        'quantity', 'reorder_level', 'track_per_project', 'store_id', 'project_id',
        'product_catalog_id' // ADD THIS
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'track_per_project' => 'boolean',
    ];

    // Relationships
    public function logs()
    {
        return $this->hasMany(InventoryLog::class, 'inventory_item_id');
    }

    public function lpoItems()
    {
        return $this->hasMany(LpoItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ADD THIS RELATIONSHIP
    public function productCatalog()
    {
        return $this->belongsTo(ProductCatalog::class);
    }

    // Check if item is low stock
    public function getIsLowStockAttribute()
    {
        return $this->quantity < $this->reorder_level;
    }

    // Check if item is out of stock
    public function getIsOutOfStockAttribute()
    {
        return $this->quantity <= 0;
    }

    // Get stock status
    public function getStockStatusAttribute()
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        } elseif ($this->is_low_stock) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    // Get stock status badge class
    public function getStockStatusBadgeClass()
    {
        return match($this->stock_status) {
            'out_of_stock' => 'bg-danger',
            'low_stock' => 'bg-warning',
            'in_stock' => 'bg-success',
            default => 'bg-secondary'
        };
    }

    // ADD: Accessor for name to use product catalog name if available
    public function getNameAttribute($value)
    {
        return $value ?? $this->productCatalog?->name;
    }

    // ADD: Accessor for description to use product catalog description if available
    public function getDescriptionAttribute($value)
    {
        return $value ?? $this->productCatalog?->description;
    }

    // ADD: Accessor for unit to use product catalog unit if available
    public function getUnitAttribute($value)
    {
        return $value ?? $this->productCatalog?->unit;
    }
}