<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lpo extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpo_number',
        'requisition_id',
        'supplier_id',
        'prepared_by', // UNCOMMENT THIS
        'issued_by',   // UNCOMMENT THIS
        'issued_at',
        'status',
        'subtotal',
        'tax',
        'other_charges',
        'total',
        'delivery_date',
        'terms',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
        'delivery_date' => 'datetime',
    ];

    // UNCOMMENT THESE RELATIONSHIPS
    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // Keep other relationships
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function items()
    {
        return $this->hasMany(LpoItem::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}