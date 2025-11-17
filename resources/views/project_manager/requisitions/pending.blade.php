@extends('project_manager.layouts.app')

@section('title', 'Pending Requisitions - Project Manager')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Pending Requisitions</h2>
            <p class="text-muted mb-0">Approve or reject store requisitions from your projects</p>
        </div>
        <a href="{{ route('project_manager.requisitions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to All Requisitions
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref No.</th>
                            <th>Project</th>
                            <th>Requested By</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Date</th>
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
                                    <span class="badge bg-info">{{ ucfirst($requisition->type) }}</span>
                                </td>
                                <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('project_manager.requisitions.show', $requisition) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($requisition->status === \App\Models\Requisition::STATUS_PENDING)
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
                            <div class="modal fade" id="approveModal{{ $requisition->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approve Requisition</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('project_manager.requisitions.approve', $requisition) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Are you sure you want to approve this requisition?</p>
                                                <p><strong>Ref:</strong> {{ $requisition->ref }}</p>
                                                <p><strong>Project:</strong> {{ $requisition->project->name }}</p>
                                                <p><strong>Amount:</strong> UGX {{ number_format($requisition->estimated_total, 2) }}</p>
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
                                        <form action="{{ route('project_manager.requisitions.reject', $requisition) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Please provide a reason for rejecting this requisition:</p>
                                                <div class="mb-3">
                                                    <textarea name="rejection_reason" class="form-control" rows="3" 
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
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No pending requisitions found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($pendingRequisitions->hasPages())
                <div class="card-footer bg-white">
                    {{ $pendingRequisitions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection