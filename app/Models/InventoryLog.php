<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id','project_id','user_id','type','quantity',
        'unit_price','balance_after','notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    // Add these relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Helper methods
    public function getMovementTypeBadgeClass()
    {
        return match($this->type) {
            'in' => 'bg-success',
            'out' => 'bg-warning', 
            'adjustment' => 'bg-info',
            default => 'bg-secondary'
        };
    }

    public function getMovementTypeIcon()
    {
        return match($this->type) {
            'in' => 'bi-arrow-down-circle',
            'out' => 'bi-arrow-up-circle',
            'adjustment' => 'bi-arrow-left-right',
            default => 'bi-question-circle'
        };
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getQuantityWithSign()
    {
        return match($this->type) {
            'in' => '+' . $this->quantity,
            'out' => '-' . $this->quantity,
            'adjustment' => '±' . $this->quantity,
            default => $this->quantity
        };
    }
}