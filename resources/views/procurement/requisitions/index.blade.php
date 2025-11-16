@extends('procurement.layouts.app')

@section('title', 'All Requisitions - Procurement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">All Requisitions</h2>
            <p class="text-muted mb-0">Manage all requisitions in procurement workflow</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('procurement.requisitions.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending
            </a>
            <a href="{{ route('procurement.requisitions.in-procurement') }}" class="btn btn-info">
                <i class="bi bi-gear"></i> In Procurement
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="operations_approved" {{ request('status') == 'operations_approved' ? 'selected' : '' }}>Pending Procurement</option>
                        <option value="procurement" {{ request('status') == 'procurement' ? 'selected' : '' }}>In Procurement</option>
                        <option value="ceo_approved" {{ request('status') == 'ceo_approved' ? 'selected' : '' }}>CEO Approved</option>
                        <option value="lpo_issued" {{ request('status') == 'lpo_issued' ? 'selected' : '' }}>LPO Issued</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">All Projects</option>
                        @foreach($requisitions->pluck('project')->unique() as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                    <a href="{{ route('procurement.requisitions.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Requested By</th>
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
                                <td>{{ $requisition->requester->name }}</td>
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
                                        <a href="{{ route('procurement.requisitions.show', $requisition) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($requisition->status === 'operations_approved')
                                            <a href="{{ route('procurement.requisitions.show', $requisition) }}" 
                                               class="btn btn-outline-warning" title="Process">
                                                <i class="bi bi-gear"></i>
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
        const filterSelects = document.querySelectorAll('select[name="status"], select[name="project_id"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });
</script>
@endpush