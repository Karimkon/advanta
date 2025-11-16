@extends('finance.layouts.app')

@section('title', 'Expense Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Expense Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.expenses.index') }}">Expenses</a></li>
                    <li class="breadcrumb-item active">Expense #{{ $expense->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.expenses.edit', $expense) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Expense Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Expense Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Expense ID:</strong> #{{ $expense->id }}</p>
                            <p><strong>Project:</strong> {{ $expense->project->name ?? 'N/A' }}</p>
                            <p><strong>Type:</strong> 
                                <span class="badge bg-info">{{ $expense->type }}</span>
                            </p>
                            <p><strong>Description:</strong> {{ $expense->description }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Amount:</strong> UGX {{ number_format($expense->amount, 2) }}</p>
                            <p><strong>Expense Date:</strong> 
                                @if($expense->incurred_on)
                                    {{ $expense->incurred_on->format('M d, Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $expense->status === 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </p>
                            <p><strong>Recorded By:</strong> {{ $expense->recordedBy->name ?? 'N/A' }}</p>
                            <p><strong>Created:</strong> {{ $expense->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($expense->notes)
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p class="mt-1">{{ $expense->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('finance.expenses.edit', $expense) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Expense
                        </a>
                        <form action="{{ route('finance.expenses.destroy', $expense) }}" method="POST" class="d-grid">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" 
                                    onclick="return confirm('Are you sure you want to delete this expense?')">
                                <i class="bi bi-trash"></i> Delete Expense
                            </button>
                        </form>
                        <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list"></i> View All Expenses
                        </a>
                    </div>
                </div>
            </div>

            <!-- Project Information -->
            @if($expense->project)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Project:</strong> {{ $expense->project->name }}</p>
                    <p><strong>Code:</strong> {{ $expense->project->code ?? 'N/A' }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $expense->project->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($expense->project->status) }}
                        </span>
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection