@extends('operations.layouts.app')

@section('title', 'Operations Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Welcome, {{ auth()->user()->name }}</h2>
            <p class="text-muted mb-0">Operations Dashboard - Review and approve purchase requisitions</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('operations.requisitions.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Approvals
                @if($requisitionStats->pending_operations > 0)
                    <span class="badge bg-danger ms-1">{{ $requisitionStats->pending_operations }}</span>
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
                            <h6 class="card-subtitle text-muted mb-2">Pending Approval</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $requisitionStats->pending_operations }}</h2>
                            <small class="text-warning">Waiting your review</small>
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
                            <h6 class="card-subtitle text-muted mb-2">Approved</h6>
                            <h2 class="fw-bold text-success mb-1">{{ $requisitionStats->approved }}</h2>
                            <small class="text-success">Sent to procurement</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
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
                            <h2 class="fw-bold text-info mb-1">{{ $requisitionStats->sent_to_procurement }}</h2>
                            <small class="text-info">Being processed</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-truck fs-4 text-info"></i>
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
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock text-warning me-2"></i>
                        Requisitions Pending Approval
                    </h5>
                    <a href="{{ route('operations.requisitions.pending') }}" class="btn btn-sm btn-outline-primary">
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
                                    <th>Requested By</th>
                                    <th>Amount</th>
                                    <th>Urgency</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingRequisitions as $requisition)
                                    <tr>
                                        <td><strong>{{ $requisition->ref }}</strong></td>
                                        <td>{{ $requisition->project->name }}</td>
                                        <td>{{ $requisition->requester->name }}</td>
                                        <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                                {{ ucfirst($requisition->urgency) }}
                                            </span>
                                        </td>
                                        <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('operations.requisitions.show', $requisition) }}" 
                                               class="btn btn-sm btn-outline-warning">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                                            No pending requisitions for approval.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recently Processed -->
            @if($recentProcessed->count() > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recently Processed</h5>
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
                                    <th>Processed Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProcessed as $requisition)
                                    <tr>
                                        <td><strong>{{ $requisition->ref }}</strong></td>
                                        <td>{{ $requisition->project->name }}</td>
                                        <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                                {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $requisition->approvals->where('approved_by', auth()->id())->first()->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('operations.requisitions.pending') }}" class="btn btn-warning text-start">
                            <i class="bi bi-clock me-2"></i> Review Pending Approvals
                            @if($requisitionStats->pending_operations > 0)
                                <span class="badge bg-danger ms-2">{{ $requisitionStats->pending_operations }}</span>
                            @endif
                        </a>
                        <a href="{{ route('operations.requisitions.index') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-list-check me-2"></i> View All Requisitions
                        </a>
                        <a href="{{ route('operations.requisitions.approved') }}" class="btn btn-outline-success text-start">
                            <i class="bi bi-check-circle me-2"></i> Approved Requisitions
                        </a>
                    </div>
                </div>
            </div>

            <!-- Workflow Info -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Approval Workflow</h5>
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
                        <div class="timeline-item active">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Operations</strong>
                                <small class="text-muted d-block">Your review</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Procurement</strong>
                                <small class="text-muted d-block">Supplier sourcing</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>CEO</strong>
                                <small class="text-muted d-block">Final approval</small>
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
    background: #198754;
    border-color: #198754;
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