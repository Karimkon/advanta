@extends('finance.layouts.app')

@section('title', 'Expenses Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Expenses Management</h2>
            <p class="text-muted mb-0">Track and manage all project expenses</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.expenses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Expense
            </a>
            <a href="{{ route('finance.expenses.reports') }}" class="btn btn-info">
                <i class="bi bi-graph-up"></i> Reports
            </a>
            <div class="btn-group">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('finance.expenses.export.excel') }}"><i class="bi bi-file-earmark-excel me-2"></i>Excel (.xlsx)</a></li>
                    <li><a class="dropdown-item" href="{{ route('finance.expenses.export.pdf') }}"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</a></li>
                    <li><a class="dropdown-item" href="{{ route('finance.expenses.export') }}"><i class="bi bi-file-earmark-text me-2"></i>CSV</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('type') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
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
                    <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Date</th>
                            <th>Project</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Recorded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>
                                    @if($expense->incurred_on)
                                        {{ $expense->incurred_on->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $expense->project->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $expense->type }}</span>
                                </td>
                                <td>{{ Str::limit($expense->description, 50) }}</td>
                                <td>UGX {{ number_format($expense->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $expense->status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($expense->status) }}
                                    </span>
                                </td>
                                <td>{{ $expense->recordedBy->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('finance.expenses.show', $expense) }}" 
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('finance.expenses.edit', $expense) }}" 
                                           class="btn btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('finance.expenses.destroy', $expense) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this expense?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-cash-coin display-4 d-block mb-2"></i>
                                        No expenses found.
                                        <p class="mt-2">No expense records available.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($expenses->hasPages())
                <div class="card-footer bg-white">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection