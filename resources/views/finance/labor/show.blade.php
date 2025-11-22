{{-- resources/views/finance/labor/show.blade.php --}}
@extends('finance.layouts.app')

@section('title', $worker->name . ' - Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $worker->name }}</h2>
            <p class="text-muted mb-0">Labor Worker Details & Payments</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.labor.payments.create', $worker) }}" class="btn btn-success">
                <i class="bi bi-cash-coin"></i> Record Payment
            </a>
            <a href="{{ route('finance.labor.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- Worker Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Worker Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Project:</strong> {{ $worker->project->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Role:</strong> 
                        <span class="badge bg-info">{{ $worker->role }}</span>
                    </div>
                    @if($worker->phone)
                    <div class="mb-3">
                        <strong>Phone:</strong> {{ $worker->phone }}
                    </div>
                    @endif
                    @if($worker->id_number)
                    <div class="mb-3">
                        <strong>ID Number:</strong> {{ $worker->id_number }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Payment Frequency:</strong>
                        <span class="badge bg-secondary text-capitalize">{{ $worker->payment_frequency }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Rate:</strong>
                        <span class="text-success">
                            UGX {{ number_format($worker->current_rate, 2) }} 
                            <small class="text-muted">/{{ $worker->payment_frequency === 'daily' ? 'day' : 'month' }}</small>
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Start Date:</strong> {{ $worker->start_date->format('M d, Y') }}
                    </div>
                    @if($worker->end_date)
                    <div class="mb-3">
                        <strong>End Date:</strong> {{ $worker->end_date->format('M d, Y') }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $worker->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($worker->status) }}
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
                        <span>Total Payments:</span>
                        <strong class="text-primary">UGX {{ number_format($worker->total_paid, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Count:</span>
                        <strong>{{ $worker->payments->count() }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Average Payment:</span>
                        <strong class="text-success">
                            UGX {{ number_format($worker->payments->count() > 0 ? $worker->total_paid / $worker->payments->count() : 0, 2) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Payment History -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Period</th>
                                    <th>Description</th>
                                    <th>Days Worked</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($worker->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td>
                                            <small>{{ $payment->period_start->format('M d') }} - {{ $payment->period_end->format('M d, Y') }}</small>
                                        </td>
                                        <td>{{ $payment->description }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $payment->days_worked }} days</span>
                                        </td>
                                        <td class="text-success fw-bold">UGX {{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-capitalize">{{ $payment->payment_method }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('finance.labor.payments.receipt', $payment) }}" 
                                            class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-receipt"></i> Receipt
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-cash-coin display-4 d-block mb-2"></i>
                                                No payments recorded yet.
                                                <p class="mt-2">Record the first payment for this worker.</p>
                                                <a href="{{ route('finance.labor.payments.create', $worker) }}" class="btn btn-success mt-2">
                                                    Record Payment
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Statistics -->
            @if($worker->payments->count() > 0)
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card border-0 bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">This Month</h6>
                            <h4>UGX {{ number_format($worker->payments->where('payment_date', '>=', now()->startOfMonth())->sum('amount'), 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Payments</h6>
                            <h4>UGX {{ number_format($worker->total_paid, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Payment Count</h6>
                            <h4>{{ $worker->payments->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection