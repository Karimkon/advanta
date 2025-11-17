@extends('ceo.layouts.app')

@section('title', 'All Requisitions - CEO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">All Requisitions</h2>
            <p class="text-muted mb-0">Complete overview of all requisitions across the company</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <a href="{{ route('ceo.requisitions.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Approval
                @php
                    $pendingCount = \App\Models\Requisition::where('status', \App\Models\Requisition::STATUS_PROCUREMENT)->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $requisitions->total() }}</h4>
                    <small>Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    @php
                        $pendingCount = \App\Models\Requisition::where('status', \App\Models\Requisition::STATUS_PROCUREMENT)->count();
                    @endphp
                    <h4 class="mb-0">{{ $pendingCount }}</h4>
                    <small>Pending CEO</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    @php
                        $approvedCount = \App\Models\Requisition::where('status', \App\Models\Requisition::STATUS_CEO_APPROVED)->count();
                    @endphp
                    <h4 class="mb-0">{{ $approvedCount }}</h4>
                    <small>CEO Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    @php
                        $lpoCount = \App\Models\Requisition::where('status', \App\Models\Requisition::STATUS_LPO_ISSUED)->count();
                    @endphp
                    <h4 class="mb-0">{{ $lpoCount }}</h4>
                    <small>LPO Issued</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    @php
                        $completedCount = \App\Models\Requisition::where('status', \App\Models\Requisition::STATUS_COMPLETED)->count();
                    @endphp
                    <h4 class="mb-0">{{ $completedCount }}</h4>
                    <small>Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    @php
                        $rejectedCount = \App\Models\Requisition::where('status', \App\Models\Requisition::STATUS_REJECTED)->count();
                    @endphp
                    <h4 class="mb-0">{{ $rejectedCount }}</h4>
                    <small>Rejected</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="project_manager_approved" {{ request('status') == 'project_manager_approved' ? 'selected' : '' }}>PM Approved</option>
                        <option value="operations_approved" {{ request('status') == 'operations_approved' ? 'selected' : '' }}>Operations Approved</option>
                        <option value="procurement" {{ request('status') == 'procurement' ? 'selected' : '' }}>Pending CEO Approval</option>
                        <option value="ceo_approved" {{ request('status') == 'ceo_approved' ? 'selected' : '' }}>CEO Approved</option>
                        <option value="lpo_issued" {{ request('status') == 'lpo_issued' ? 'selected' : '' }}>LPO Issued</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="store" {{ request('type') == 'store' ? 'selected' : '' }}>Store Requisition</option>
                        <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Purchase Requisition</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                    <a href="{{ route('ceo.requisitions.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Requisitions Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-check text-primary me-2"></i>
                Requisitions List
            </h5>
            <div class="text-muted small">
                Showing {{ $requisitions->count() }} of {{ $requisitions->total() }} requisitions
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref No.</th>
                            <th>Project</th>
                            <th>Requested By</th>
                            <th>Type</th>
                            <th>Estimated Total</th>
                            <th>LPO Number</th>
                            <th>Status</th>
                            <th>Urgency</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $requisition)
                            <tr>
                                <td>
                                    <strong>{{ $requisition->ref }}</strong>
                                </td>
                                <td>{{ $requisition->project->name ?? 'N/A' }}</td>
                                <td>{{ $requisition->requester->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $requisition->isStoreRequisition() ? 'info' : 'primary' }}">
                                        {{ $requisition->isStoreRequisition() ? 'Store' : 'Purchase' }}
                                    </span>
                                </td>
                                <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                <td>
                                    @if($requisition->lpo)
                                        <span class="badge bg-info">{{ $requisition->lpo->lpo_number }}</span>
                                    @else
                                        <span class="text-muted">No LPO</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                        {{ $requisition->getCurrentStage() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                        {{ ucfirst($requisition->urgency) }}
                                    </span>
                                </td>
                                <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('ceo.requisitions.show', $requisition) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($requisition->status === \App\Models\Requisition::STATUS_PROCUREMENT)
                                            <button class="btn btn-outline-success" title="Approve"
                                                    data-bs-toggle="modal" data-bs-target="#approveModal{{ $requisition->id }}">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" title="Reject"
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $requisition->id }}">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Approve Modal -->
                            @if($requisition->status === \App\Models\Requisition::STATUS_PROCUREMENT)
                            <div class="modal fade" id="approveModal{{ $requisition->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approve Requisition</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('ceo.requisitions.approve', $requisition) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Are you sure you want to approve this requisition?</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Approval Comment (Optional)</label>
                                                    <textarea name="comment" class="form-control" rows="3" 
                                                              placeholder="Add approval comments..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Approve Requisition</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $requisition->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Requisition</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('ceo.requisitions.reject', $requisition) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Please provide a reason for rejecting this requisition:</p>
                                                <div class="mb-3">
                                                    <textarea name="comment" class="form-control" rows="3" 
                                                              placeholder="Enter rejection reason..." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject Requisition</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No requisitions found.
                                        <p class="mt-2">Try adjusting your filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($requisitions->hasPages())
                <div class="card-footer bg-white">
                    {{ $requisitions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
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
</style>
@endsection