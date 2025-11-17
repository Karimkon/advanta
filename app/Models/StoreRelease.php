<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'store_id', 
        'released_by',
        'released_at',
        'status',
        'notes'
    ];

    protected $casts = [
        'released_at' => 'datetime',
    ];

    // Status constants
    const STATUS_RELEASED = 'released';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PENDING = 'pending';
    const STATUS_CANCELLED = 'cancelled';

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function items()
    {
        return $this->hasMany(StoreReleaseItem::class);
    }

    public function getTotalQuantityReleasedAttribute()
    {
        return $this->items->sum('quantity_released');
    }

    public function getTotalValueAttribute()
    {
        $total = 0;
        foreach ($this->items as $item) {
            if ($item->inventoryItem) {
                $total += $item->quantity_released * $item->inventoryItem->unit_price;
            }
        }
        return $total;
    }

    public function isFullySatisfied()
    {
        foreach ($this->items as $item) {
            if ($item->quantity_released < $item->quantity_requested) {
                return false;
            }
        }
        return true;
    }
}