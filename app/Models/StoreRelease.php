<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id', 'store_id', 'released_by', 'released_at',
        'status', 'notes'
    ];

    protected $casts = [
        'released_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_RELEASED = 'released';
    const STATUS_PARTIAL = 'partial';
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
}