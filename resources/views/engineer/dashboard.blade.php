@extends('engineer.layouts.app')

@section('title', 'Engineer Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="h3 mb-0">Welcome, {{ Auth::user()->name }}</h2>
            <p class="text-muted">Engineer Dashboard - Overview of your projects and requisitions</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Requisitions</h6>
                            <h4 class="mb-0">{{ $requisitions }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0"><small>All time</small></p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Pending</h6>
                            <h4 class="mb-0">{{ $pendingRequisitions }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock-history text-warning fs-3"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0"><small>Waiting approval</small></p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Approved</h6>
                            <h4 class="mb-0">{{ $approvedRequisitions }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle text-success fs-3"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0"><small>Moving forward</small></p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">My Projects</h6>
                            <h4 class="mb-0">{{ $projects }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-folder2 text-info fs-3"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0"><small>Active projects</small></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Requisitions -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Recent Requisitions</h5>
                </div>
                <div class="card-body">
                    @if($recentRequisitions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Ref No.</th>
                                        <th>Project</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Actual Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequisitions as $requisition)
                                        @php
                                            $showActualAmount = in_array($requisition->status, ['delivered', 'completed']) && 
                                                              $requisition->actual_amount != $requisition->estimated_total;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $requisition->ref }}</strong></td>
                                            <td>{{ $requisition->project->name }}</td>
                                            <td>
                                                <span class="badge {{ $requisition->type === 'store' ? 'bg-info' : 'bg-primary' }}">
                                                    {{ ucfirst($requisition->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                UGX {{ number_format($requisition->estimated_total, 2) }}
                                                @if($showActualAmount)
                                                    <br><small class="text-muted">Original</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($showActualAmount)
                                                    <strong class="text-success">UGX {{ number_format($requisition->actual_amount, 2) }}</strong>
                                                    <br><small class="text-success">Actual</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                                    {{ $requisition->getCurrentStage() }}
                                                </span>
                                            </td>
                                            <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('engineer.requisitions.show', $requisition) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No requisitions yet</p>
                            <a href="{{ route('engineer.requisitions.create') }}" class="btn btn-primary btn-sm">
                                Create Your First Requisition
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('engineer.requisitions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Requisition
                        </a>
                        <a href="{{ route('engineer.requisitions.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul"></i> View All Requisitions
                        </a>
                        <a href="{{ route('engineer.projects.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-folder2"></i> My Projects
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection