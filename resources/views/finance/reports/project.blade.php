@extends('finance.layouts.app')

@section('title', 'Project Financial Report - ' . $project->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Project Financial Report</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.reports.index') }}">Financial Reports</a></li>
                    <li class="breadcrumb-item active">{{ $project->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    <!-- Project Summary -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Total Payments</h6>
                    <h3 class="text-success">UGX {{ number_format($project->payments->sum('total_amount'), 2) }}</h3>
                    <small class="text-muted">{{ $project->payments->count() }} payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Total Expenses</h6>
                    <h3 class="text-danger">UGX {{ number_format($project->expenses->sum('amount'), 2) }}</h3>
                    <small class="text-muted">{{ $project->expenses->count() }} expenses</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Net Total</h6>
                    <h3 class="text-primary">UGX {{ number_format($project->payments->sum('total_amount') + $project->expenses->sum('amount'), 2) }}</h3>
                    <small class="text-muted">Financial activity</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Payments by Month -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payments by Month</h5>
                </div>
                <div class="card-body">
                    @if($paymentsByMonth->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Total Payments</th>
                                        <th>Transactions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentsByMonth as $payment)
                                        <tr>
                                            <td>{{ date('M Y', mktime(0, 0, 0, $payment->month, 1, $payment->year)) }}</td>
                                            <td class="text-success">UGX {{ number_format($payment->total, 2) }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $payment->count ?? 1 }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-credit-card display-4 d-block mb-2"></i>
                            No payment data available.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Expenses by Category -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Expenses by Category</h5>
                </div>
                <div class="card-body">
                    @if($expensesByCategory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Total Amount</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalExpenses = $project->expenses->sum('amount');
                                    @endphp
                                    @foreach($expensesByCategory as $expense)
                                        @php
                                            $percentage = $totalExpenses > 0 ? ($expense->total / $totalExpenses) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $expense->category }}</td>
                                            <td class="text-danger">UGX {{ number_format($expense->total, 2) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-danger" role="progressbar" 
                                                             style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <small>{{ number_format($percentage, 1) }}%</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-cash-coin display-4 d-block mb-2"></i>
                            No expense data available.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Financial Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Category/Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $activities = collect();
                                    
                                    // Add payments
                                    foreach($project->payments->take(10) as $payment) {
                                        $activities->push([
                                            'date' => $payment->paid_on,
                                            'type' => 'payment',
                                            'description' => 'Payment to ' . ($payment->supplier->name ?? 'Supplier'),
                                            'amount' => $payment->amount,
                                            'detail' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                                            'color' => 'success'
                                        ]);
                                    }
                                    
                                    // Add expenses
                                    foreach($project->expenses->take(10) as $expense) {
                                        $activities->push([
                                            'date' => $expense->expense_date,
                                            'type' => 'expense',
                                            'description' => $expense->description,
                                            'amount' => $expense->amount,
                                            'detail' => $expense->category,
                                            'color' => 'danger'
                                        ]);
                                    }
                                    
                                    // Sort by date and take latest 15
                                    $activities = $activities->sortByDesc('date')->take(15);
                                @endphp

                                @forelse($activities as $activity)
                                    <tr>
                                        <td>{{ $activity['date']->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $activity['color'] }}">
                                                {{ ucfirst($activity['type']) }}
                                            </span>
                                        </td>
                                        <td>{{ $activity['description'] }}</td>
                                        <td class="text-{{ $activity['color'] }}">
                                            {{ $activity['type'] === 'payment' ? '+' : '-' }}UGX {{ number_format($activity['amount'], 2) }}
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $activity['detail'] }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No financial activity found for this project.
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
@endsection