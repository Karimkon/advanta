{{-- resources/views/finance/labor/index.blade.php - UPDATED --}}
@extends('finance.layouts.app')

@section('title', 'Labor Workers Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Labor Workers Management</h2>
            <p class="text-muted mb-0">Manage all labor workers and their payments</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.labor.import') }}" class="btn btn-outline-primary">
                <i class="bi bi-upload"></i> Import Workers
            </a>
            <a href="{{ route('finance.labor.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Worker
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Project</th>
                            <th>Role</th>
                            <th>Payment Frequency</th>
                            <th>Rate</th>
                            <th>NSSF No</th>
                            <th>Total Paid</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workers as $worker)
                            <tr>
                                <td>
                                    <strong>{{ $worker->name }}</strong>
                                    @if($worker->phone)
                                        <br><small class="text-muted">{{ $worker->phone }}</small>
                                    @endif
                                </td>
                                <td>{{ $worker->project->name }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $worker->role }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary text-capitalize">{{ $worker->payment_frequency }}</span>
                                </td>
                                <td class="text-success">
                                    UGX {{ number_format($worker->current_rate, 2) }} 
                                    <small class="text-muted">/{{ $worker->payment_frequency === 'daily' ? 'day' : 'month' }}</small>
                                </td>
                                <td>
                                    @if($worker->nssf_number)
                                        <span class="badge bg-warning">{{ $worker->nssf_number }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-primary">UGX {{ number_format($worker->total_paid, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $worker->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($worker->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('finance.labor.show', $worker) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('finance.labor.payments.create', $worker) }}" 
                                           class="btn btn-outline-success">
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-person-badge display-4 d-block mb-2"></i>
                                        No labor workers found.
                                        <p class="mt-2">Add workers individually or import from Excel.</p>
                                        <div class="mt-3">
                                            <a href="{{ route('finance.labor.import') }}" class="btn btn-outline-primary me-2">
                                                <i class="bi bi-upload"></i> Import Workers
                                            </a>
                                            <a href="{{ route('finance.labor.create') }}" class="btn btn-primary">
                                                Add Worker
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($workers->hasPages())
                <div class="card-footer bg-white">
                    {{ $workers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection