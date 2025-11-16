<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'code', 'type', 'project_id'
    ];

    // Store Types
    const TYPE_MAIN = 'main';
    const TYPE_PROJECT = 'project';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function storeReleases()
    {
        return $this->hasMany(StoreRelease::class);
    }

    public function requisitions()
    {
        return $this->hasMany(Requisition::class);
    }

    // Check if this is a project store
    public function isMainStore()
    {
        return $this->type === self::TYPE_MAIN;
    }

    // Check if this is the main store
    public function isProjectStore()
    {
        return $this->type === self::TYPE_PROJECT;
    }

    // Get store display name
    public function getDisplayNameAttribute()
    {
        if ($this->isProjectStore() && $this->project) {
            return $this->project->name . " Store";
        }
        return $this->name;
    }

    // Get total items count in store
    public function getTotalItemsCount()
    {
        return $this->inventoryItems()->count();
    }

    // Get total quantity in store
    public function getTotalQuantity()
    {
        return $this->inventoryItems()->sum('quantity');
    }

    // Get low stock items
    public function getLowStockItems()
    {
        return $this->inventoryItems()
            ->whereRaw('quantity < reorder_level')
            ->get();
    }

    // Get out of stock items
    public function getOutOfStockItems()
    {
        return $this->inventoryItems()
            ->where('quantity', '<=', 0)
            ->get();
    }

    // Get store value (total cost of inventory)
    public function getStoreValue()
    {
        return $this->inventoryItems()
            ->selectRaw('SUM(quantity * unit_price) as total_value')
            ->value('total_value') ?? 0;
    }

    // Scope methods
    public function scopeMainStore($query)
    {
        return $query->where('type', self::TYPE_MAIN);
    }

    public function scopeProjectStores($query)
    {
        return $query->where('type', self::TYPE_PROJECT);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    // Get stores accessible by user
    public function scopeAccessibleBy($query, $user)
    {
        return $query->where(function($q) use ($user) {
            // Main store - accessible to main store manager (user ID 6)
            $q->where(function($mainQuery) use ($user) {
                $mainQuery->where('type', self::TYPE_MAIN)
                         ->where(function($subQuery) use ($user) {
                             $subQuery->where('id', 1) // Main store ID
                                     ->where('user.id', 6); // Main store manager
                         });
            })
            // Project stores - accessible to users assigned to those projects
            ->orWhere(function($projectQuery) use ($user) {
                $projectQuery->where('type', self::TYPE_PROJECT)
                           ->whereHas('project.users', function($userQuery) use ($user) {
                               $userQuery->where('user_id', $user->id);
                           });
            });
        });
    }
}