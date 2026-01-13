@extends('ceo.layouts.app')

@section('title', 'Financial Reports - CEO Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Financial Reports</h2>
            <p class="text-muted mb-0">Executive financial overview and insights</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ceo.reports.requisitions') }}" class="btn btn-info">
                <i class="bi bi-list-check"></i> Requisitions Report
            </a>
            <div class="btn-group">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('ceo.reports.export.excel') }}"><i class="bi bi-file-earmark-excel me-2"></i>Excel (.xlsx)</a></li>
                    <li><a class="dropdown-item" href="{{ route('ceo.reports.export.pdf') }}"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Executive Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Payments</h6>
                            <h2 class="fw-bold text-primary mb-1">UGX {{ number_format($totalPayments, 2) }}</h2>
                            <small class="text-primary">All company payments</small>
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
                            <small class="text-info">Operational expenses</small>
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
                            <h6 class="card-subtitle text-muted mb-2">CEO Approved</h6>
                            <h2 class="fw-bold text-success mb-1">{{ $ceoStats['total_approved_requisitions'] }}</h2>
                            <small class="text-success">Requisitions approved</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
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
                            <h6 class="card-subtitle text-muted mb-2">Pending Approval</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $ceoStats['pending_ceo_approval'] }}</h2>
                            <small class="text-warning">Awaiting CEO decision</small>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-clock fs-4 text-warning"></i>
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
                        Project Spending Analysis
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
                                    <tr>
                                        <td>
                                            <strong>{{ $project->name }}</strong>
                                        </td>
                                        <td class="text-success">UGX {{ number_format($project->total_payments, 2) }}</td>
                                        <td class="text-danger">UGX {{ number_format($project->total_expenses, 2) }}</td>
                                        <td class="fw-bold">UGX {{ number_format($project->total_spent, 2) }}</td>
                                        <td>
                                            <a href="{{ route('ceo.reports.project', $project->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Details
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

        <!-- Payment Methods & CEO Stats -->
        <div class="col-lg-6">
            <!-- Payment Methods -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card text-success me-2"></i>
                        Payment Methods Breakdown
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
                                            No payment method data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- CEO Approval Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-warning me-2"></i>
                        CEO Approval Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-success">{{ $ceoStats['total_approved_requisitions'] }}</h4>
                            <small class="text-muted">Approved Requisitions</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-primary">{{ $ceoStats['total_approved_lpos'] }}</h4>
                            <small class="text-muted">Approved LPOs</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning">{{ $ceoStats['pending_ceo_approval'] }}</h4>
                            <small class="text-muted">Pending Approval</small>
                        </div>
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
                        Recent Financial Activities
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $combinedActivities = collect();
                                    
                                    // Add payments
                                    foreach($recentPayments as $payment) {
                                        $combinedActivities->push([
                                            'date' => $payment->paid_on ?? $payment->created_at,
                                            'type' => 'payment',
                                            'description' => 'Payment to ' . ($payment->supplier->name ?? 'N/A'),
                                            'amount' => $payment->amount,
                                            'color' => 'success'
                                        ]);
                                    }
                                    
                                    // Add expenses
                                    foreach($recentExpenses as $expense) {
                                        $combinedActivities->push([
                                            'date' => $expense->created_at,
                                            'type' => 'expense',
                                            'description' => $expense->description,
                                            'amount' => $expense->amount,
                                            'color' => 'danger'
                                        ]);
                                    }
                                    
                                    // Sort by date and take latest 10
                                    $combinedActivities = $combinedActivities->sortByDesc('date')->take(10);
                                @endphp
                                
                                @forelse($combinedActivities as $activity)
                                    <tr>
                                        <td>{{ $activity['date']->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $activity['color'] }}">
                                                {{ ucfirst($activity['type']) }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($activity['description'], 40) }}</td>
                                        <td class="fw-bold text-{{ $activity['color'] }}">
                                            UGX {{ number_format($activity['amount'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No recent financial activities found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-info me-2"></i>
                        Monthly Payment Trends
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Total Payments</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyPayments as $monthly)
                                    <tr>
                                        <td>
                                            {{ DateTime::createFromFormat('!m', $monthly->month)->format('F') }} {{ $monthly->year }}
                                        </td>
                                        <td class="fw-bold">UGX {{ number_format($monthly->total, 2) }}</td>
                                        <td>
                                            @if($monthly->total > 0)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">No Data</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            No monthly trend data available.
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