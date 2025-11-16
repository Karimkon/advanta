@extends('ceo.layouts.app')

@section('title', 'All Requisitions - CEO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">All Requisitions</h2>
            <p class="text-muted mb-0">Complete overview of all requisitions</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
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
                        <option value="ceo_approved" {{ request('status') == 'ceo_approved' ? 'selected' : '' }}>Pending CEO Approval</option>
                        <option value="lpo_issued" {{ request('status') == 'lpo_issued' ? 'selected' : '' }}>LPO Issued</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                    <a href="{{ route('ceo.requisitions.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                                <td>{{ $requisition->project->name }}</td>
                                <td>{{ $requisition->requester->name ?? 'N/A' }}</td>
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
                                        {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
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
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
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

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelects = document.querySelectorAll('select[name="status"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });
</script>
@endpush
@endsection