@extends('admin.layouts.app')
@section('title', $store->name)
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $store->name }}</h2>
            <p class="text-muted mb-0">
                <span class="badge bg-{{ $store->type === 'main' ? 'success' : 'info' }}">{{ ucfirst($store->type) }}</span>
                <code class="ms-2">{{ $store->code }}</code>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stores.edit', $store) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Store
            </a>
            <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Store Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Store Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Type</td>
                            <td><span class="badge bg-{{ $store->type === 'main' ? 'success' : 'info' }}">{{ ucfirst($store->type) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Address</td>
                            <td>{{ $store->address ?: 'Not specified' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Project</td>
                            <td>
                                @if($store->project)
                                    <a href="{{ route('admin.projects.show', $store->project) }}">{{ $store->project->name }}</a>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Manager</td>
                            <td>
                                @if($store->store_manager)
                                    <div>
                                        <strong>{{ $store->store_manager->name }}</strong><br>
                                        <small class="text-muted">{{ $store->store_manager->phone ?? $store->store_manager->email }}</small>
                                    </div>
                                @else
                                    <span class="badge bg-warning text-dark">No Manager</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-md-8 mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-boxes text-primary fs-1 mb-2 d-block"></i>
                            <h3 class="mb-0">{{ $store->inventoryItems->count() }}</h3>
                            <small class="text-muted">Total Items</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-stack text-success fs-1 mb-2 d-block"></i>
                            <h3 class="mb-0">{{ number_format($store->getTotalQuantity(), 0) }}</h3>
                            <small class="text-muted">Total Quantity</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-dollar text-info fs-1 mb-2 d-block"></i>
                            <h3 class="mb-0">{{ number_format($store->getStoreValue(), 0) }}</h3>
                            <small class="text-muted">Total Value (UGX)</small>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $lowStockItems = $store->getLowStockItems();
            @endphp
            @if($lowStockItems->count() > 0)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>{{ $lowStockItems->count() }}</strong> item(s) are below reorder level
                </div>
            @endif
        </div>
    </div>

    <!-- Inventory Items Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Inventory Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($store->inventoryItems as $item)
                            <tr>
                                <td><code>{{ $item->sku }}</code></td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category }}</td>
                                <td>{{ number_format($item->quantity, 0) }} {{ $item->unit }}</td>
                                <td>UGX {{ number_format($item->unit_price, 0) }}</td>
                                <td>UGX {{ number_format($item->quantity * $item->unit_price, 0) }}</td>
                                <td>
                                    @if($item->quantity <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($item->quantity < $item->reorder_level)
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-box display-4 d-block mb-2"></i>
                                    No inventory items in this store.
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
