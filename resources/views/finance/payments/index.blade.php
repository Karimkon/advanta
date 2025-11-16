@extends('finance.layouts.app')

@section('title', 'All Payments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">All Payments</h2>
            <p class="text-muted mb-0">View and manage all payment records</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.payments.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Payments
            </a>
            <a href="{{ route('finance.payments.export') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="mobile_money" {{ request('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('finance.payments.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment ID</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Payment Method</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td><strong>#{{ $payment->id }}</strong></td>
                                <td>
                                    @if($payment->paid_on)
                                        {{ $payment->paid_on->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-secondary text-capitalize">
                                        {{ $payment->payment_method ? str_replace('_', ' ', $payment->payment_method) : 'Not set' }}
                                    </span>
                                </td>
                                <td>UGX {{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ $payment->status ? ucfirst($payment->status) : 'Unknown' }}
                                    </span>
                                </td>
                                <td>{{ $payment->reference ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('finance.payments.show', $payment) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-credit-card display-4 d-block mb-2"></i>
                                        No payments found.
                                        <p class="mt-2">No payment records available.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($payments->hasPages())
                <div class="card-footer bg-white">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection