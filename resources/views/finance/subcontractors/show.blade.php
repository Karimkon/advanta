{{-- resources/views/finance/subcontractors/show.blade.php - FIXED --}}
@extends('finance.layouts.app')

@section('title', $subcontractor->name . ' - Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $subcontractor->name }}</h2>
            <p class="text-muted mb-0">Subcontractor Details & Contracts</p>
        </div>
        <a href="{{ route('finance.subcontractors.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- Subcontractor Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Specialization:</strong> {{ $subcontractor->specialization }}
                    </div>
                    @if($subcontractor->contact_person)
                    <div class="mb-3">
                        <strong>Contact Person:</strong> {{ $subcontractor->contact_person }}
                    </div>
                    @endif
                    @if($subcontractor->phone)
                    <div class="mb-3">
                        <strong>Phone:</strong> {{ $subcontractor->phone }}
                    </div>
                    @endif
                    @if($subcontractor->email)
                    <div class="mb-3">
                        <strong>Email:</strong> {{ $subcontractor->email }}
                    </div>
                    @endif
                    @if($subcontractor->tax_number)
                    <div class="mb-3">
                        <strong>Tax Number:</strong> {{ $subcontractor->tax_number }}
                    </div>
                    @endif
                    @if($subcontractor->address)
                    <div class="mb-3">
                        <strong>Address:</strong> {{ $subcontractor->address }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $subcontractor->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($subcontractor->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Contracts:</span>
                        <strong class="text-success">UGX {{ number_format($subcontractor->total_contracts_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Paid:</span>
                        <strong class="text-primary">UGX {{ number_format($subcontractor->total_paid_amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Balance:</span>
                        <strong class="text-warning">UGX {{ number_format($subcontractor->balance, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Active Projects:</span>
                        <strong>{{ $subcontractor->projectSubcontractors->where('status', 'active')->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Project Contracts -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Project Contracts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Project</th>
                                    <th>Contract No.</th>
                                    <th>Work Description</th>
                                    <th>Contract Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subcontractor->projectSubcontractors as $contract)
                                    @php
                                        $totalPaid = $contract->payments->sum('amount');
                                        $balance = $contract->contract_amount - $totalPaid;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $contract->project->name }}</strong>
                                            <br><small class="text-muted">Start: {{ $contract->start_date->format('M d, Y') }}</small>
                                        </td>
                                        <td>{{ $contract->contract_number }}</td>
                                        <td>{{ Str::limit($contract->work_description, 50) }}</td>
                                        <td class="text-success">UGX {{ number_format($contract->contract_amount, 2) }}</td>
                                        <td class="text-primary">UGX {{ number_format($totalPaid, 2) }}</td>
                                        <td class="fw-bold {{ $balance > 0 ? 'text-warning' : 'text-success' }}">
                                            UGX {{ number_format($balance, 2) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $contract->status === 'active' ? 'success' : ($contract->status === 'completed' ? 'info' : 'secondary') }}">
                                                {{ ucfirst($contract->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('finance.subcontractors.ledger', $contract) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="bi bi-journal-text"></i> Ledger
                                                </a>
                                                @if($balance > 0)
                                                <a href="{{ route('finance.subcontractors.payments.create', $contract) }}" 
                                                   class="btn btn-outline-success">
                                                    <i class="bi bi-cash-coin"></i> Pay
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            @if($subcontractor->payments->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Payments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Project</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subcontractor->payments->take(5) as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td>{{ $payment->projectSubcontractor->project->name }}</td>
                                        <td>{{ $payment->description }}</td>
                                        <td class="text-success">UGX {{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-capitalize">{{ $payment->payment_type }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection