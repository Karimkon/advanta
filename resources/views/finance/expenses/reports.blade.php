@extends('finance.layouts.app')

@section('title', 'Expense Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Expense Reports</h2>
            <p class="text-muted mb-0">Monthly expense analysis and breakdown</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.expenses.export') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Expenses
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
                            <h6 class="card-subtitle text-muted mb-2">Total Expenses</h6>
                            <h2 class="fw-bold text-primary mb-1">UGX {{ number_format($expenses->sum('amount'), 2) }}</h2>
                            <small class="text-muted">This month</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-cash-coin fs-4 text-primary"></i>
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
                            <h6 class="card-subtitle text-muted mb-2">Categories</h6>
                            <h2 class="fw-bold text-info mb-1">{{ $categoryTotals->count() }}</h2>
                            <small class="text-muted">Expense categories</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-tags fs-4 text-info"></i>
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
                            <h6 class="card-subtitle text-muted mb-2">Projects</h6>
                            <h2 class="fw-bold text-success mb-1">{{ $projectTotals->count() }}</h2>
                            <small class="text-muted">Active projects</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-folder2 fs-4 text-success"></i>
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
                            <h6 class="card-subtitle text-muted mb-2">Average Expense</h6>
                            <h2 class="fw-bold text-warning mb-1">UGX {{ number_format($expenses->avg('amount'), 2) }}</h2>
                            <small class="text-muted">Per transaction</small>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-graph-up fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Category Breakdown -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Expenses by Category</h5>
                </div>
                <div class="card-body">
                    @if($categoryTotals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalAmount = $expenses->sum('amount');
                                    @endphp
                                    @foreach($categoryTotals as $category => $amount)
                                        @php
                                            $percentage = $totalAmount > 0 ? ($amount / $totalAmount) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $category }}</td>
                                            <td>UGX {{ number_format($amount, 2) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar" role="progressbar" 
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
                            <i class="bi bi-pie-chart display-4 d-block mb-2"></i>
                            No expense data available for analysis.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Project Breakdown -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Expenses by Project</h5>
                </div>
                <div class="card-body">
                    @if($projectTotals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Amount</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projectTotals as $project => $amount)
                                        @php
                                            $percentage = $totalAmount > 0 ? ($amount / $totalAmount) * 100 : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $project }}</td>
                                            <td>UGX {{ number_format($amount, 2) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
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
                            <i class="bi bi-folder-x display-4 d-block mb-2"></i>
                            No project expense data available.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Expenses (This Month)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Project</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Receipt No.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses->take(10) as $expense)
                                    <tr>
                                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                        <td>{{ $expense->project->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-info">{{ $expense->category }}</span></td>
                                        <td>{{ Str::limit($expense->description, 60) }}</td>
                                        <td>UGX {{ number_format($expense->amount, 2) }}</td>
                                        <td>{{ $expense->receipt_number ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No expenses found for this month.
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
.progress {
    background-color: #e9ecef;
}
</style>
@endsection