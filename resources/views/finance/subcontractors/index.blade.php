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