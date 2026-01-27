@extends('admin.layouts.app')
@section('title', 'Subcontractors Management')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Subcontractors Management</h2>
            <p class="text-muted mb-0">Manage subcontractors, contracts, and payment details</p>
        </div>
        <div>
            <a href="{{ route('admin.subcontractors.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Subcontractor
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Subcontractors</h6>
                            <h3 class="mb-0">{{ $totalSubcontractors }}</h3>
                        </div>
                        <i class="bi bi-people fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Active</h6>
                            <h3 class="mb-0">{{ $activeSubcontractors }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Contract Value</h6>
                            <h3 class="mb-0">{{ number_format($totalContractValue, 0) }}</h3>
                        </div>
                        <i class="bi bi-file-text fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Paid</h6>
                            <h3 class="mb-0">{{ number_format($totalPaid, 0) }}</h3>
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.subcontractors.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="specialization" class="form-select">
                        <option value="">All Specializations</option>
                        @foreach($specializations as $spec)
                            <option value="{{ $spec }}" {{ request('specialization') === $spec ? 'selected' : '' }}>{{ $spec }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subcontractors Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Phone / Email</th>
                            <th>Specialization</th>
                            <th>Contracts</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subcontractors as $subcontractor)
                            @php
                                $totalValue = $subcontractor->projectSubcontractors->sum('contract_amount');
                                $totalPaidSub = $subcontractor->projectSubcontractors->flatMap->payments->sum('amount');
                                $balance = $totalValue - $totalPaidSub;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $subcontractor->name }}</strong>
                                    @if($subcontractor->tax_number)
                                        <br><small class="text-muted">TIN: {{ $subcontractor->tax_number }}</small>
                                    @endif
                                </td>
                                <td>{{ $subcontractor->contact_person ?? '-' }}</td>
                                <td>
                                    @if($subcontractor->phone)
                                        <i class="bi bi-telephone text-muted"></i> {{ $subcontractor->phone }}<br>
                                    @endif
                                    @if($subcontractor->email)
                                        <i class="bi bi-envelope text-muted"></i> {{ $subcontractor->email }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $subcontractor->specialization }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $subcontractor->project_subcontractors_count }} contract(s)</span>
                                </td>
                                <td>
                                    <strong>KES {{ number_format($totalValue, 0) }}</strong>
                                    @if($balance > 0)
                                        <br><small class="text-danger">Bal: {{ number_format($balance, 0) }}</small>
                                    @else
                                        <br><small class="text-success">Fully Paid</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $subcontractor->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($subcontractor->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.subcontractors.show', $subcontractor) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.subcontractors.edit', $subcontractor) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.subcontractors.destroy', $subcontractor) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this subcontractor?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people display-4 d-block mb-2"></i>
                                        No subcontractors found.
                                        <p class="mt-2">
                                            <a href="{{ route('admin.subcontractors.create') }}" class="btn btn-primary">
                                                Add First Subcontractor
                                            </a>
                                        </p>
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
