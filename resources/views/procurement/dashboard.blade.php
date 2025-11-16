@extends('procurement.layouts.app')

@section('title', 'Procurement Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Welcome, {{ auth()->user()->name }}</h2>
            <p class="text-muted mb-0">Procurement Dashboard - Manage suppliers and create LPOs</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('procurement.requisitions.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Requisitions
                @if($requisitionStats->pending_procurement > 0)
                    <span class="badge bg-danger ms-1">{{ $requisitionStats->pending_procurement }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Pending Procurement</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $requisitionStats->pending_procurement }}</h2>
                            <small class="text-warning">Waiting processing</small>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-clock fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">In Procurement</h6>
                            <h2 class="fw-bold text-info mb-1">{{ $requisitionStats->in_procurement }}</h2>
                            <small class="text-info">Being processed</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-gear fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">LPOs Issued</h6>
                            <h2 class="fw-bold text-success mb-1">{{ $lpoStats->issued }}</h2>
                            <small class="text-success">Sent to suppliers</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-receipt fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Processed</h6>
                            <h2 class="fw-bold text-primary mb-1">{{ $requisitionStats->total }}</h2>
                            <small class="text-muted">All requisitions</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Requisitions -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock text-warning me-2"></i>
                        Requisitions Pending Processing
                    </h5>
                    <a href="{{ route('procurement.requisitions.pending') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref No.</th>
                                    <th>Project</th>
                                    <th>Amount</th>
                                    <th>Urgency</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingRequisitions as $requisition)
                                    <tr>
                                        <td><strong>{{ $requisition->ref }}</strong></td>
                                        <td>{{ $requisition->project->name }}</td>
                                        <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                                {{ ucfirst($requisition->urgency) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('procurement.requisitions.show', $requisition) }}" 
                                               class="btn btn-sm btn-outline-warning">Process</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                                            No pending requisitions for processing.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- In Procurement -->
            @if($procurementRequisitions->count() > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Currently in Procurement</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref No.</th>
                                    <th>Project</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($procurementRequisitions as $requisition)
                                    <tr>
                                        <td><strong>{{ $requisition->ref }}</strong></td>
                                        <td>{{ $requisition->project->name }}</td>
                                        <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                                {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Recent LPOs & Quick Actions -->
        <div class="col-lg-6">
            <!-- Recent LPOs -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent LPOs</h5>
                    <a href="{{ route('procurement.lpos.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>LPO No.</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLpos as $lpo)
                                    <tr>
                                        <td><strong>{{ $lpo->lpo_number }}</strong></td>
                                        <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
                                        <td>UGX {{ number_format($lpo->items->sum('total_price'), 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $lpo->status === 'issued' ? 'success' : ($lpo->status === 'delivered' ? 'info' : 'warning') }}">
                                                {{ ucfirst($lpo->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No LPOs created yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('procurement.requisitions.pending') }}" class="btn btn-warning text-start">
                            <i class="bi bi-clock me-2"></i> Process Pending Requisitions
                            @if($requisitionStats->pending_procurement > 0)
                                <span class="badge bg-danger ms-2">{{ $requisitionStats->pending_procurement }}</span>
                            @endif
                        </a>
                        <a href="{{ route('procurement.requisitions.in-procurement') }}" class="btn btn-outline-info text-start">
                            <i class="bi bi-gear me-2"></i> View In Procurement
                        </a>
                        <a href="{{ route('procurement.lpos.index') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-receipt me-2"></i> LPO Management
                        </a>
                        <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-outline-success text-start">
                            <i class="bi bi-truck me-2"></i> Supplier Management
                        </a>
                    </div>
                </div>
            </div>

            <!-- Workflow Info -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Procurement Workflow</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Project Manager</strong>
                                <small class="text-muted d-block">Initial approval</small>
                            </div>
                        </div>
                        <div class="timeline-item completed">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Operations</strong>
                                <small class="text-muted d-block">Department approval</small>
                            </div>
                        </div>
                        <div class="timeline-item active">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Procurement</strong>
                                <small class="text-muted d-block">Supplier sourcing & LPO</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>CEO</strong>
                                <small class="text-muted d-block">Final approval</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Supplier</strong>
                                <small class="text-muted d-block">Delivery</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Finance</strong>
                                <small class="text-muted d-block">Payment</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-wrapper {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

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