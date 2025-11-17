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

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Pending Payments</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $stats['pending_payments'] }}</h2>
                            <small class="text-warning">Awaiting processing</small>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-clock fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">This Month Payments</h6>
                            <h2 class="fw-bold text-primary mb-1">{{ $stats['total_payments_this_month'] }}</h2>
                            <small class="text-muted">{{ number_format($stats['total_amount_this_month'], 2) }} UGX</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-credit-card fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Expenses</h6>
                            <h2 class="fw-bold text-info mb-1">UGX {{ number_format($stats['total_expenses'], 2) }}</h2>
                            <small class="text-muted">All time expenses</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-cash-coin fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Overdue Payments</h6>
                            <h2 class="fw-bold text-danger mb-1">{{ $stats['overdue_payments'] }}</h2>
                            <small class="text-danger">Require attention</small>
                        </div>
                        <div class="icon-wrapper bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Payments -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock text-warning me-2"></i>
                        Pending Payments
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
                                    <th>Amount</th>
                                    <th>LPO Number</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingPayments as $requisition)
                                    <tr>
                                        <td>
                                            <strong>{{ $requisition->ref }}</strong>
                                        </td>
                                        <td>{{ $requisition->project->name ?? 'N/A' }}</td>
                                        <td>{{ $requisition->supplier->name ?? 'N/A' }}</td>
                                        <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                                        <td>
                                            @if($requisition->lpo)
                                                {{ $requisition->lpo->lpo_number }}
                                            @else
                                                <span class="text-muted">No LPO</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('finance.payments.create', $requisition) }}" 
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-credit-card"></i> Process Payment
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
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

          <!-- Recent Payments -->
@if($recentPayments->count() > 0)
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-credit-card text-success me-2"></i>
            Recent Payments
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payment Date</th>
                        <th>Supplier</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->formatted_paid_on }}</td>
                            <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">
                                    {{ $payment->payment_method ? str_replace('_', ' ', $payment->payment_method) : 'Not set' }}
                                </span>
                            </td>
                            <td>UGX {{ number_format($payment->amount, 2) }}</td>
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
                    </div>
                </div>
            </div>

           <!-- Project Spending Overview -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Project Spending</h5>
    </div>
    <div class="card-body">
        @forelse($projectSpending as $project)
            @php
                $totalSpending = $project['total_payments'] + $project['total_expenses'];
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <div>
                    <h6 class="mb-1">{{ $project['name'] }}</h6>
                    <small class="text-muted">Total: UGX {{ number_format($totalSpending, 2) }}</small>
                </div>
                <div class="text-end">
                    <div class="text-success small">+UGX {{ number_format($project['total_payments'], 2) }}</div>
                    <div class="text-danger small">-UGX {{ number_format($project['total_expenses'], 2) }}</div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-3">
                <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                No project data available.
            </div>
        @endforelse
        
        <!-- Total Summary -->
        @if($projectSpending->count() > 0)
            @php
                $grandTotalPayments = $projectSpending->sum('total_payments');
                $grandTotalExpenses = $projectSpending->sum('total_expenses');
                $grandTotal = $grandTotalPayments + $grandTotalExpenses;
            @endphp
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Grand Total:</strong>
                    <strong>UGX {{ number_format($grandTotal, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between text-success small">
                    <span>Total Payments:</span>
                    <span>+UGX {{ number_format($grandTotalPayments, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between text-danger small">
                    <span>Total Expenses:</span>
                    <span>-UGX {{ number_format($grandTotalExpenses, 2) }}</span>
                </div>
            </div>
        @endif
    </div>
</div>
        </div>
    </div>
</div>

<style>
.icon-wrapper {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection