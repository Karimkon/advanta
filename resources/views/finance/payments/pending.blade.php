@extends('finance.layouts.app')

@section('title', 'Pending Payments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Pending Payments</h2>
            <p class="text-muted mb-0">Requisitions waiting for payment processing</p>
        </div>
        <a href="{{ route('finance.payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to All Payments
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Requisition Ref</th>
                            <th>Project</th>
                            <th>Supplier</th>
                            <th>Type</th>
                            <th>Total Amount</th>
                            <th>LPO Number</th>
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
                                <td>{{ $requisition->project->name ?? 'N/A' }}</td>
                                <td>
                                    @if($requisition->lpo && $requisition->lpo->supplier)
                                        {{ $requisition->lpo->supplier->name }}
                                    @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                <td>
                                    <span class="badge bg-{{ $requisition->type === 'store' ? 'info' : 'primary' }}">
                                        {{ ucfirst($requisition->type) }}
                                    </span>
                                </td>
                                <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                <td>
                                    @if($requisition->lpo)
                                        {{ $requisition->lpo->lpo_number }}
                                    @else
                                        <span class="text-muted">No LPO</span>
                                    @endif
                                </td>
                                <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('finance.payments.create', $requisition) }}" 
                                           class="btn btn-success">
                                            <i class="bi bi-credit-card"></i> Process Payment
                                        </a>
                                        <a href="{{ route('project_manager.requisitions.show', $requisition) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> Details
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                                        No pending payments found.
                                        <p class="mt-2">All requisitions have been processed.</p>
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