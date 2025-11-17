<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreReleaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_release_id',
        'inventory_item_id',
        'requisition_item_id',
        'quantity_requested',
        'quantity_released',
        'notes'
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_released' => 'decimal:2',
    ];

    public function storeRelease()
    {
        return $this->belongsTo(StoreRelease::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function requisitionItem()
    {
        return $this->belongsTo(RequisitionItem::class);
    }
}