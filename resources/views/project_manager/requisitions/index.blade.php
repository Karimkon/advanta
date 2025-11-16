@extends('project_manager.layouts.app')

@section('title', 'My Requisitions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Requisitions</h2>
            <p class="text-muted mb-0">Manage and track all your requisitions</p>
        </div>
        <a href="{{ route('project_manager.requisitions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Requisition
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="project_manager_approved" {{ request('status') == 'project_manager_approved' ? 'selected' : '' }}>Approved</option>
                        <option value="operations_approved" {{ request('status') == 'operations_approved' ? 'selected' : '' }}>Operations Approved</option>
                        <option value="procurement" {{ request('status') == 'procurement' ? 'selected' : '' }}>Procurement</option>
                        <option value="ceo_approved" {{ request('status') == 'ceo_approved' ? 'selected' : '' }}>CEO Approved</option>
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
                <div class="col-md-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                    <a href="{{ route('project_manager.requisitions.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Requisitions Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref No.</th>
                            <th>Project</th>
                            <th>Type</th>
                            <th>Estimated Total</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Current Stage</th>
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
                                <td>{{ $requisition->project->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $requisition->isStoreRequisition() ? 'info' : 'primary' }}">
                                        {{ $requisition->isStoreRequisition() ? 'Store' : 'Purchase' }}
                                    </span>
                                </td>
                                <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                <td>
                                    <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                        {{ ucfirst($requisition->urgency) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $requisition->getCurrentStage() }}</small>
                                </td>
                                <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('project_manager.requisitions.show', $requisition) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($requisition->canBeEdited())
                                            <a href="#" class="btn btn-outline-warning" title="Edit" disabled>
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-file-earmark-text display-4 d-block mb-2"></i>
                                        No requisitions found.
                                        <a href="{{ route('project_manager.requisitions.create') }}" class="btn btn-primary mt-2">
                                            Create Your First Requisition
                                        </a>
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
@endsection

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelects = document.querySelectorAll('select[name="status"], select[name="type"], select[name="project_id"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });
</script>
@endpush