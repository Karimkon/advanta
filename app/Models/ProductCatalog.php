<?php
// app/Models/ProductCatalog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCatalog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'unit',
        'sku',
        'is_active',
        'specifications'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function requisitionItems()
    {
        return $this->hasMany(RequisitionItem::class, 'product_catalog_id');
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class, 'product_catalog_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return $this->name . ($this->sku ? " ({$this->sku})" : '');
    }

    public function getUsageCountAttribute()
    {
        return $this->requisitionItems()->count();
    }

    public function canBeDeleted()
    {
        return $this->requisitionItems()->count() === 0 && 
               $this->inventoryItems()->count() === 0;
    }
}