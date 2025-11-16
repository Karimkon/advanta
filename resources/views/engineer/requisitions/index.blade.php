@extends('engineer.layouts.app')

@section('title', 'My Requisitions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Requisitions</h2>
            <p class="text-muted mb-0">View and manage all your requisitions</p>
        </div>
        <a href="{{ route('engineer.requisitions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Requisition
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
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
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="store" {{ request('type') == 'store' ? 'selected' : '' }}>Store</option>
                        <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="project_manager_approved" {{ request('status') == 'project_manager_approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref No.</th>
                            <th>Project</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Urgency</th>
                            <th>Date</th>
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
                                    <span class="badge {{ $requisition->type === 'store' ? 'bg-info' : 'bg-primary' }}">
                                        {{ ucfirst($requisition->type) }}
                                    </span>
                                </td>
                                <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                <td>
                                    <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                        {{ $requisition->getCurrentStage() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $requisition->urgency === 'high' ? 'danger' : ($requisition->urgency === 'medium' ? 'warning' : 'success') }}">
                                        {{ ucfirst($requisition->urgency) }}
                                    </span>
                                </td>
                                <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('engineer.requisitions.show', $requisition) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-file-earmark-x display-4 d-block mb-2"></i>
                                        No requisitions found.
                                        <a href="{{ route('engineer.requisitions.create') }}" class="btn btn-primary mt-2">
                                            Create First Requisition
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($requisitions->hasPages())
                <div class="card-footer bg-white">
                    {{ $requisitions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection