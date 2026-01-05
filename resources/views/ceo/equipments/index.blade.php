@extends('ceo.layouts.app')

@section('title', 'Company Equipments - CEO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">🚜 Company Equipments</h2>
            <p class="text-muted mb-0">Overview of all company equipment and their total worth</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 p-3 rounded-3 me-3">
                            <i class="bi bi-truck fs-4"></i>
                        </div>
                        <div>
                            <p class="mb-1 small opacity-75">Total Equipment</p>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_count']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 p-3 rounded-3 me-3">
                            <i class="bi bi-currency-exchange fs-4"></i>
                        </div>
                        <div>
                            <p class="mb-1 small opacity-75">Total Equipment Worth</p>
                            <h3 class="fw-bold mb-0">UGX {{ number_format($stats['total_value'], 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 p-3 rounded-3 me-3">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <div>
                            <p class="mb-1 small opacity-75">Active Equipment</p>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['active_count']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-50 p-3 rounded-3 me-3">
                            <i class="bi bi-wrench fs-4"></i>
                        </div>
                        <div>
                            <p class="mb-1 small">Under Maintenance</p>
                            <h3 class="fw-bold mb-0">{{ number_format($stats['maintenance_count']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Value by Category -->
    <div class="row g-4 mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Equipment Value by Category</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($valueByCategory as $category => $data)
                            <div class="col-md-3 col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-secondary">{{ $categories[$category] ?? ucfirst(str_replace('_', ' ', $category)) }}</span>
                                        <span class="text-muted small">{{ $data['count'] }} items</span>
                                    </div>
                                    <h5 class="fw-bold text-success mb-0">UGX {{ number_format($data['value'], 0) }}</h5>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">
                                <i class="bi bi-pie-chart fs-1 d-block mb-2"></i>
                                No equipment data available
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('ceo.equipments.index') }}" class="row g-3">
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
                                        <span class="text-primary">{{ $equipment->project->name }}</span>
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
                                    <a href="{{ route('ceo.equipments.show', $equipment) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
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
