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

            <!-- Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Requisition Items</h5>
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
                                        <td>{{ $item->quantity }}</td>
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

            <!-- LPO Information -->
            @if($requisition->lpo)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">LPO Information</h5>
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
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Amount:</strong> UGX {{ number_format($requisition->lpo->total, 2) }}</p>
                                @if($requisition->lpo->delivery_date)
                                    <p><strong>Delivery Date:</strong> {{ $requisition->lpo->delivery_date->format('M d, Y') }}</p>
                                @endif
                                @if($requisition->lpo->notes)
                                    <p><strong>Notes:</strong> {{ $requisition->lpo->notes }}</p>
                                @endif
                            </div>
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
            <!-- CEO Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">CEO Actions</h5>
                </div>
                <div class="card-body">
                    @if($requisition->status === 'ceo_approved')
                        <div class="alert alert-info">
                            <strong>Ready for Final Approval:</strong> You can approve this requisition and issue the LPO.
                        </div>

                        <!-- Approve Requisition Form -->
                        <form action="{{ route('ceo.requisitions.approve', $requisition) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="mb-3">
                                <label for="comment" class="form-label">Approval Comment (Optional)</label>
                                <textarea name="comment" id="comment" class="form-control" rows="3" 
                                          placeholder="Add any comments for approval..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="approved_amount" class="form-label">Approved Amount</label>
                                <input type="number" name="approved_amount" id="approved_amount" 
                                       class="form-control" step="0.01" min="0"
                                       value="{{ $requisition->estimated_total }}"
                                       placeholder="Enter approved amount">
                            </div>
                            <button type="submit" class="btn btn-success w-100" 
                                    onclick="return confirm('Approve this requisition and issue LPO?')">
                                <i class="bi bi-check-circle"></i> Approve & Issue LPO
                            </button>
                        </form>

                        <hr>

                        <!-- Reject Requisition Form -->
                        <form action="{{ route('ceo.requisitions.reject', $requisition) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="reject_comment" class="form-label">Rejection Reason (Required)</label>
                                <textarea name="comment" id="reject_comment" class="form-control" rows="2" 
                                          placeholder="Please provide reason for rejection..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Reject this requisition? This action cannot be undone.')">
                                <i class="bi bi-x-circle"></i> Reject Requisition
                            </button>
                        </form>

                    @elseif($requisition->status === 'lpo_issued')
                        <div class="alert alert-success">
                            <strong>Approved & Issued:</strong> LPO has been issued to supplier.
                        </div>
                        
                        @if($requisition->lpo)
                            <div class="text-center">
                                <a href="{{ route('procurement.lpos.show', $requisition->lpo) }}" 
                                   class="btn btn-outline-primary w-100 mb-2" target="_blank">
                                    <i class="bi bi-eye"></i> View LPO Details
                                </a>
                            </div>
                        @endif

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
                        @endphp
                        
                        @foreach($statuses as $status => $info)
                            <div class="timeline-item {{ $requisition->status === $status ? 'active' : (array_search($status, array_keys($statuses)) < array_search($requisition->status, array_keys($statuses)) ? 'completed' : '') }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <span class="me-2">{{ $info['icon'] }}</span>
                                    <strong>{{ $info['label'] }}</strong>
                                    @if($requisition->status === $status)
                                        <small class="text-success ms-2">Current</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
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
</style>
@endsection