<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'lpo_id', 
        'supplier_id',
        'paid_by',
        'payment_method',
        'status',
        'amount',
        'paid_on',
        'reference',
        'notes'
    ];

    protected $casts = [
        'paid_on' => 'date',
        'amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function lpo()
    {
        return $this->belongsTo(Lpo::class);
    }

    // Add relationship to get requisition through LPO
    public function requisition()
    {
        return $this->hasOneThrough(
            Requisition::class,
            Lpo::class,
            'id',           // Foreign key on LPO table
            'id',           // Foreign key on Requisition table  
            'lpo_id',       // Local key on Payment table
            'requisition_id' // Local key on LPO table
        );
    }

    // Alternative: Direct access to requisition through LPO
    public function getRequisitionAttribute()
    {
        return $this->lpo->requisition ?? null;
    }
}