@extends('operations.layouts.app')

@section('title', 'Approved Requisitions - Operations')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Approved Requisitions</h2>
            <p class="text-muted mb-0">Requisitions you have approved</p>
        </div>
        <a href="{{ route('operations.requisitions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to All Requisitions
        </a>
    </div>

    <!-- Approved Requisitions -->
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
                            <th>Status</th>
                            <th>Approved Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvedRequisitions as $requisition)
                            <tr>
                                <td>
                                    <strong>{{ $requisition->ref }}</strong>
                                </td>
                                <td>{{ $requisition->project->name }}</td>
                                <td>{{ $requisition->requester->name }}</td>
                                <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                <td>
                                    <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $approval = $requisition->approvals->where('role', 'operations')->first();
                                    @endphp
                                    {{ $approval ? $approval->created_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('operations.requisitions.show', $requisition) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        @if($requisition->status === 'operations_approved')
                                            <form action="{{ route('operations.requisitions.send-to-procurement', $requisition) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm"
                                                        onclick="return confirm('Send this requisition to procurement?')">
                                                    <i class="bi bi-send"></i> Send to Procurement
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-file-earmark-check display-4 d-block mb-2"></i>
                                        No approved requisitions found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($approvedRequisitions->hasPages())
                <div class="card-footer bg-white">
                    {{ $approvedRequisitions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection