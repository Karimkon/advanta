<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Approval status constants - use these consistently throughout the application
    const APPROVAL_PENDING = 'pending_ceo';
    const APPROVAL_APPROVED = 'ceo_approved';
    const APPROVAL_REJECTED = 'ceo_rejected';

    // Payment status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Boot method to synchronize approval fields
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure ceo_approved boolean stays in sync with approval_status
        static::saving(function ($payment) {
            // If approval_status is being set, sync ceo_approved
            if ($payment->isDirty('approval_status')) {
                $payment->ceo_approved = $payment->approval_status === self::APPROVAL_APPROVED;
            }
            // If ceo_approved is being set directly, sync approval_status
            elseif ($payment->isDirty('ceo_approved')) {
                if ($payment->ceo_approved && $payment->approval_status !== self::APPROVAL_APPROVED) {
                    $payment->approval_status = self::APPROVAL_APPROVED;
                } elseif (!$payment->ceo_approved && $payment->approval_status === self::APPROVAL_APPROVED) {
                    $payment->approval_status = self::APPROVAL_PENDING;
                }
            }
        });
    }

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
        'approval_status',
        'ceo_approved',
        'ceo_approved_by', 
        'ceo_approved_at',
        'ceo_notes',
        'payment_voucher_path'
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
        return $this->belongsTo(Supplier::class)->withDefault([
            'name' => 'Unknown Supplier'
        ]);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by')->withDefault([
            'name' => 'Unknown User'
        ]);
    }

    public function ceoApprovedBy()
    {
        return $this->belongsTo(User::class, 'ceo_approved_by')->withDefault([
            'name' => 'N/A'
        ]);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function lpo()
    {
        return $this->belongsTo(Lpo::class)->withDefault([
            'lpo_number' => 'N/A',
            'requisition' => new Requisition() // Fallback empty requisition
        ]);
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

    // Scope for pending CEO approval
    public function scopePendingCeoApproval($query)
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
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

    // In Payment.php model
public function getTotalWithVatAttribute()
{
    return $this->amount + $this->vat_amount;
}

public function getBaseAmountAttribute()
{
    return $this->amount - $this->vat_amount;
}

    // NEW: Check if payment is pending CEO approval
    public function isPendingCeoApproval()
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    // NEW: Check if payment is CEO approved
    public function isCeoApproved()
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    // NEW: Check if payment is CEO rejected
    public function isCeoRejected()
    {
        return $this->approval_status === self::APPROVAL_REJECTED;
    }

    // NEW: Get approval status badge class
    public function getApprovalStatusBadgeClass()
    {
        return match($this->approval_status) {
            self::APPROVAL_PENDING => 'warning',
            self::APPROVAL_APPROVED => 'success',
            self::APPROVAL_REJECTED => 'danger',
            default => 'secondary'
        };
    }

    // NEW: Get approval status text
    public function getApprovalStatusText()
    {
        return match($this->approval_status) {
            self::APPROVAL_PENDING => 'Pending CEO Approval',
            self::APPROVAL_APPROVED => 'CEO Approved',
            self::APPROVAL_REJECTED => 'CEO Rejected',
            default => 'Unknown'
        };
    }

    // Scope for approved payments
    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    // Scope for rejected payments
    public function scopeRejected($query)
    {
        return $query->where('approval_status', self::APPROVAL_REJECTED);
    }

    // Scope for payments by status
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Scope for payments by approval status
    public function scopeByApprovalStatus($query, string $approvalStatus)
    {
        return $query->where('approval_status', $approvalStatus);
    }

    /**
     * Approve the payment (CEO action)
     */
    public function approve(?int $approvedById = null, ?string $notes = null): bool
    {
        $this->approval_status = self::APPROVAL_APPROVED;
        $this->ceo_approved = true;
        $this->ceo_approved_by = $approvedById ?? auth()->id();
        $this->ceo_approved_at = now();
        $this->ceo_notes = $notes;
        $this->status = self::STATUS_COMPLETED;

        return $this->save();
    }

    /**
     * Reject the payment (CEO action)
     */
    public function reject(?int $rejectedById = null, ?string $notes = null): bool
    {
        $this->approval_status = self::APPROVAL_REJECTED;
        $this->ceo_approved = false;
        $this->ceo_approved_by = $rejectedById ?? auth()->id();
        $this->ceo_approved_at = now();
        $this->ceo_notes = $notes;
        $this->status = self::STATUS_FAILED;

        return $this->save();
    }

    /**
     * Get unified status for display (combines status and approval_status)
     */
    public function getDisplayStatus(): string
    {
        if ($this->approval_status === self::APPROVAL_REJECTED) {
            return 'Rejected';
        }
        if ($this->approval_status === self::APPROVAL_PENDING) {
            return 'Pending Approval';
        }
        if ($this->approval_status === self::APPROVAL_APPROVED) {
            return 'Approved';
        }
        return ucfirst($this->status ?? 'Unknown');
    }

    /**
     * Get unified status color for UI
     */
    public function getDisplayStatusColor(): string
    {
        if ($this->approval_status === self::APPROVAL_REJECTED) {
            return 'danger';
        }
        if ($this->approval_status === self::APPROVAL_PENDING) {
            return 'warning';
        }
        if ($this->approval_status === self::APPROVAL_APPROVED) {
            return 'success';
        }
        return 'secondary';
    }
}