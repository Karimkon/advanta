{{-- resources/views/finance/subcontractors/index.blade.php - FIXED --}}
@extends('finance.layouts.app')

@section('title', 'Subcontractors Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Subcontractors Management</h2>
            <p class="text-muted mb-0">Manage all subcontractors and their contracts</p>
        </div>
        <a href="{{ route('finance.subcontractors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Subcontractor
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Specialization</th>
                            <th>Contact</th>
                            <th>Projects</th>
                            <th>Total Contracts</th>
                            <th>Total Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subcontractors as $subcontractor)
                            <tr>
                                <td>
                                    <strong>{{ $subcontractor->name }}</strong>
                                    @if($subcontractor->contact_person)
                                        <br><small class="text-muted">Contact: {{ $subcontractor->contact_person }}</small>
                                    @endif
                                </td>
                                <td>{{ $subcontractor->specialization }}</td>
                                <td>
                                    @if($subcontractor->phone)
                                        <div>{{ $subcontractor->phone }}</div>
                                    @endif
                                    @if($subcontractor->email)
                                        <div class="text-muted small">{{ $subcontractor->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $subcontractor->project_subcontractors_count }}</span>
                                </td>
                                <td class="text-success">UGX {{ number_format($subcontractor->total_contracts_amount, 2) }}</td>
                                <td class="text-primary">UGX {{ number_format($subcontractor->total_paid_amount, 2) }}</td>
                                <td class="fw-bold {{ $subcontractor->balance > 0 ? 'text-warning' : 'text-success' }}">
                                    UGX {{ number_format($subcontractor->balance, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $subcontractor->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($subcontractor->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('finance.subcontractors.show', $subcontractor) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people display-4 d-block mb-2"></i>
                                        No subcontractors found.
                                        <p class="mt-2">Add your first subcontractor to get started.</p>
                                        <a href="{{ route('finance.subcontractors.create') }}" class="btn btn-primary mt-2">
                                            Add Subcontractor
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subcontractors->hasPages())
                <div class="card-footer bg-white">
                    {{ $subcontractors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection