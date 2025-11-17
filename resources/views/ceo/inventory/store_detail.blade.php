@extends('ceo.layouts.app')

@section('title', $store->display_name . ' - Inventory Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $store->display_name }} - Inventory Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('ceo.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ceo.inventory.index') }}">Inventory Overview</a></li>
                    <li class="breadcrumb-item active">{{ $store->display_name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>

    <!-- Store Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $storeStats['total_items'] }}</h4>
                    <small>Total Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $storeStats['in_stock_items'] }}</h4>
                    <small>In Stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $storeStats['low_stock_items'] }}</h4>
                    <small>Low Stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $storeStats['out_of_stock_items'] }}</h4>
                    <small>Out of Stock</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Value Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-body text-center">
                    <h3 class="mb-1">UGX {{ number_format($storeStats['total_value'], 2) }}</h3>
                    <small class="opacity-75">Total Inventory Value</small>
                    <div class="mt-2">
                        <small>Total Quantity: {{ number_format($storeStats['total_quantity']) }} units</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Inventory Items -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam text-primary me-2"></i>
                        Inventory Items
                    </h5>
                    <div class="text-muted small">
                        {{ $inventoryItems->count() }} items
                    </div>
                </div>
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
                                        <td>
                                            <strong>UGX {{ number_format($item->quantity * $item->unit_price, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($item->quantity <= 0)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @elseif($item->quantity < $item->reorder_level)
                                                <span class="badge bg-warning">Low Stock</span>
                                            @else
                                                <span class="badge bg-success">In Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                No inventory items found in this store.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Store Info & Recent Movements -->
        <div class="col-lg-4">
            <!-- Store Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-info me-2"></i>
                        Store Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Store Name:</th>
                            <td>{{ $store->display_name }}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>
                                <span class="badge bg-{{ $store->isMainStore() ? 'primary' : 'info' }}">
                                    {{ $store->isMainStore() ? 'Main Store' : 'Project Store' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Project:</th>
                            <td>{{ $store->project->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td>{{ $store->location ?? 'Not specified' }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $store->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Recent Stock Movements -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-warning me-2"></i>
                        Recent Stock Movements
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($recentMovements as $movement)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-badge bg-{{ $movement->type === 'in' ? 'success' : ($movement->type === 'out' ? 'warning' : 'info') }} bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-arrow-{{ $movement->type === 'in' ? 'down' : ($movement->type === 'out' ? 'up' : 'left-right') }}-circle text-{{ $movement->type === 'in' ? 'success' : ($movement->type === 'out' ? 'warning' : 'info') }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $movement->item->name }}</h6>
                                        <p class="mb-1 small">
                                            <span class="badge bg-{{ $movement->type === 'in' ? 'success' : ($movement->type === 'out' ? 'warning' : 'info') }}">
                                                {{ strtoupper($movement->type) }}
                                            </span>
                                            <strong class="text-{{ $movement->type === 'in' ? 'success' : ($movement->type === 'out' ? 'warning' : 'info') }}">
                                                {{ $movement->type === 'in' ? '+' : ($movement->type === 'out' ? '-' : '±') }}{{ $movement->quantity }}
                                            </strong>
                                            {{ $movement->item->unit }}
                                        </p>
                                        @if($movement->notes)
                                            <p class="mb-1 small text-muted">
                                                <i class="bi bi-chat-left"></i> {{ Str::limit($movement->notes, 40) }}
                                            </p>
                                        @endif
                                        <small class="text-muted">
                                            {{ $movement->created_at->diffForHumans() }}
                                            @if($movement->user)
                                                • by {{ $movement->user->name }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-activity display-4 d-block mb-2"></i>
                                <p>No recent stock movements</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-badge {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
</style>
@endsection