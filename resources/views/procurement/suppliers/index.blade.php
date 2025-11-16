@extends('procurement.layouts.app')

@section('title', 'Supplier Management - Procurement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Supplier Management</h2>
            <p class="text-muted mb-0">Manage all suppliers and vendor relationships</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('procurement.suppliers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Supplier
            </a>
        </div>
    </div>

    @include('procurement.partials.alerts')

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Suppliers</h6>
                            <h2 class="fw-bold text-primary mb-1">{{ $suppliers->total() }}</h2>
                            <small class="text-muted">All suppliers</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-truck fs-4 text-primary"></i>
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
                            <h6 class="card-subtitle text-muted mb-2">Active Suppliers</h6>
                            <h2 class="fw-bold text-success mb-1">{{ $suppliers->where('status', 'active')->count() }}</h2>
                            <small class="text-success">Ready for business</small>
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
                            <h6 class="card-subtitle text-muted mb-2">Categories</h6>
                            <h2 class="fw-bold text-info mb-1">{{ $suppliers->pluck('category')->unique()->count() }}</h2>
                            <small class="text-info">Different categories</small>
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
                            <h6 class="card-subtitle text-muted mb-2">Avg Rating</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ number_format($suppliers->avg('rating') ?? 0, 1) }}</h2>
                            <small class="text-warning">Supplier performance</small>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-star fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Category</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td>
                                    <strong>{{ $supplier->name }}</strong>
                                </td>
                                <td>{{ $supplier->code }}</td>
                                <td>{{ $supplier->contact_person }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->email }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $supplier->category }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="text-warning me-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="bi bi-star{{ $i <= $supplier->rating ? '-fill' : '' }}"></i>
                                            @endfor
                                        </span>
                                        <small class="text-muted">({{ number_format($supplier->rating, 1) }})</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $supplier->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($supplier->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('procurement.suppliers.show', $supplier) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('procurement.suppliers.edit', $supplier) }}" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('procurement.suppliers.destroy', $supplier) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this supplier?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-truck display-4 d-block mb-2"></i>
                                        No suppliers found.
                                        <a href="{{ route('procurement.suppliers.create') }}" class="btn btn-primary mt-2">
                                            Add First Supplier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($suppliers->hasPages())
                <div class="card-footer bg-white">
                    {{ $suppliers->links() }}
                </div>
            @endif
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