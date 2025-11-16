<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'category', 'unit_price', 'unit', 
        'quantity', 'reorder_level', 'track_per_project', 'store_id', 'project_id'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'track_per_project' => 'boolean',
    ];

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
}