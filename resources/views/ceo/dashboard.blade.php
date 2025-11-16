@extends('ceo.layouts.app')

@section('title', 'CEO Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">CEO Dashboard</h2>
            <p class="text-muted mb-0">Executive overview and approval dashboard</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.requisitions.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Approvals
                @if($pendingRequisitions->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingRequisitions->count() }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Pending Approval</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $pendingRequisitions->count() }}</h2>
                            <small class="text-warning">Requisitions awaiting decision</small>
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
                            <h6 class="card-subtitle text-muted mb-2">Total Value</h6>
                            <h2 class="fw-bold text-primary mb-1">UGX {{ number_format($stats->total_amount ?? 0) }}</h2>
                            <small class="text-primary">All requisitions</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-currency-dollar fs-4 text-primary"></i>
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
                            <h2 class="fw-bold text-success mb-1">{{ $stats->approved ?? 0 }}</h2>
                            <small class="text-success">LPOs issued</small>
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
                            <h6 class="card-subtitle text-muted mb-2">Total Requisitions</h6>
                            <h2 class="fw-bold text-info mb-1">{{ $stats->total ?? 0 }}</h2>
                            <small class="text-info">In CEO workflow</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-list-check fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Requisitions -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Approval</h5>
                    <a href="{{ route('ceo.requisitions.pending') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($pendingRequisitions as $requisition)
                        <div class="border-start border-3 border-warning ps-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $requisition->ref }}</h6>
                                    <p class="mb-1 text-muted small">{{ $requisition->project->name }}</p>
                                    <p class="mb-0 fw-bold text-primary">UGX {{ number_format($requisition->estimated_total, 2) }}</p>
                                </div>
                                <a href="{{ route('ceo.requisitions.show', $requisition) }}" 
                                   class="btn btn-sm btn-outline-primary">Review</a>
                            </div>
                            @if($requisition->lpo)
                                <small class="text-info">LPO: {{ $requisition->lpo->lpo_number }}</small>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                            No pending requisitions for approval.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Approvals -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Approvals</h5>
                </div>
                <div class="card-body">
                    @forelse($recentApprovals as $requisition)
                        <div class="border-start border-3 border-success ps-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">{{ $requisition->ref }}</h6>
                                    <p class="mb-1 text-muted small">{{ $requisition->project->name }}</p>
                                    <p class="mb-0 fw-bold text-success">UGX {{ number_format($requisition->estimated_total, 2) }}</p>
                                </div>
                                <span class="badge bg-success">Approved</span>
                            </div>
                            <small class="text-muted">{{ $requisition->updated_at->diffForHumans() }}</small>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-clock-history display-4 d-block mb-2"></i>
                            No recent approvals.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Pending LPOs -->
    @if($pendingLpos->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending LPOs for Issuance</h5>
                    <span class="badge bg-warning">{{ $pendingLpos->count() }} LPOs</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>LPO Number</th>
                                    <th>Supplier</th>
                                    <th>Requisition</th>
                                    <th>Total Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingLpos as $lpo)
                                    <tr>
                                        <td><strong>{{ $lpo->lpo_number }}</strong></td>
                                        <td>{{ $lpo->supplier->name }}</td>
                                        <td>{{ $lpo->requisition->ref }}</td>
                                        <td>UGX {{ number_format($lpo->total, 2) }}</td>
                                        <td>
                                            <a href="{{ route('ceo.requisitions.show', $lpo->requisition) }}" 
                                               class="btn btn-sm btn-outline-primary">Review & Issue</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.icon-wrapper {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection