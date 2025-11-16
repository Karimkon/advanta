@extends('project_manager.layouts.app')

@section('title', 'Project Manager Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Welcome, {{ auth()->user()->name }}</h2>
            <p class="text-muted mb-0">Project Manager Dashboard - Overview of your projects and requisitions</p>
        </div>
        <a href="{{ route('project_manager.requisitions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Requisition
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Requisitions</h6>
                            <h2 class="fw-bold text-primary mb-1">{{ $requisitionStats['total'] }}</h2>
                            <small class="text-muted">All time</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
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
                            <h6 class="card-subtitle text-muted mb-2">Pending</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $requisitionStats['pending'] }}</h2>
                            <small class="text-warning">Waiting approval</small>
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
                            <h2 class="fw-bold text-success mb-1">{{ $requisitionStats['approved'] + $requisitionStats['operations_approved'] }}</h2>
                            <small class="text-success">Moving forward</small>
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
                            <h6 class="card-subtitle text-muted mb-2">My Projects</h6>
                            <h2 class="fw-bold text-info mb-1">{{ $projects->count() }}</h2>
                            <small class="text-muted">Active projects</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-folder2 fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Requisitions -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Recent Requisitions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref No.</th>
                                    <th>Project</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRequisitions as $requisition)
                                    <tr>
                                        <td><strong>{{ $requisition->ref }}</strong></td>
                                        <td>{{ $requisition->project->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $requisition->isStoreRequisition() ? 'info' : 'primary' }}">
                                                {{ $requisition->isStoreRequisition() ? 'Store' : 'Purchase' }}
                                            </span>
                                        </td>
                                        <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                                {{ $requisition->getCurrentStage() }}
                                            </span>
                                        </td>
                                        <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('project_manager.requisitions.show', $requisition) }}" 
                                               class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No requisitions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recentRequisitions->count() > 0)
                        <div class="text-center mt-3">
                            <a href="{{ route('project_manager.requisitions.index') }}" class="btn btn-outline-primary btn-sm">
                                View All Requisitions
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Approvals -->
            @if($pendingApprovals->count() > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                        Pending My Approval
                    </h5>
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
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingApprovals as $requisition)
                                    <tr>
                                        <td><strong>{{ $requisition->ref }}</strong></td>
                                        <td>{{ $requisition->project->name }}</td>
                                        <td>{{ $requisition->requester->name }}</td>
                                        <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $requisition->isStoreRequisition() ? 'info' : 'primary' }}">
                                                {{ $requisition->isStoreRequisition() ? 'Store' : 'Purchase' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('project_manager.requisitions.show', $requisition) }}" 
                                                   class="btn btn-outline-primary">Review</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('project_manager.requisitions.pending') }}" class="btn btn-warning btn-sm">
                            Review All Pending Approvals
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Quick Actions & Projects -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('project_manager.requisitions.create') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-plus-circle me-2"></i> Create New Requisition
                        </a>
                        <a href="{{ route('project_manager.requisitions.pending') }}" class="btn btn-outline-warning text-start">
                            <i class="bi bi-clock me-2"></i> Review Pending Approvals
                        </a>
                        <a href="{{ route('project_manager.requisitions.index') }}" class="btn btn-outline-info text-start">
                            <i class="bi bi-list-check me-2"></i> View My Requisitions
                        </a>
                        <a href="{{ route('project_manager.projects.index') }}" class="btn btn-outline-success text-start">
                            <i class="bi bi-folder2 me-2"></i> My Projects
                        </a>
                    </div>
                </div>
            </div>

            <!-- My Projects -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">My Projects</h5>
                </div>
                <div class="card-body">
                    @forelse($projects as $project)
                        <div class="project-item d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="project-icon bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="bi bi-folder text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $project->name }}</h6>
                                <small class="text-muted d-block">{{ $project->code }}</small>
                                <span class="badge bg-{{ $project->status === 'active' ? 'success' : 'secondary' }} mt-1">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                            No projects assigned.
                        </div>
                    @endforelse
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
.project-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
</style>
@endsection