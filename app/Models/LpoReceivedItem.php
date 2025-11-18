<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LpoReceivedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpo_id',
        'lpo_item_id', 
        'quantity_ordered',
        'quantity_received',
        'condition',
        'received_by',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:2',
        'quantity_received' => 'decimal:2',
    ];

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }

    public function lpoItem()
    {
        return $this->belongsTo(LpoItem::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}