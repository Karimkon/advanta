{{-- resources/views/finance/subcontractors/index.blade.php --}}
@extends('finance.layouts.app')

@section('title', 'Subcontractors Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Subcontractors Management</h2>
            <p class="text-muted mb-0">Manage all subcontractors and their contracts</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.subcontractors.export.excel') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export Excel
            </a>
            <a href="{{ route('finance.subcontractors.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Subcontractor
            </a>
        </div>
    </div>

    {{-- Search and Filter Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('finance.subcontractors.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search Subcontractors</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               placeholder="Search by name, phone, email, TIN..."
                               value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('finance.subcontractors.index') }}" class="btn btn-outline-secondary" title="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="specialization" class="form-label">Specialization</label>
                    <select class="form-select" id="specialization" name="specialization">
                        <option value="">All Specializations</option>
                        @foreach($specializations ?? [] as $spec)
                            <option value="{{ $spec }}" {{ request('specialization') == $spec ? 'selected' : '' }}>
                                {{ $spec }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </form>

            @if(request('search') || request('status') || request('specialization'))
                <div class="mt-3 d-flex align-items-center gap-2">
                    <span class="text-muted">Filters:</span>
                    @if(request('search'))
                        <span class="badge bg-primary">
                            Search: "{{ request('search') }}"
                            <a href="{{ route('finance.subcontractors.index', array_merge(request()->except('search'), [])) }}" class="text-white ms-1">&times;</a>
                        </span>
                    @endif
                    @if(request('specialization'))
                        <span class="badge bg-info">
                            {{ request('specialization') }}
                            <a href="{{ route('finance.subcontractors.index', array_merge(request()->except('specialization'), [])) }}" class="text-white ms-1">&times;</a>
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="badge bg-{{ request('status') == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst(request('status')) }}
                            <a href="{{ route('finance.subcontractors.index', array_merge(request()->except('status'), [])) }}" class="text-white ms-1">&times;</a>
                        </span>
                    @endif
                    <a href="{{ route('finance.subcontractors.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear All
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Results Count --}}
    @if(request('search') || request('status') || request('specialization'))
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle"></i>
            Found <strong>{{ $subcontractors->total() }}</strong> subcontractor(s) matching your criteria
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Specialization</th>
                            <th>Contact</th>
                            <th>Project</th> {{-- CHANGED from "Projects" to "Project" --}}
                            <th>Contract Amount</th>
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
                                    {{-- SHOW PROJECT NAME INSTEAD OF COUNT --}}
                                    @if($subcontractor->projectSubcontractors->count() > 0)
                                        @foreach($subcontractor->projectSubcontractors as $contract)
                                            <div class="mb-1">
                                                <strong>{{ $contract->project->name ?? 'N/A' }}</strong>
                                                <small class="text-muted d-block">
                                                    Contract: {{ $contract->contract_number }}
                                                </small>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No project assigned</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- SHOW CONTRACT AMOUNT --}}
                                    @if($subcontractor->projectSubcontractors->count() > 0)
                                        @foreach($subcontractor->projectSubcontractors as $contract)
                                            <div class="mb-1">
                                                UGX {{ number_format($contract->contract_amount, 2) }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- SHOW TOTAL PAID --}}
                                    @if($subcontractor->payments->count() > 0)
                                        UGX {{ number_format($subcontractor->payments->sum('amount'), 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- SHOW BALANCE --}}
                                    @if($subcontractor->projectSubcontractors->count() > 0)
                                        @php
                                            $totalContracts = $subcontractor->projectSubcontractors->sum('contract_amount');
                                            $totalPaid = $subcontractor->payments->sum('amount');
                                            $balance = $totalContracts - $totalPaid;
                                        @endphp
                                        <strong class="{{ $balance > 0 ? 'text-warning' : 'text-success' }}">
                                            UGX {{ number_format($balance, 2) }}
                                        </strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $subcontractor->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($subcontractor->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('finance.subcontractors.show', $subcontractor) }}" 
                                           class="btn btn-outline-primary" 
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($subcontractor->projectSubcontractors->count() > 0)
                                            <a href="{{ route('finance.subcontractors.ledger', $subcontractor->projectSubcontractors->first()) }}" 
                                               class="btn btn-outline-info" 
                                               title="View Ledger">
                                                <i class="bi bi-journal-text"></i>
                                            </a>
                                        @endif
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