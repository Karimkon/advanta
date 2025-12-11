@extends('finance.layouts.app')

@section('title', 'Finance Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Finance Dashboard</h2>
            <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }} - Financial Overview & Payments</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.payments.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Payments
            </a>
            <a href="{{ route('finance.reports.index') }}" class="btn btn-info">
                <i class="bi bi-graph-up"></i> Reports
            </a>
        </div>
    </div>

    <!-- Statistics Cards - Clean Design -->
    <div class="row g-3 mb-4">
        <!-- Pending Payments Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-warning bg-opacity-10 me-2">
                                    <i class="bi bi-clock fs-5 text-warning"></i>
                                </div>
                                <h6 class="card-title text-muted mb-0">Pending Payments</h6>
                            </div>
                            <h2 class="fw-bold mb-3">{{ $stats['pending_payments'] }}</h2>
                            
                            <div class="mb-1">
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Total</span>
                                    <span class="small fw-bold">UGX {{ number_format($stats['pending_total_vat_inclusive'] ?? 0, 0) }}</span>
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="d-flex justify-content-between">
                                    <span class="small text-success">Base Amount</span>
                                    <span class="small text-success">UGX {{ number_format($stats['pending_total_base'] ?? 0, 0) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-info">VAT Amount</span>
                                    <span class="small text-info">UGX {{ number_format($stats['pending_total_vat'] ?? 0, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month Total Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-primary bg-opacity-10 me-2">
                                    <i class="bi bi-calendar-month fs-5 text-primary"></i>
                                </div>
                                <h6 class="card-title text-muted mb-0">This Month</h6>
                            </div>
                            <h2 class="fw-bold mb-3 text-primary">UGX {{ number_format($stats['total_amount_this_month'], 0) }}</h2>
                            
                            <div class="mb-1">
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">Base Amount</span>
                                    <span class="small text-success fw-medium">UGX {{ number_format($stats['base_amount_this_month'], 0) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted">VAT Amount</span>
                                    <span class="small text-info fw-medium">UGX {{ number_format($stats['total_vat_this_month'], 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 me-2">
                                    <i class="bi bi-cash-coin fs-5 text-info"></i>
                                </div>
                                <h6 class="card-title text-muted mb-0">Total Expenses</h6>
                            </div>
                            <h2 class="fw-bold mb-3 text-info">UGX {{ number_format($stats['total_expenses'], 0) }}</h2>
                            <div class="mt-4">
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    All time expenses
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Payments Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-danger bg-opacity-10 me-2">
                                    <i class="bi bi-exclamation-triangle fs-5 text-danger"></i>
                                </div>
                                <h6 class="card-title text-muted mb-0">Overdue</h6>
                            </div>
                            <h2 class="fw-bold mb-3 text-danger">{{ $stats['overdue_payments'] }}</h2>
                            <div class="mt-4">
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    Require attention
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alternative Design Option - Even Cleaner -->
    <div class="row g-3 mb-4 d-none">
        <!-- Alternative Design - Minimal Cards -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Pending</h6>
                            <h3 class="fw-bold mb-1">{{ $stats['pending_payments'] }}</h3>
                            <div class="small">
                                <div class="text-success">Base: {{ number_format($stats['pending_total_base'] ?? 0, 0) }}</div>
                                <div class="text-info">VAT: {{ number_format($stats['pending_total_vat'] ?? 0, 0) }}</div>
                            </div>
                        </div>
                        <div class="icon-circle-sm bg-warning bg-opacity-10">
                            <i class="bi bi-clock text-warning"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-2">
                    <small class="text-muted">Total: UGX {{ number_format($stats['pending_total_vat_inclusive'] ?? 0, 0) }}</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">This Month</h6>
                            <h3 class="fw-bold text-primary mb-1">UGX {{ number_format($stats['total_amount_this_month'], 0) }}</h3>
                            <div class="small">
                                <div class="text-success">Base: {{ number_format($stats['base_amount_this_month'], 0) }}</div>
                                <div class="text-info">VAT: {{ number_format($stats['total_vat_this_month'], 0) }}</div>
                            </div>
                        </div>
                        <div class="icon-circle-sm bg-primary bg-opacity-10">
                            <i class="bi bi-calendar-month text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-2">
                    <small class="text-muted">{{ now()->format('F Y') }} payments</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Expenses</h6>
                            <h3 class="fw-bold text-info mb-1">UGX {{ number_format($stats['total_expenses'], 0) }}</h3>
                            <div class="small text-muted">All recorded expenses</div>
                        </div>
                        <div class="icon-circle-sm bg-info bg-opacity-10">
                            <i class="bi bi-cash-coin text-info"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-2">
                    <small class="text-muted">Cumulative total</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2">Overdue</h6>
                            <h3 class="fw-bold text-danger mb-1">{{ $stats['overdue_payments'] }}</h3>
                            <div class="small text-danger">Payments delayed</div>
                        </div>
                        <div class="icon-circle-sm bg-danger bg-opacity-10">
                            <i class="bi bi-exclamation-triangle text-danger"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-2">
                    <small class="text-danger">Action required</small>
                </div>
            </div>
        </div>
    </div>

    <!-- REST OF YOUR EXISTING CODE BELOW -->
    <div class="row g-4">
        <!-- Pending Payments with VAT-inclusive amounts -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock text-warning me-2"></i>
                        Pending Payments (VAT Inclusive)
                    </h5>
                    <a href="{{ route('finance.payments.pending') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Requisition Ref</th>
                                    <th>Project</th>
                                    <th>Supplier</th>
                                    <th>Base Amount</th>
                                    <th>VAT</th>
                                    <th>Total Amount</th>
                                    <th>LPO Number</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingPayments as $requisition)
                                    @php
                                        $vatInclusiveTotal = $requisition->vat_inclusive_total ?? $requisition->estimated_total;
                                        $baseAmount = $requisition->base_amount ?? $requisition->estimated_total;
                                        $vatAmount = $requisition->vat_amount ?? 0;
                                        $hasVat = $vatAmount > 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $requisition->ref }}</strong>
                                        </td>
                                        <td>{{ $requisition->project->name ?? 'N/A' }}</td>
                                        <td>{{ $requisition->supplier->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="text-success">
                                                UGX {{ number_format($baseAmount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($hasVat)
                                                <span class="badge bg-info">
                                                    UGX {{ number_format($vatAmount, 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>UGX {{ number_format($vatInclusiveTotal, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($requisition->lpo)
                                                <code>{{ $requisition->lpo->lpo_number }}</code>
                                            @else
                                                <span class="text-muted">No LPO</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('finance.payments.create', $requisition) }}" 
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-credit-card"></i> Process
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                                            No pending payments found.
                                            <p class="mt-2">All payments have been processed.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Payments with VAT breakdown -->
            @if($recentPayments->count() > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card text-success me-2"></i>
                        Recent Payments (with VAT breakdown)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Method</th>
                                    <th>Base Amount</th>
                                    <th>VAT</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                    @php
                                        $base_amount = $payment->amount - $payment->vat_amount;
                                    @endphp
                                    <tr>
                                        <td>{{ $payment->formatted_paid_on ?? ($payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A') }}</td>
                                        <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-capitalize">
                                                {{ $payment->payment_method ? str_replace('_', ' ', $payment->payment_method) : 'Not set' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success">
                                                UGX {{ number_format($base_amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($payment->vat_amount > 0)
                                                <span class="badge bg-info">
                                                    UGX {{ number_format($payment->vat_amount, 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>UGX {{ number_format($payment->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                                {{ $payment->status ? ucfirst($payment->status) : 'Unknown' }}
                                            </span>
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

        <!-- Quick Actions & Project Spending -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('finance.payments.pending') }}" class="btn btn-outline-warning text-start">
                            <i class="bi bi-clock me-2"></i> Process Pending Payments
                        </a>
                        <a href="{{ route('finance.expenses.create') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-plus-circle me-2"></i> Record New Expense
                        </a>
                        <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-info text-start">
                            <i class="bi bi-list-check me-2"></i> View All Expenses
                        </a>
                        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-success text-start">
                            <i class="bi bi-graph-up me-2"></i> Financial Reports
                        </a>
                        <a href="{{ route('finance.payments.index') }}" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-cash-stack me-2"></i> All Payments
                        </a>
                    </div>
                </div>
            </div>

            <!-- Project Spending Overview with VAT -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Project Spending (with VAT breakdown)</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($projectSpending as $project)
                        @php
                            $totalCompleted = $project['total_payments'];
                            $baseCompleted = $project['total_base_payments'];
                            $vatCompleted = $project['total_vat_payments'];
                            $pendingTotal = $project['pending_payments_total'] ?? 0;
                            $pendingBase = $project['pending_payments_base'] ?? 0;
                            $pendingVat = $project['pending_payments_vat'] ?? 0;
                            $hasPending = $pendingTotal > 0;
                        @endphp
                        @if($totalCompleted > 0 || $hasPending)
                            <div class="mb-3 pb-3 border-bottom">
                                <h6 class="mb-1">{{ $project['name'] }}</h6>
                                
                                <!-- Completed Payments -->
                                @if($totalCompleted > 0)
                                    <div class="mb-2">
                                        <div class="small text-muted mb-1">Completed Payments:</div>
                                        <div class="row small">
                                            <div class="col-6">
                                                <div class="text-success">Base:</div>
                                                <div class="text-info">VAT:</div>
                                                <div class="fw-bold">Total:</div>
                                            </div>
                                            <div class="col-6 text-end">
                                                <div class="text-success">UGX {{ number_format($baseCompleted, 2) }}</div>
                                                <div class="text-info">UGX {{ number_format($vatCompleted, 2) }}</div>
                                                <div class="fw-bold">UGX {{ number_format($totalCompleted, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Pending Payments -->
                                @if($hasPending)
                                    <div class="mb-2">
                                        <div class="small text-warning mb-1">Pending Payments:</div>
                                        <div class="row small">
                                            <div class="col-6">
                                                <div class="text-success">Base:</div>
                                                <div class="text-info">VAT:</div>
                                                <div class="fw-bold">Total:</div>
                                            </div>
                                            <div class="col-6 text-end">
                                                <div class="text-success">UGX {{ number_format($pendingBase, 2) }}</div>
                                                <div class="text-info">UGX {{ number_format($pendingVat, 2) }}</div>
                                                <div class="fw-bold">UGX {{ number_format($pendingTotal, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Expenses -->
                                @if($project['total_expenses'] > 0)
                                    <div class="mt-2 pt-2 border-top">
                                        <div class="text-danger">Expenses: UGX {{ number_format($project['total_expenses'], 2) }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                            No project spending data available.
                        </div>
                    @endforelse
                    
                    <!-- Total Summary with VAT -->
                    @if($projectSpending->count() > 0)
                        @php
                            $grandTotalCompleted = $projectSpending->sum('total_payments');
                            $grandBaseCompleted = $projectSpending->sum('total_base_payments');
                            $grandVatCompleted = $projectSpending->sum('total_vat_payments');
                            $grandTotalPending = $projectSpending->sum('pending_payments_total');
                            $grandBasePending = $projectSpending->sum('pending_payments_base');
                            $grandVatPending = $projectSpending->sum('pending_payments_vat');
                            $grandTotalExpenses = $projectSpending->sum('total_expenses');
                        @endphp
                        <div class="mt-3 pt-3 border-top bg-light p-3 rounded">
                            <h6 class="mb-2">Summary Totals:</h6>
                            
                            <!-- Completed Totals -->
                            @if($grandTotalCompleted > 0)
                                <div class="mb-2">
                                    <div class="small text-muted mb-1">Completed:</div>
                                    <div class="row small">
                                        <div class="col-6">
                                            <div class="text-success">Base:</div>
                                            <div class="text-info">VAT:</div>
                                            <div class="fw-bold">Total:</div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <div class="text-success">UGX {{ number_format($grandBaseCompleted, 2) }}</div>
                                            <div class="text-info">UGX {{ number_format($grandVatCompleted, 2) }}</div>
                                            <div class="fw-bold">UGX {{ number_format($grandTotalCompleted, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Pending Totals -->
                            @if($grandTotalPending > 0)
                                <div class="mb-2">
                                    <div class="small text-warning mb-1">Pending:</div>
                                    <div class="row small">
                                        <div class="col-6">
                                            <div class="text-success">Base:</div>
                                            <div class="text-info">VAT:</div>
                                            <div class="fw-bold">Total:</div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <div class="text-success">UGX {{ number_format($grandBasePending, 2) }}</div>
                                            <div class="text-info">UGX {{ number_format($grandVatPending, 2) }}</div>
                                            <div class="fw-bold">UGX {{ number_format($grandTotalPending, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Expenses Total -->
                            @if($grandTotalExpenses > 0)
                                <div class="mt-2 pt-2 border-top">
                                    <div class="row small">
                                        <div class="col-6">
                                            <div class="text-danger">Total Expenses:</div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <div class="text-danger">UGX {{ number_format($grandTotalExpenses, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Icon circles for cleaner design */
.icon-circle {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.icon-circle-sm {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

/* Card improvements */
.stat-card .card-body {
    padding: 1.25rem;
}
.card-title {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Table improvements */
.table th {
    font-weight: 600;
    font-size: 0.875rem;
}
.table td {
    vertical-align: middle;
}

/* Number formatting */
h2.fw-bold {
    font-size: 2rem;
    line-height: 1.2;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    h2.fw-bold {
        font-size: 1.75rem;
    }
    .icon-circle {
        width: 36px;
        height: 36px;
    }
}
</style>
@endsection