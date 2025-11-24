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
        'notes',
        'vat_amount',
        'additional_costs',
        'additional_costs_description',
        'approval_status'
         
    ];
 protected $casts = [
        'amount' => 'decimal:2',
        'additional_costs' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'paid_on' => 'date',
        'additional_costs_description' => 'string', 
        'ceo_approved' => 'boolean',
        'ceo_approved_at' => 'datetime',
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

    // Accessor for safe date formatting
    public function getFormattedPaidOnAttribute()
    {
        return $this->paid_on ? $this->paid_on->format('M d, Y') : 'Not set';
    }

    // Scope for payments with dates
    public function scopeWithDates($query)
    {
        return $query->whereNotNull('paid_on');
    }

    // NEW: Get payment breakdown
     public function getPaymentBreakdown()
    {
        $subtotal = $this->amount - $this->vat_amount - $this->additional_costs;
        
        return [
            'subtotal' => $subtotal,
            'vat_amount' => $this->vat_amount,
            'additional_costs' => $this->additional_costs,
            'additional_costs_description' => $this->additional_costs_description,
            'total' => $this->amount,
        ];
    }

     // NEW: Check if payment has additional costs
    public function hasAdditionalCosts()
    {
        return $this->additional_costs > 0;
    }

     // NEW: Get formatted additional costs description
    public function getFormattedAdditionalCosts()
    {
        if ($this->additional_costs > 0) {
            $description = $this->additional_costs_description ?: 'Additional Costs';
            return "{$description}: UGX " . number_format($this->additional_costs, 2);
        }
        return null;
    }

    // NEW: Check if payment includes VAT
    public function hasVat()
    {
        return $this->vat_amount > 0;
    }

    // NEW: Get VAT percentage
    public function getVatPercentage()
    {
        if ($this->vat_amount > 0 && $this->amount > 0) {
            $subtotal = $this->amount - $this->vat_amount;
            return $subtotal > 0 ? ($this->vat_amount / $subtotal) * 100 : 0;
        }
        return 0;
    }
}