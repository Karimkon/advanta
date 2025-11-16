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

    <!-- Financial Overview Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Payments</h6>
                            <h2 class="fw-bold text-success mb-1">UGX {{ number_format($totalPayments, 2) }}</h2>
                            <small class="text-success">All time payments</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-credit-card fs-4 text-success"></i>
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
                            <h2 class="fw-bold text-danger mb-1">UGX {{ number_format($totalExpenses, 2) }}</h2>
                            <small class="text-danger">All time expenses</small>
                        </div>
                        <div class="icon-wrapper bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-cash-coin fs-4 text-danger"></i>
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
                            <h2 class="fw-bold text-primary mb-1">UGX {{ number_format($totalPayments + $totalExpenses, 2) }}</h2>
                            <small class="text-primary">Payments + Expenses</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-graph-up fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Monthly Trends -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Monthly Payments Trend</h5>
                </div>
                <div class="card-body">
                    @if($monthlyPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Total Payments</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthlyPayments->reverse() as $payment)
                                        <tr>
                                            <td>{{ date('M Y', mktime(0, 0, 0, $payment->month, 1, $payment->year)) }}</td>
                                            <td>UGX {{ number_format($payment->total, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $payment->total > 0 ? 'success' : 'secondary' }}">
                                                    {{ $payment->total > 0 ? 'Active' : 'No Data' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-graph-up display-4 d-block mb-2"></i>
                            No payment data available for trend analysis.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Monthly Expenses -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Monthly Expenses Trend</h5>
                </div>
                <div class="card-body">
                    @if($monthlyExpenses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Total Expenses</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthlyExpenses->reverse() as $expense)
                                        <tr>
                                            <td>{{ date('M Y', mktime(0, 0, 0, $expense->month, 1, $expense->year)) }}</td>
                                            <td>UGX {{ number_format($expense->total, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $expense->total > 0 ? 'danger' : 'secondary' }}">
                                                    {{ $expense->total > 0 ? 'Active' : 'No Data' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-graph-down display-4 d-block mb-2"></i>
                            No expense data available for trend analysis.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Project-wise Breakdown -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Project-wise Financial Breakdown</h5>
                    <a href="{{ route('finance.reports.export.summary') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-download"></i> Export
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Project</th>
                                    <th>Total Payments</th>
                                    <th>Total Expenses</th>
                                    <th>Net Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectBreakdown as $project)
                                    <tr>
                                        <td>
                                            <strong>{{ $project['name'] }}</strong>
                                        </td>
                                        <td class="text-success">
                                            +UGX {{ number_format($project['total_payments'], 2) }}
                                        </td>
                                        <td class="text-danger">
                                            -UGX {{ number_format($project['total_expenses'], 2) }}
                                        </td>
                                        <td class="fw-bold">
                                            UGX {{ number_format($project['net_total'], 2) }}
                                        </td>
                                        <td>
                                            @php
                                                $ratio = $project['total_payments'] > 0 ? ($project['total_expenses'] / $project['total_payments']) * 100 : 0;
                                            @endphp
                                            <span class="badge bg-{{ $ratio < 80 ? 'success' : ($ratio < 100 ? 'warning' : 'danger') }}">
                                                {{ number_format($ratio, 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('finance.reports.project', $project['id'] ?? 0) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-folder-x display-4 d-block mb-2"></i>
                                            No project financial data available.
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