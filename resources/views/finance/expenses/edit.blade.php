@extends('finance.layouts.app')

@section('title', 'Edit Expense')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit Expense</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.expenses.index') }}">Expenses</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.expenses.show', $expense) }}">#{{ $expense->id }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.expenses.show', $expense) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Expense Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.expenses.update', $expense) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="project_id" class="form-label">Project *</label>
                                <select class="form-select" id="project_id" name="project_id" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id', $expense->project_id) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ old('type', $expense->type) == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description', $expense->description) }}</textarea>
                                @error('description')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount (UGX) *</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                       value="{{ old('amount', $expense->amount) }}" required>
                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="incurred_on" class="form-label">Expense Date *</label>
                                <input type="date" class="form-control" id="incurred_on" name="incurred_on" 
                                       value="{{ old('incurred_on', $expense->incurred_on ? $expense->incurred_on->format('Y-m-d') : '') }}" required>
                                @error('incurred_on')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="unpaid" {{ old('status', $expense->status) == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="paid" {{ old('status', $expense->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $expense->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Update Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection