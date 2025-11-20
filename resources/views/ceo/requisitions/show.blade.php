@extends('ceo.layouts.app')

@section('title', 'Requisition ' . $requisition->ref)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Requisition: {{ $requisition->ref }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('ceo.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ceo.requisitions.index') }}">Requisitions</a></li>
                    <li class="breadcrumb-item active">{{ $requisition->ref }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.requisitions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            @if(in_array($requisition->status, ['procurement', 'ceo_approved']))
                <a href="{{ route('ceo.requisitions.edit', $requisition) }}" 
                   class="btn btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Requisition Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Requisition Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Project:</strong> {{ $requisition->project->name ?? 'N/A' }}</p>
                            <p><strong>Type:</strong> 
                                <span class="badge bg-{{ $requisition->isStoreRequisition() ? 'info' : 'primary' }}">
                                    {{ $requisition->isStoreRequisition() ? 'Store Requisition' : 'Purchase Requisition' }}
                                </span>
                            </p>
                            <p><strong>Urgency:</strong> 
                                <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                    {{ ucfirst($requisition->urgency) }}
                                </span>
                            </p>
                            <p><strong>Requested By:</strong> {{ $requisition->requester->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                                </span>
                            </p>
                            <p><strong>Current Stage:</strong> {{ $requisition->getCurrentStage() }}</p>
                            <p><strong>Created:</strong> {{ $requisition->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Estimated Total:</strong> UGX {{ number_format($requisition->estimated_total, 2) }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <strong>Workflow:</strong>
                        <p class="mb-1">{{ $requisition->getWorkflowDescription() }}</p>
                    </div>

                    <div class="mt-3">
                        <strong>Reason/Purpose:</strong>
                        <p class="mt-1">{{ $requisition->reason }}</p>
                    </div>
                </div>
            </div>

            <!-- Original Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Original Requisition Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisition->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ number_format($item->quantity, 3) }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td>UGX {{ number_format($item->total_price, 2) }}</td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                    <td colspan="2"><strong>UGX {{ number_format($requisition->estimated_total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- LPO Information with VAT Details -->
            @if($requisition->lpo)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">LPO Information with VAT Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>LPO Number:</strong> {{ $requisition->lpo->lpo_number }}</p>
                            <p><strong>Supplier:</strong> {{ $requisition->lpo->supplier->name ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $requisition->lpo->status === 'issued' ? 'success' : 'warning' }}">
                                    {{ ucfirst($requisition->lpo->status) }}
                                </span>
                            </p>
                            <p><strong>Prepared By:</strong> {{ $requisition->lpo->preparer->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <!-- VAT Financial Summary -->
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Financial Summary with VAT</h6>
                                    <p class="mb-1"><strong>Subtotal (Excluding VAT):</strong> UGX {{ number_format($requisition->lpo->subtotal, 2) }}</p>
                                    <p class="mb-1"><strong>VAT Amount (18%):</strong> UGX {{ number_format($requisition->lpo->vat_amount, 2) }}</p>
                                    <p class="mb-1"><strong>Other Charges:</strong> UGX {{ number_format($requisition->lpo->other_charges, 2) }}</p>
                                    <p class="mb-0"><strong>Grand Total:</strong> UGX {{ number_format($requisition->lpo->total, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($requisition->lpo->delivery_date)
                        <p class="mt-3 mb-1"><strong>Delivery Date:</strong> {{ $requisition->lpo->delivery_date->format('M d, Y') }}</p>
                    @endif
                    @if($requisition->lpo->terms)
                        <p class="mb-1"><strong>Terms:</strong> {{ $requisition->lpo->terms }}</p>
                    @endif
                    @if($requisition->lpo->notes)
                        <p class="mb-0"><strong>Notes:</strong> {{ $requisition->lpo->notes }}</p>
                    @endif
                </div>
            </div>

            <!-- LPO Items with VAT Status -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">LPO Items with VAT Configuration</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Unit Price</th>
                                    <th>VAT Status</th>
                                    <th>Total (Excl. VAT)</th>
                                    <th>VAT Amount</th>
                                    <th>Total (Incl. VAT)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisition->lpo->items as $item)
                                    <tr>
                                        <td>{{ $item->description ?? 'No description' }}</td>
                                        <td>{{ number_format($item->quantity, 3) }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td>
                                            @if($item->has_vat)
                                                <span class="badge bg-success">18% VAT Included</span>
                                            @else
                                                <span class="badge bg-secondary">No VAT</span>
                                            @endif
                                        </td>
                                        <td>UGX {{ number_format($item->total_price, 2) }}</td>
                                        <td>
                                            @if($item->has_vat)
                                                UGX {{ number_format($item->total_price * 0.18, 2) }}
                                            @else
                                                UGX 0.00
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->has_vat)
                                                UGX {{ number_format($item->total_price * 1.18, 2) }}
                                            @else
                                                UGX {{ number_format($item->total_price, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong>UGX {{ number_format($requisition->lpo->subtotal, 2) }}</strong></td>
                                    <td><strong>UGX {{ number_format($requisition->lpo->vat_amount, 2) }}</strong></td>
                                    <td><strong>UGX {{ number_format($requisition->lpo->total, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="text-end small text-muted">
                                        VAT Summary: {{ $requisition->lpo->items->where('has_vat', true)->count() }} items with VAT, 
                                        {{ $requisition->lpo->items->where('has_vat', false)->count() }} items without VAT
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Approval History -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Approval History</h5>
                </div>
                <div class="card-body">
                    @forelse($requisition->approvals as $approval)
                        <div class="border-start border-3 border-primary ps-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong>{{ ucfirst($approval->action) }} by {{ $approval->approver->name ?? 'System' }}</strong>
                                <small class="text-muted">{{ $approval->created_at->format('M d, Y H:i') }}</small>
                            </div>
                            <small class="text-muted d-block">{{ ucfirst($approval->role) }}</small>
                            @if($approval->comment)
                                <p class="mb-0 mt-1">{{ $approval->comment }}</p>
                            @endif
                            @if($approval->approved_amount)
                                <small class="text-success">Approved Amount: UGX {{ number_format($approval->approved_amount, 2) }}</small>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted text-center">No approval history yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-4">
            <!-- CEO Approval Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">CEO Approval Actions</h5>
                </div>
                <div class="card-body">
                    @if($requisition->status === 'procurement')
                        <div class="alert alert-warning">
                            <strong>Pending Your Approval:</strong> Review and adjust items before approval.
                        </div>

                        <!-- Item-Level Approval Form -->
                        <form action="{{ route('ceo.requisitions.approve', $requisition) }}" method="POST" id="ceoApprovalForm">
                            @csrf
                            
                            <!-- Item Approval Controls -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Approve/Modify Items</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th width="5%" class="text-center">Approve</th>
                                                    <th width="25%">Item</th>
                                                    <th width="10%">Req Qty</th>
                                                    <th width="12%">Approve Qty</th>
                                                    <th width="8%">Unit</th>
                                                    <th width="15%">Unit Price</th>
                                                    <th width="15%">Total</th>
                                                    <th width="10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($requisition->items as $index => $item)
                                                <tr class="item-row" data-item-id="{{ $item->id }}">
                                                    <td class="text-center">
                                                        <div class="form-check">
                                                            <input type="checkbox" 
                                                                   name="approved_items[{{ $item->id }}]" 
                                                                   value="1" 
                                                                   class="form-check-input item-approval-checkbox"
                                                                   data-item-id="{{ $item->id }}"
                                                                   checked>
                                                        </div>
                                                    </td>
                                                    <td class="small">{{ Str::limit($item->name, 20) }}</td>
                                                    <td class="text-center">{{ number_format($item->quantity, 3) }}</td>
                                                    <td>
                                                        <input type="number" 
                                                               name="approved_quantities[{{ $item->id }}]" 
                                                               class="form-control form-control-sm approved-quantity"
                                                               value="{{ $item->quantity }}"
                                                               min="0" 
                                                               max="{{ $item->quantity }}"
                                                               step="0.001"
                                                               data-original-quantity="{{ $item->quantity }}"
                                                               data-item-id="{{ $item->id }}"
                                                               data-unit-price="{{ $item->unit_price }}">
                                                    </td>
                                                    <td class="text-center small">{{ $item->unit }}</td>
                                                    <td class="small">UGX {{ number_format($item->unit_price, 2) }}</td>
                                                    <td>
                                                        <span class="approved-total small fw-bold text-success" data-item-id="{{ $item->id }}">
                                                            UGX {{ number_format($item->total_price, 2) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" 
                                                                class="btn btn-outline-danger btn-sm remove-item-btn"
                                                                data-item-id="{{ $item->id }}"
                                                                title="Remove this item">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Summary -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body py-3">
                                            <h6 class="card-title mb-2">Approval Summary</h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <p class="mb-1 small"><strong>Original Total:</strong></p>
                                                    <p class="mb-1 small"><strong>Approved Total:</strong></p>
                                                    <p class="mb-0 small"><strong>Items Approved:</strong></p>
                                                </div>
                                                <div class="col-6 text-end">
                                                    <p class="mb-1 small" id="original-total">UGX {{ number_format($requisition->estimated_total, 2) }}</p>
                                                    <p class="mb-1 small"><span id="approved-total" class="text-success fw-bold">UGX {{ number_format($requisition->estimated_total, 2) }}</span></p>
                                                    <p class="mb-0 small"><span id="approved-count" class="fw-bold">{{ $requisition->items->count() }}</span> of {{ $requisition->items->count() }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Approval Comments</label>
                                <textarea name="comment" id="comment" class="form-control" rows="2" 
                                          placeholder="Add comments about your approval decisions..."></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success" id="approveBtn">
                                    <i class="bi bi-check-circle"></i> Approve Selected Items
                                </button>
                                
                                <button type="button" class="btn btn-outline-success" id="approveAllBtn">
                                    <i class="bi bi-check-all"></i> Approve All Items
                                </button>
                            </div>
                        </form>

                        <hr>

                        <!-- Rejection Form -->
                        <form action="{{ route('ceo.requisitions.reject', $requisition) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="reject_comment" class="form-label">Rejection Reason (Required)</label>
                                <textarea name="comment" id="reject_comment" class="form-control" rows="2" 
                                          placeholder="Please provide reason for rejection..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Reject this entire requisition? This action cannot be undone.')">
                                <i class="bi bi-x-circle"></i> Reject Entire Requisition
                            </button>
                        </form>

                    @elseif($requisition->status === 'ceo_approved')
                        <div class="alert alert-success">
                            <strong>Approved:</strong> You have approved this requisition with modifications.
                        </div>
                        
                        <!-- Show Approval Details -->
                        @php
                            $lastApproval = $requisition->approvals->where('role', 'ceo')->where('action', 'approved')->last();
                        @endphp
                        
                        @if($lastApproval && $lastApproval->approved_amount != $requisition->estimated_total)
                            <div class="alert alert-info">
                                <strong>Modifications Made:</strong><br>
                                Original Amount: UGX {{ number_format($requisition->estimated_total, 2) }}<br>
                                Approved Amount: UGX {{ number_format($lastApproval->approved_amount, 2) }}
                            </div>
                        @endif
                        
                        @if($requisition->lpo)
                            <div class="text-center">
                                <a href="{{ route('ceo.lpos.show', $requisition->lpo) }}" 
                                   class="btn btn-outline-primary w-100 mb-2">
                                    <i class="bi bi-receipt"></i> View LPO with Approved Items
                                </a>
                            </div>
                        @endif

                    @elseif(in_array($requisition->status, ['lpo_issued', 'delivered', 'completed']))
                        <div class="alert alert-info">
                            <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                        </div>
                        
                    @elseif($requisition->status === 'rejected')
                        <div class="alert alert-danger">
                            <strong>Rejected:</strong> This requisition has been rejected.
                        </div>

                    @else
                        <div class="alert alert-info">
                            <strong>Status:</strong> {{ $requisition->getCurrentStage() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Status Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @php
                            $statuses = [
                                'pending' => ['icon' => '⏳', 'label' => 'Pending'],
                                'project_manager_approved' => ['icon' => '✅', 'label' => 'Project Manager Approved'],
                                'operations_approved' => ['icon' => '✅', 'label' => 'Operations Approved'],
                                'procurement' => ['icon' => '📦', 'label' => 'Procurement'],
                                'ceo_approved' => ['icon' => '👑', 'label' => 'CEO Approved'],
                                'lpo_issued' => ['icon' => '📄', 'label' => 'LPO Issued'],
                                'delivered' => ['icon' => '🚚', 'label' => 'Delivered'],
                                'completed' => ['icon' => '🎉', 'label' => 'Completed']
                            ];
                            
                            $currentStatusIndex = array_search($requisition->status, array_keys($statuses));
                        @endphp
                        
                        @foreach($statuses as $status => $info)
                            @php
                                $statusIndex = array_search($status, array_keys($statuses));
                                $isCompleted = $statusIndex < $currentStatusIndex;
                                $isCurrent = $status === $requisition->status;
                                $isFuture = $statusIndex > $currentStatusIndex;
                            @endphp
                            
                            <div class="timeline-item {{ $isCurrent ? 'active' : ($isCompleted ? 'completed' : '') }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <span class="me-2">{{ $info['icon'] }}</span>
                                    <strong>{{ $info['label'] }}</strong>
                                    @if($isCurrent)
                                        <small class="text-success ms-2">Current</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Summary</h5>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">{{ $requisition->items->count() }}</div>
                            <div class="stat-label">Items</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">UGX</div>
                            <div class="stat-label">{{ number_format($requisition->estimated_total, 0) }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ $requisition->created_at->format('M d') }}</div>
                            <div class="stat-label">Created</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    margin-bottom: 15px;
}

.timeline-marker {
    position: absolute;
    left: -20px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dee2e6;
    border: 2px solid #fff;
}

.timeline-item.active .timeline-marker {
    background: #6f42c1;
    border-color: #6f42c1;
}

.timeline-item.completed .timeline-marker {
    background: #198754;
    border-color: #198754;
}

.timeline-content {
    padding-bottom: 10px;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

/* CEO Approval System Styles */
.item-approval-checkbox:checked + label {
    font-weight: bold;
    color: #198754;
}

.approved-quantity:disabled {
    background-color: #f8f9fa;
    opacity: 0.6;
    cursor: not-allowed;
}

.remove-item-btn {
    transition: all 0.3s ease;
    padding: 0.25rem 0.5rem;
}

.remove-item-btn:hover {
    transform: scale(1.1);
    background-color: #dc3545;
    color: white;
}

#approved-total {
    font-weight: bold;
    font-size: 1.1em;
}

#approveBtn {
    transition: all 0.3s ease;
}

#approveBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Highlight modified items */
.approved-quantity.modified {
    border-color: #ffc107;
    background-color: #fff3cd;
}

/* Item row styling for removed items */
.item-row.removed {
    opacity: 0.6;
    background-color: #f8f9fa;
    text-decoration: line-through;
}

/* Stats grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    text-align: center;
}

.stat-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-value {
    font-size: 16px;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .approved-quantity {
        font-size: 0.75rem;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize approval system
    initializeCEOApprovalSystem();
    
    function initializeCEOApprovalSystem() {
        const form = document.getElementById('ceoApprovalForm');
        if (!form) return;
        
        // Calculate initial totals
        calculateApprovalSummary();
        
        // Event listeners for quantity changes
        document.querySelectorAll('.approved-quantity').forEach(input => {
            input.addEventListener('input', function() {
                updateItemTotal(this);
                calculateApprovalSummary();
                highlightModifiedItems();
            });
            
            input.addEventListener('change', function() {
                validateQuantity(this);
            });
        });
        
        // Event listeners for approval checkboxes
        document.querySelectorAll('.item-approval-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const itemId = this.dataset.itemId;
                const quantityInput = document.querySelector(`.approved-quantity[data-item-id="${itemId}"]`);
                const itemRow = document.querySelector(`.item-row[data-item-id="${itemId}"]`);
                
                if (!this.checked) {
                    // If unchecking, set quantity to 0 and disable
                    quantityInput.value = 0;
                    quantityInput.disabled = true;
                    itemRow.classList.add('removed');
                } else {
                    // If checking, restore original quantity
                    quantityInput.value = quantityInput.dataset.originalQuantity;
                    quantityInput.disabled = false;
                    itemRow.classList.remove('removed');
                }
                
                updateItemTotal(quantityInput);
                calculateApprovalSummary();
                highlightModifiedItems();
            });
        });
        
        // Remove item buttons
        document.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.dataset.itemId;
                const checkbox = document.querySelector(`.item-approval-checkbox[data-item-id="${itemId}"]`);
                const quantityInput = document.querySelector(`.approved-quantity[data-item-id="${itemId}"]`);
                const itemRow = document.querySelector(`.item-row[data-item-id="${itemId}"]`);
                
                // Uncheck and set quantity to 0
                checkbox.checked = false;
                quantityInput.value = 0;
                quantityInput.disabled = true;
                itemRow.classList.add('removed');
                
                updateItemTotal(quantityInput);
                calculateApprovalSummary();
                highlightModifiedItems();
                
                // Show feedback
                showToast('Item removed from approval', 'warning');
            });
        });
        
        // Approve all button
        document.getElementById('approveAllBtn').addEventListener('click', function() {
            document.querySelectorAll('.item-approval-checkbox').forEach(checkbox => {
                checkbox.checked = true;
                const itemId = checkbox.dataset.itemId;
                const quantityInput = document.querySelector(`.approved-quantity[data-item-id="${itemId}"]`);
                const itemRow = document.querySelector(`.item-row[data-item-id="${itemId}"]`);
                
                quantityInput.value = quantityInput.dataset.originalQuantity;
                quantityInput.disabled = false;
                itemRow.classList.remove('removed');
                
                updateItemTotal(quantityInput);
            });
            
            calculateApprovalSummary();
            highlightModifiedItems();
            
            // Show confirmation
            showToast('All items selected for approval with original quantities', 'success');
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            const approvedItems = document.querySelectorAll('.item-approval-checkbox:checked');
            const approvedTotal = parseFloat(document.getElementById('approved-total').textContent.replace(/[^\d.]/g, ''));
            
            if (approvedItems.length === 0) {
                e.preventDefault();
                showToast('Please approve at least one item before submitting.', 'error');
                return;
            }
            
            if (approvedTotal === 0) {
                e.preventDefault();
                showToast('Approved amount cannot be zero. Please adjust quantities.', 'error');
                return;
            }
            
            // Show confirmation with summary
            const confirmed = confirm(
                `APPROVAL CONFIRMATION\n\n` +
                `Items Approved: ${approvedItems.length}\n` +
                `Total Amount: UGX ${approvedTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}\n\n` +
                `This will update the LPO and proceed with procurement.\n\n` +
                `Click OK to confirm approval.`
            );
            
            if (!confirmed) {
                e.preventDefault();
            }
        });
        
        // Initial highlight check
        highlightModifiedItems();
    }
    
    function updateItemTotal(quantityInput) {
        const itemId = quantityInput.dataset.itemId;
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(quantityInput.dataset.unitPrice) || 0;
        const total = quantity * unitPrice;
        
        const totalElement = document.querySelector(`.approved-total[data-item-id="${itemId}"]`);
        if (totalElement) {
            totalElement.textContent = `UGX ${total.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
            totalElement.className = `approved-total small fw-bold ${total > 0 ? 'text-success' : 'text-danger'}`;
        }
    }
    
    function calculateApprovalSummary() {
        let approvedTotal = 0;
        let approvedCount = 0;
        
        document.querySelectorAll('.item-approval-checkbox').forEach(checkbox => {
            if (checkbox.checked) {
                const itemId = checkbox.dataset.itemId;
                const quantityInput = document.querySelector(`.approved-quantity[data-item-id="${itemId}"]`);
                const quantity = parseFloat(quantityInput.value) || 0;
                
                if (quantity > 0) {
                    approvedCount++;
                    
                    const unitPrice = parseFloat(quantityInput.dataset.unitPrice) || 0;
                    approvedTotal += quantity * unitPrice;
                }
            }
        });
        
        // Update display
        document.getElementById('approved-total').textContent = `UGX ${approvedTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        document.getElementById('approved-count').textContent = approvedCount;
        
        // Update button text and state
        const approveBtn = document.getElementById('approveBtn');
        if (approveBtn) {
            approveBtn.innerHTML = `<i class="bi bi-check-circle"></i> Approve ${approvedCount} Items (UGX ${approvedTotal.toLocaleString('en-US', {minimumFractionDigits: 2})})`;
            approveBtn.disabled = approvedCount === 0 || approvedTotal === 0;
        }
    }
    
    function highlightModifiedItems() {
        document.querySelectorAll('.approved-quantity').forEach(input => {
            const originalQty = parseFloat(input.dataset.originalQuantity);
            const currentQty = parseFloat(input.value) || 0;
            
            if (currentQty !== originalQty) {
                input.classList.add('modified');
            } else {
                input.classList.remove('modified');
            }
        });
    }
    
    function validateQuantity(input) {
        const maxQty = parseFloat(input.max);
        const currentQty = parseFloat(input.value) || 0;
        
        if (currentQty > maxQty) {
            input.value = maxQty;
            updateItemTotal(input);
            calculateApprovalSummary();
            showToast('Quantity cannot exceed requested amount', 'warning');
        }
    }
    
    function showToast(message, type = 'info') {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }
});
</script>
@endpush
@endsection