@extends('stores.layouts.app')

@section('title', $inventoryItem->name . ' - Inventory Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $inventoryItem->name }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('stores.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('stores.inventory.index', $store) }}">Inventory</a></li>
                    <li class="breadcrumb-item active">{{ $inventoryItem->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('stores.inventory.index', $store) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Item Details -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Item Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>SKU:</strong> {{ $inventoryItem->sku }}</p>
                            <p><strong>Category:</strong> {{ $inventoryItem->category }}</p>
                            <p><strong>Unit:</strong> {{ $inventoryItem->unit }}</p>
                            <p><strong>Unit Price:</strong> UGX {{ number_format($inventoryItem->unit_price, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Current Stock:</strong> 
                                <span class="fw-bold text-primary">{{ $inventoryItem->quantity }} {{ $inventoryItem->unit }}</span>
                            </p>
                            <p><strong>Reorder Level:</strong> {{ $inventoryItem->reorder_level }} {{ $inventoryItem->unit }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge {{ $inventoryItem->getStockStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $inventoryItem->stock_status)) }}
                                </span>
                            </p>
                            <p><strong>Total Value:</strong> 
                                <span class="fw-bold text-success">
                                    UGX {{ number_format($inventoryItem->quantity * $inventoryItem->unit_price, 2) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    @if($inventoryItem->description)
                        <div class="mt-3">
                            <strong>Description:</strong>
                            <p class="mt-1">{{ $inventoryItem->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#addStockModal">
                                <i class="bi bi-plus-circle"></i> Add Stock
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#removeStockModal">
                                <i class="bi bi-dash-circle"></i> Remove Stock
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#setStockModal">
                                <i class="bi bi-pencil"></i> Set Quantity
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-primary w-100">
                                <i class="bi bi-clock-history"></i> Stock History
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Movement History -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Stock Movements</h5>
                </div>
                <div class="card-body">
                    @forelse($inventoryItem->logs as $log)
                        <div class="border-start border-3 border-primary ps-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong class="text-capitalize">{{ $log->type }}</strong> 
                                    <span class="text-{{ $log->type === 'in' ? 'success' : 'danger' }} fw-bold">
                                        {{ $log->quantity }} {{ $inventoryItem->unit }}
                                    </span>
                                    <br>
                                    <small class="text-muted">By: {{ $log->user->name }}</small>
                                    @if($log->notes)
                                        <br><small>{{ $log->notes }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">{{ $log->created_at->format('M d, Y H:i') }}</small>
                                    <small class="text-primary">Balance: {{ $log->balance_after }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-clock-history display-4 d-block mb-2"></i>
                            No stock movement history yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('stores.inventory.adjust', [$store, $inventoryItem]) }}" method="POST">
                @csrf
                <input type="hidden" name="adjustment_type" value="in">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body  ">
                    <div class="mb-3">
                        <label for="addQuantity" class="form-label">Quantity to Add ({{ $inventoryItem->unit }})</label>
                        <input type="number" class="form-control" id="addQuantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="addNotes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="addNotes" name="notes" rows="2"></textarea>
                    </div>
                </div>      
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

</div>
<!-- Remove Stock Modal -->
<div class="modal fade" id="removeStockModal" tabindex="-1" aria-labelledby="   

removeStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('stores.inventory.adjust', [$store, $inventoryItem]) }}" method="POST">
                @csrf
                <input type="hidden" name="adjustment_type" value="out">
                <div class="modal-header">
                    <h5 class="modal-title" id="removeStockModalLabel">Remove Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="removeQuantity" class="form-label">Quantity to Remove ({{ $inventoryItem->unit }})</label>
                        <input type="number" class="form-control" id="removeQuantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="removeNotes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="removeNotes" name="notes" rows="2"></textarea>
                    </div>
                </div>      
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Remove Stock</button>              
                </div>
            </form> 
        </div>
    </div>
</div>  
<!-- Set Stock Modal -->

<div class="modal fade" id="setStockModal" tabindex="-1" aria-labelledby="setStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('stores.inventory.adjust', [$store, $inventoryItem]) }}" method="POST">
                @csrf
                <input type="hidden" name="adjustment_type" value="set">
                <div class="modal-header">
                    <h5 class="modal-title" id="setStockModalLabel">Set Stock Quantity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body
">
                    <div class="mb-3">
                        <label for="setQuantity" class="form-label">New Quantity ({{ $inventoryItem->unit }})</label>
                        <input type="number" class="form-control" id="setQuantity" name="quantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="setNotes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="setNotes" name="notes" rows="2"></textarea>
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Set Quantity</button> 
                </div>
            </form> 
        </div>
    </div>

</div>
@endsection