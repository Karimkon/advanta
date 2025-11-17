@extends('stores.layouts.app')

@section('title', $store->project->name . ' - Inventory Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $store->project->name }} - Inventory</h2>
            <p class="text-muted mb-0">Manage stock levels and track inventory</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('stores.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <a href="{{ route('stores.inventory.create', $store) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Item
            </a>
        </div>
    </div>

    <!-- Inventory Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $stats['total_items'] }}</h4>
                    <small>Total Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    @php
                        $inStockCount = $inventoryItems->filter(function($item) {
                            return $item->stock_status === 'in_stock';
                        })->count();
                    @endphp
                    <h4 class="mb-0">{{ $inStockCount }}</h4>
                    <small>In Stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    @php
                        $lowStockCount = $inventoryItems->filter(function($item) {
                            return $item->stock_status === 'low_stock';
                        })->count();
                    @endphp
                    <h4 class="mb-0">{{ $lowStockCount }}</h4>
                    <small>Low Stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    @php
                        $outOfStockCount = $inventoryItems->filter(function($item) {
                            return $item->stock_status === 'out_of_stock';
                        })->count();
                    @endphp
                    <h4 class="mb-0">{{ $outOfStockCount }}</h4>
                    <small>Out of Stock</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if($item->description)
                                        <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->category }}</td>
                                <td>
                                    <strong>{{ $item->quantity }}</strong> {{ $item->unit }}
                                    @if($item->reorder_level)
                                        <br><small class="text-muted">Reorder: {{ $item->reorder_level }}</small>
                                    @endif
                                </td>
                                <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                <td>UGX {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $item->getStockStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->stock_status)) }}
                                    </span>
                                </td>
                                <td>{{ $item->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('stores.inventory.show', [$store, $item]) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-warning" title="Adjust Stock"
                                                data-bs-toggle="modal" data-bs-target="#adjustItemModal{{ $item->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Adjust Stock Modal for this item -->
                            <div class="modal fade" id="adjustItemModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Adjust Stock - {{ $item->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('stores.inventory.adjust', [$store, $item]) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Current Stock</label>
                                                    <input type="text" class="form-control" value="{{ $item->quantity }} {{ $item->unit }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Adjustment Type</label>
                                                    <select name="adjustment_type" class="form-select" required>
                                                        <option value="add">Add Stock</option>
                                                        <option value="remove">Remove Stock</option>
                                                        <option value="set">Set Exact Quantity</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" name="quantity" class="form-control" 
                                                           step="0.01" min="0.01" required placeholder="Enter quantity">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="3" 
                                                              placeholder="Reason for adjustment..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Apply Adjustment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No inventory items found in this store.
                                        <p class="mt-2">
                                            <a href="{{ route('stores.inventory.create', $store) }}" class="btn btn-primary">
                                                Add Your First Item
                                            </a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($inventoryItems->hasPages())
                <div class="card-footer bg-white">
                    {{ $inventoryItems->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection