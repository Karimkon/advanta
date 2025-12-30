@extends('admin.layouts.app')
@section('title', 'Stores Management')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Stores Management</h2>
            <p class="text-muted mb-0">Manage all stores and their managers</p>
        </div>
        <a href="{{ route('admin.stores.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create New Store
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-shop text-primary fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $stores->count() }}</h3>
                            <small class="text-muted">Project Stores</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-boxes text-warning fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $totalInventoryItems }}</h3>
                            <small class="text-muted">Total Items</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-currency-dollar text-success fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">UGX {{ number_format($stores->sum(fn($s) => $s->getStoreValue()), 0) }}</h3>
                            <small class="text-muted">Total Value</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stores Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Store Name</th>
                            <th>Code</th>
                            <th>Project</th>
                            <th>Manager</th>
                            <th>Items</th>
                            <th>Total Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stores as $store)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info bg-opacity-10 p-2 rounded-circle me-2">
                                            <i class="bi bi-folder text-info"></i>
                                        </div>
                                        <strong>{{ $store->name }}</strong>
                                    </div>
                                </td>
                                <td><code>{{ $store->code }}</code></td>
                                <td>
                                    @if($store->project)
                                        <a href="{{ route('admin.projects.show', $store->project) }}" class="text-decoration-none">
                                            {{ $store->project->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($store->store_manager)
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success bg-opacity-10 p-1 rounded-circle me-2">
                                                <i class="bi bi-person-check text-success"></i>
                                            </div>
                                            <div>
                                                <small class="d-block fw-semibold">{{ $store->store_manager->name }}</small>
                                                <small class="text-muted">{{ $store->store_manager->phone ?? $store->store_manager->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle me-1"></i>No Manager
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $store->inventoryItems->count() }} items</span>
                                </td>
                                <td>
                                    <strong>UGX {{ number_format($store->getStoreValue(), 0) }}</strong>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.stores.show', $store) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.stores.edit', $store) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-shop display-4 d-block mb-2"></i>
                                    No project stores found. Create your first store to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
