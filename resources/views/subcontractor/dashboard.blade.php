@extends('subcontractor.layouts.app')

@section('title', 'Subcontractor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Welcome, {{ $subcontractor->name }}</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-tools me-1"></i> {{ $subcontractor->specialization }}
                @if($subcontractor->contact_person)
                    <span class="ms-3"><i class="bi bi-person me-1"></i> {{ $subcontractor->contact_person }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('subcontractor.requisitions.create') }}" class="btn btn-warning">
            <i class="bi bi-plus-circle me-1"></i> New Requisition
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Active Contracts</h6>
                            <h2 class="fw-bold text-primary mb-1">{{ $stats['active_contracts'] }}</h2>
                            <small class="text-muted">of {{ $stats['total_contracts'] }} total</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Requisitions</h6>
                            <h2 class="fw-bold text-info mb-1">{{ $stats['total_requisitions'] }}</h2>
                            <small class="text-warning">{{ $stats['pending_requisitions'] }} pending</small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-clipboard-data fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Contract Value</h6>
                            <h4 class="fw-bold text-success mb-1">UGX {{ number_format($stats['total_contract_value']) }}</h4>
                            <small class="text-success">Total contracts</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-currency-dollar fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Amount Paid</h6>
                            <h4 class="fw-bold text-warning mb-1">UGX {{ number_format($stats['total_paid']) }}</h4>
                            <small class="text-muted">Balance: UGX {{ number_format($stats['balance']) }}</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-wallet2 fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Contracts -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-check text-primary me-2"></i>Active Contracts</h5>
                    <span class="badge bg-primary">{{ $activeContracts->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse($activeContracts as $contract)
                        <div class="border-start border-3 border-primary ps-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">{{ $contract->project->name ?? 'N/A' }}</h6>
                                    <p class="mb-1 text-muted small">{{ $contract->work_description }}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($contract->start_date)->format('M d, Y') }}
                                        @if($contract->end_date)
                                            - {{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}
                                        @endif
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">Contract #{{ $contract->contract_number }}</small>
                                    <span class="badge bg-success">UGX {{ number_format($contract->contract_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-folder-x display-4 d-block mb-2"></i>
                            No active contracts found.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Requisitions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-info me-2"></i>Recent Requisitions</h5>
                    <a href="{{ route('subcontractor.requisitions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($recentRequisitions as $requisition)
                        <div class="border-start border-3 {{ $requisition->status === 'pending' ? 'border-warning' : 'border-info' }} ps-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('subcontractor.requisitions.show', $requisition) }}" class="text-decoration-none">
                                            {{ $requisition->ref }}
                                        </a>
                                    </h6>
                                    <p class="mb-1 text-muted small">{{ $requisition->project->name ?? 'N/A' }}</p>
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $requisition->type === 'store' ? 'bg-info' : 'bg-primary' }} me-2">
                                            {{ ucfirst($requisition->type) }}
                                        </span>
                                        <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                            {{ $requisition->getCurrentStage() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">{{ $requisition->created_at->diffForHumans() }}</small>
                                    <small class="text-success">UGX {{ number_format($requisition->estimated_total) }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            No requisitions yet.
                            <a href="{{ route('subcontractor.requisitions.create') }}" class="d-block mt-2">
                                Create your first requisition
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning text-warning me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('subcontractor.requisitions.create') }}?type=store" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-box-seam fs-4 d-block mb-2"></i>
                                Store Requisition
                                <small class="d-block text-muted">Request from project store</small>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('subcontractor.requisitions.create') }}?type=purchase" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-cart-plus fs-4 d-block mb-2"></i>
                                Purchase Requisition
                                <small class="d-block text-muted">Request new purchases</small>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('subcontractor.requisitions.index') }}" class="btn btn-outline-secondary w-100 py-3">
                                <i class="bi bi-list-ul fs-4 d-block mb-2"></i>
                                View All Requisitions
                                <small class="d-block text-muted">Track your requests</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
