@extends('admin.layouts.app')

@section('title', 'Company Equipments - Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">🚜 Company Equipments</h2>
            <p class="text-muted mb-0">View and manage all company equipment</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-truck text-primary fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">Total Equipment</p>
                            <h4 class="fw-bold mb-0">{{ number_format($stats['total_count']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-currency-exchange text-success fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">Total Value</p>
                            <h4 class="fw-bold mb-0 text-success">UGX {{ number_format($stats['total_value'], 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-check-circle text-info fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">Active Equipment</p>
                            <h4 class="fw-bold mb-0">{{ number_format($stats['active_count']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                            <i class="bi bi-cash-stack text-warning fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">Active Value</p>
                            <h4 class="fw-bold mb-0 text-warning">UGX {{ number_format($stats['active_value'], 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.equipments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, model, serial..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Equipment List -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Equipment</th>
                            <th>Model / Spec</th>
                            <th>Category</th>
                            <th>Project</th>
                            <th>Value</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipments as $equipment)
                            <tr>
                                <td>
                                    @if($equipment->primary_image)
                                        <img src="{{ asset('storage/' . $equipment->primary_image) }}" 
                                             alt="{{ $equipment->name }}" 
                                             class="rounded" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bi bi-truck text-muted fs-4"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $equipment->name }}</div>
                                    @if($equipment->serial_number)
                                        <small class="text-muted">S/N: {{ $equipment->serial_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-dark">{{ $equipment->model }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $equipment->category_label }}</span>
                                </td>
                                <td>
                                    @if($equipment->project)
                                        <a href="{{ route('admin.projects.show', $equipment->project) }}" class="text-primary text-decoration-none">
                                            {{ $equipment->project->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">UGX {{ number_format($equipment->value, 0) }}</span>
                                </td>
                                <td>
                                    @php
                                        $conditionColors = [
                                            'new' => 'success',
                                            'good' => 'primary',
                                            'fair' => 'warning',
                                            'poor' => 'danger',
                                            'needs_repair' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $conditionColors[$equipment->condition] ?? 'secondary' }}">
                                        {{ $equipment->condition_label }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'active' => 'success',
                                            'inactive' => 'secondary',
                                            'maintenance' => 'warning',
                                            'disposed' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$equipment->status] ?? 'secondary' }}">
                                        {{ $equipment->status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.equipments.show', $equipment) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.equipments.edit', $equipment) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-truck fs-1 d-block mb-3"></i>
                                        <p class="mb-0">No equipment found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($equipments->hasPages())
            <div class="card-footer">
                {{ $equipments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
