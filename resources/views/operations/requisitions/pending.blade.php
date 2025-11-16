@extends('operations.layouts.app')

@section('title', 'Pending Approvals - Operations')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Pending Approvals</h2>
            <p class="text-muted mb-0">Requisitions waiting for your approval</p>
        </div>
        <a href="{{ route('operations.requisitions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to All Requisitions
        </a>
    </div>

    <!-- Pending Requisitions -->
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
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingRequisitions as $requisition)
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
                                <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('operations.requisitions.show', $requisition) }}" 
                                           class="btn btn-outline-warning">
                                            <i class="bi bi-clipboard-check"></i> Review
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                                        No pending approvals found.
                                        <p class="mt-2">All requisitions have been reviewed.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($pendingRequisitions->hasPages())
                <div class="card-footer bg-white">
                    {{ $pendingRequisitions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection