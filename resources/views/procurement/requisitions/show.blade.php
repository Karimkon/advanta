@extends('procurement.layouts.app')

@section('title', 'Requisition ' . $requisition->ref)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Requisition: {{ $requisition->ref }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('procurement.requisitions.index') }}">Requisitions</a></li>
                    <li class="breadcrumb-item active">{{ $requisition->ref }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('procurement.requisitions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            @if(in_array($requisition->status, ['procurement', 'ceo_approved']))
                <a href="{{ route('procurement.requisitions.edit', $requisition) }}" 
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
                            <p><strong>Requested By:</strong> {{ $requisition->requester->name }}</p>
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
            <!-- Procurement Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Procurement Actions</h5>
                </div>
                <div class="card-body">
                    @if($requisition->status === 'operations_approved')
                        <div class="alert alert-warning">
                            <strong>Step 1:</strong> Start procurement process for this requisition.
                        </div>
                        
                        <form action="{{ route('procurement.requisitions.start-procurement', $requisition) }}" method="POST" class="d-grid mb-3">
                            @csrf
                            <button type="submit" class="btn btn-warning" 
                                    onclick="return confirm('Start procurement process for this requisition?')">
                                <i class="bi bi-gear"></i> Start Procurement Process
                            </button>
                        </form>

                   @elseif($requisition->status === 'procurement')
    <!-- STEP 2: Send to CEO for Approval -->
    <div class="alert alert-info">
        <strong>Step 2:</strong> Create LPO with VAT configuration and send to CEO for approval.
    </div>

    <!-- Show any errors or success messages -->
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Form Errors:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- NEW: Link to dedicated LPO creation page with VAT configuration -->
    <div class="text-center">
        <a href="{{ route('procurement.requisitions.create-lpo-page', $requisition) }}" 
           class="btn btn-success btn-lg w-100">
            <i class="bi bi-receipt"></i> Create LPO with VAT Configuration
        </a>
        
        <p class="text-muted mt-2 small">
            <i class="bi bi-info-circle"></i>
            You'll be able to configure VAT for each item before sending to CEO
        </p>
    </div>
                    @elseif($requisition->status === 'ceo_approved')
                        <div class="alert alert-success">
                            <strong>CEO Approved:</strong> Ready to issue LPO to supplier.
                        </div>
                        
                        @if($requisition->lpo)
                            <div class="text-center">
                                <a href="{{ route('procurement.lpos.show', $requisition->lpo) }}" 
                                   class="btn btn-outline-primary w-100 mb-2">
                                    <i class="bi bi-eye"></i> View LPO
                                </a>
                                
                                @if($requisition->lpo->status === 'draft')
                                    <form action="{{ route('procurement.lpos.issue', $requisition->lpo) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100"
                                                onclick="return confirm('Issue LPO to supplier?')">
                                            <i class="bi bi-send"></i> Issue LPO to Supplier
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif

                    @elseif($requisition->status === 'lpo_issued')
                        <div class="alert alert-primary">
                            <strong>LPO Issued:</strong> Waiting for supplier delivery.
                            <br><small class="text-muted">Store personnel will confirm delivery when items are received.</small>
                        </div>
                        
                        @if($requisition->lpo)
                            <a href="{{ route('procurement.lpos.show', $requisition->lpo) }}" class="btn btn-info w-100 mb-2">
                                <i class="bi bi-eye"></i> View LPO
                            </a>
                        @endif

                    @elseif($requisition->status === 'delivered')
                        <div class="alert alert-success">
                            <strong>Delivered:</strong> Items received by store.
                        </div>
                        
                    @else
                        <div class="alert alert-secondary">
                            <strong>Status:</strong> {{ $requisition->getCurrentStage() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- LPO Information -->
            @if($requisition->lpo)
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">LPO Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>LPO Number:</strong> {{ $requisition->lpo->lpo_number }}</p>
                        <p><strong>Supplier:</strong> {{ $requisition->lpo->supplier->name ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $requisition->lpo->status === 'issued' ? 'success' : ($requisition->lpo->status === 'delivered' ? 'info' : 'warning') }}">
                                {{ ucfirst($requisition->lpo->status) }}
                            </span>
                        </p>
                        @if($requisition->lpo->delivery_date)
                            <p><strong>Delivery Date:</strong> {{ $requisition->lpo->delivery_date->format('M d, Y') }}</p>
                        @else
                            <p><strong>Delivery Date:</strong> <span class="text-muted">Not set</span></p>
                        @endif
                        <a href="{{ route('procurement.lpos.show', $requisition->lpo) }}" class="btn btn-outline-primary btn-sm w-100">
                            View LPO Details
                        </a>
                    </div>
                </div>
            @endif

            <!-- Status Timeline -->
            <div class="card shadow-sm mt-4">
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
</style>
@endsection