<?php
// app/Models/ProductCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'parent_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(ProductCatalog::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }

    // Helper methods
    public function getFullPathAttribute()
    {
        $path = [];
        $category = $this;
        
        while ($category) {
            $path[] = $category->name;
            $category = $category->parent;
        }
        
        return implode(' > ', array_reverse($path));
    }

    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    public function canBeDeleted()
    {
        return $this->products()->count() === 0 && $this->children()->count() === 0;
    }

      // Auto-generate slug
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                // Ensure slug is unique
                $originalSlug = $category->slug;
                $counter = 1;
                while (static::where('slug', $category->slug)->exists()) {
                    $category->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                // Ensure slug is unique
                $originalSlug = $category->slug;
                $counter = 1;
                while (static::where('slug', $category->slug)->where('id', '!=', $category->id)->exists()) {
                    $category->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    
}