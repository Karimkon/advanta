@extends('finance.layouts.app')

@section('title', 'Financial Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Financial Reports</h2>
            <p class="text-muted mb-0">Comprehensive financial analysis and insights</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.reports.export.summary') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export Summary
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Payments</h6>
                            <h2 class="fw-bold text-primary mb-1">UGX {{ number_format($totalPayments, 2) }}</h2>
                            <small class="text-muted">All time payments</small>
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
                            <h2 class="fw-bold text-info mb-1">UGX {{ number_format($totalExpenses, 2) }}</h2>
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
                            <h6 class="card-subtitle text-muted mb-2">Pending Payments</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $pendingPayments }}</h2>
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
                            <h6 class="card-subtitle text-muted mb-2">Net Total</h6>
                            <h2 class="fw-bold text-success mb-1">UGX {{ number_format($totalPayments + $totalExpenses, 2) }}</h2>
                            <small class="text-muted">Payments + Expenses</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-graph-up fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
      <!-- Project Spending -->
<div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-pie-chart text-primary me-2"></i>
                Project Spending
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Project</th>
                            <th>Payments</th>
                            <th>Expenses</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projectSpending as $project)
                            @php
                                // Handle both array and object access
                                $projectId = is_array($project) ? $project['id'] : $project->id;
                                $projectName = is_array($project) ? $project['name'] : $project->name;
                                $totalPayments = is_array($project) ? $project['total_payments'] : $project->total_payments;
                                $totalExpenses = is_array($project) ? $project['total_expenses'] : $project->total_expenses;
                                $totalSpent = is_array($project) ? $project['total_spent'] : $project->total_spent;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $projectName }}</strong>
                                </td>
                                <td class="text-success">UGX {{ number_format($totalPayments, 2) }}</td>
                                <td class="text-danger">UGX {{ number_format($totalExpenses, 2) }}</td>
                                <td class="fw-bold">UGX {{ number_format($totalSpent, 2) }}</td>
                                <td>
                                    <a href="{{ route('finance.reports.project', $projectId) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-folder-x display-4 d-block mb-2"></i>
                                    No project spending data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

        <!-- Payment Methods -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card text-success me-2"></i>
                        Payment Methods
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Method</th>
                                    <th>Count</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentMethods as $method)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary text-capitalize">
                                                {{ str_replace('_', ' ', $method->payment_method) }}
                                            </span>
                                        </td>
                                        <td>{{ $method->count }}</td>
                                        <td class="fw-bold">UGX {{ number_format($method->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="bi bi-credit-card display-4 d-block mb-2"></i>
                                            No payment method data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row g-4 mt-4">
        <!-- Recent Payments -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-warning me-2"></i>
                        Recent Payments
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                    <tr>
                                        <td>{{ $payment->formatted_paid_on ?? $payment->created_at->format('M d, Y') }}</td>
                                        <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
                                        <td class="fw-bold">UGX {{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-secondary text-capitalize">
                                                {{ str_replace('_', ' ', $payment->payment_method) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No recent payments found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cash-coin text-danger me-2"></i>
                        Recent Expenses
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Project</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentExpenses as $expense)
                                    <tr>
                                        <td>{{ $expense->created_at->format('M d, Y') }}</td>
                                        <td>{{ Str::limit($expense->description, 30) }}</td>
                                        <td>{{ $expense->project->name ?? 'N/A' }}</td>
                                        <td class="fw-bold text-danger">UGX {{ number_format($expense->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No recent expenses found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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