@extends('engineer.layouts.app')

@section('title', 'Pending Requisitions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Pending Requisitions</h2>
            <p class="text-muted mb-0">Requisitions awaiting Project Manager approval</p>
        </div>
        <a href="{{ route('engineer.requisitions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Requisition
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
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Urgency</th>
                            <th>Date Submitted</th>
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
                                <td>
                                    <span class="badge {{ $requisition->type === 'store' ? 'bg-info' : 'bg-primary' }}">
                                        {{ ucfirst($requisition->type) }}
                                    </span>
                                </td>
                                <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
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
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                                        No pending requisitions.
                                        <p class="mt-2">All your requisitions have been processed or approved.</p>
                                        <a href="{{ route('engineer.requisitions.create') }}" class="btn btn-primary mt-2">
                                            Create New Requisition
                                        </a>
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