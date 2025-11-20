<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref', 'project_id', 'requested_by', 'urgency', 'status', 
        'estimated_total', 'reason', 'attachments', 'type', 'store_id'
    ];

    protected $casts = [
        'estimated_total' => 'decimal:2',
        'attachments' => 'array',
    ];

    // Requisition Types
    const TYPE_STORE = 'store';       // From project store (on-site) - Engineer initiates
    const TYPE_PURCHASE = 'purchase'; // New purchase (office) - Project Manager initiates

    // Status constants - Complete workflow
    const STATUS_PENDING = 'pending';                    // Initial state
    const STATUS_PROJECT_MANAGER_APPROVED = 'project_manager_approved'; // Store requisitions approved
    const STATUS_OPERATIONS_APPROVED = 'operations_approved'; // Purchase requisitions approved by operations
    const STATUS_PROCUREMENT = 'procurement';            // Sent to procurement
    const STATUS_CEO_APPROVED = 'ceo_approved';          // CEO approves purchase
    const STATUS_LPO_ISSUED = 'lpo_issued';              // LPO created for supplier
    const STATUS_DELIVERED = 'delivered';                // Items delivered by supplier
    const STATUS_PAYMENT_COMPLETED = 'payment_completed';
    const STATUS_COMPLETED = 'completed';                // All done
    const STATUS_REJECTED = 'rejected';                  // Rejected at any stage

    // Urgency constants
    const URGENCY_LOW = 'low';
    const URGENCY_MEDIUM = 'medium';
    const URGENCY_HIGH = 'high';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function approvals()
    {
        return $this->hasMany(RequisitionApproval::class);
    }

   public function lpo()
{
    return $this->hasOne(Lpo::class)->latest();
}

    public function storeRelease()
    {
        return $this->hasOne(StoreRelease::class);
    }

    // Helper methods
    public function isStoreRequisition()
    {
        return $this->type === self::TYPE_STORE;
    }

    public function isPurchaseRequisition()
    {
        return $this->type === self::TYPE_PURCHASE;
    }

    public function getNextApprovalRole()
    {
        if ($this->isStoreRequisition()) {
            return match($this->status) {
                self::STATUS_PENDING => 'project_manager',
                self::STATUS_PROJECT_MANAGER_APPROVED => 'stores',
                default => null
            };
        } else {
            return match($this->status) {
                self::STATUS_PENDING => 'project_manager',
                self::STATUS_PROJECT_MANAGER_APPROVED => 'operations',
                self::STATUS_OPERATIONS_APPROVED => 'procurement',
                self::STATUS_PROCUREMENT => 'ceo',
                self::STATUS_CEO_APPROVED => 'procurement', // Back to procurement for LPO
                default => null
            };
        }
    }

    public function canBeApprovedBy($role)
    {
        $currentRole = $this->getNextApprovalRole();
        return $currentRole === $role;
    }

    public function canBeCreatedBy($userRole)
    {
        if ($this->isStoreRequisition()) {
            return in_array($userRole, ['engineer', 'project_manager']);
        } else {
            return $userRole === 'project_manager';
        }
    }

    public function getWorkflowDescription()
    {
        if ($this->isStoreRequisition()) {
            return "Engineer → Project Manager → Store Officer";
        } else {
            return "Project Manager → Operations → Procurement → CEO → Supplier → Finance";
        }
    }

    public function getCurrentStage()
    {
        return match($this->status) {
            self::STATUS_PENDING => $this->isStoreRequisition() ? 'Waiting for Project Manager' : 'Waiting for Project Manager',
            self::STATUS_PROJECT_MANAGER_APPROVED => $this->isStoreRequisition() ? 'Ready for Store Release' : 'Waiting for Operations',
            self::STATUS_OPERATIONS_APPROVED => 'Waiting for Procurement',
            self::STATUS_PROCUREMENT => 'Waiting for CEO Approval',
            self::STATUS_CEO_APPROVED => 'Ready for LPO Creation',
            self::STATUS_LPO_ISSUED => 'Waiting for Supplier Delivery',
            self::STATUS_DELIVERED => 'Waiting for Finance',
            self::STATUS_PAYMENT_COMPLETED => 'Payment Completed',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REJECTED => 'Rejected',
            
              // Handle any legacy or unexpected statuses
            'approved' => 'Approved',
            'in_progress' => 'In Progress',
            'processing' => 'Processing',
            'issued' => 'LPO Issued',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            
            default => 'Processing'
        };
    }

    public function supplier()
{
    return $this->hasOneThrough(
        Supplier::class,
        Lpo::class,
        'requisition_id', // Foreign key on LPO table
        'id',             // Foreign key on Supplier table  
        'id',             // Local key on Requisition table
        'supplier_id'     // Local key on LPO table
    );
}

    // Scope methods
    public function scopeStoreRequisitions($query)
    {
        return $query->where('type', self::TYPE_STORE);
    }

    public function scopePurchaseRequisitions($query)
    {
        return $query->where('type', self::TYPE_PURCHASE);
    }

    public function scopePendingApproval($query, $role)
    {
        $nextStatus = match($role) {
            'project_manager' => self::STATUS_PENDING,
            'operations' => self::STATUS_PROJECT_MANAGER_APPROVED,
            'procurement' => self::STATUS_OPERATIONS_APPROVED,
            'ceo' => self::STATUS_PROCUREMENT,
            'stores' => self::STATUS_PROJECT_MANAGER_APPROVED,
            default => null
        };

        return $nextStatus ? $query->where('status', $nextStatus) : $query;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_PROJECT_MANAGER_APPROVED => 'bg-info',
            self::STATUS_OPERATIONS_APPROVED => 'bg-primary',
            self::STATUS_PROCUREMENT => 'bg-secondary',
            self::STATUS_CEO_APPROVED => 'bg-success',
            self::STATUS_LPO_ISSUED => 'bg-info',
            self::STATUS_DELIVERED => 'bg-primary',
             self::STATUS_PAYMENT_COMPLETED => 'bg-success',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            
             // Handle legacy statuses
            'approved' => 'bg-success',
            'in_progress' => 'bg-info',
            'processing' => 'bg-primary',
            'issued' => 'bg-info',
            'paid' => 'bg-success',
            'cancelled' => 'bg-danger',
            
            default => 'bg-secondary'
        };
    }

    public function getUrgencyBadgeClass()
    {
        return match($this->urgency) {
            self::URGENCY_LOW => 'bg-success',
            self::URGENCY_MEDIUM => 'bg-warning',
            self::URGENCY_HIGH => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function canBeEdited()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeDeleted()
    {
        return $this->status === self::STATUS_PENDING;
    }
}