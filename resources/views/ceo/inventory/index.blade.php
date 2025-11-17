@extends('ceo.layouts.app')

@section('title', 'Inventory Overview - CEO Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Inventory Overview</h2>
            <p class="text-muted mb-0">Complete inventory management across all stores</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.inventory.export') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export Report
            </a>
            <a href="{{ route('ceo.inventory.movements') }}" class="btn btn-info">
                <i class="bi bi-arrow-left-right"></i> Stock Movements
            </a>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $overallStats['total_stores'] }}</h4>
                    <small>Total Stores</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $overallStats['total_inventory_items'] }}</h4>
                    <small>Total Items</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">UGX {{ number_format($overallStats['total_inventory_value'], 2) }}</h4>
                    <small>Total Value</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $overallStats['total_low_stock_items'] }}</h4>
                    <small>Low Stock Items</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $overallStats['total_out_of_stock_items'] }}</h4>
                    <small>Out of Stock</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4">
            <div class="card bg-dark text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">UGX {{ number_format($overallStats['avg_store_value'], 2) }}</h4>
                    <small>Avg Store Value</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Stores Overview -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building text-primary me-2"></i>
                        Stores Inventory Summary
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Store Name</th>
                                    <th>Project</th>
                                    <th>Type</th>
                                    <th>Items</th>
                                    <th>Quantity</th>
                                    <th>Total Value</th>
                                    <th>Stock Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stores as $store)
                                    <tr>
                                        <td>
                                            <strong>{{ $store['name'] }}</strong>
                                            @if($store['is_main_store'])
                                                <br><small class="text-muted">Main Company Store</small>
                                            @endif
                                        </td>
                                        <td>{{ $store['project']->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $store['is_main_store'] ? 'primary' : 'info' }}">
                                                {{ $store['is_main_store'] ? 'Main Store' : 'Project Store' }}
                                            </span>
                                        </td>
                                        <td>{{ $store['total_items'] }}</td>
                                        <td>{{ number_format($store['total_quantity']) }}</td>
                                        <td>
                                            <strong>UGX {{ number_format($store['total_value'], 2) }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column small">
                                                <span class="text-success">
                                                    <i class="bi bi-check-circle"></i> In Stock: {{ $store['in_stock_items'] }}
                                                </span>
                                                <span class="text-warning">
                                                    <i class="bi bi-exclamation-triangle"></i> Low: {{ $store['low_stock_items'] }}
                                                </span>
                                                <span class="text-danger">
                                                    <i class="bi bi-x-circle"></i> Out: {{ $store['out_of_stock_items'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('ceo.inventory.store', $store['id']) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Valuable Items -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy text-warning me-2"></i>
                        Top 10 Most Valuable Items
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Store</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topValuableItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->name }}</strong>
                                            <br><small class="text-muted">{{ $item->sku }}</small>
                                        </td>
                                        <td>{{ $item->store->display_name }}</td>
                                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td>
                                            <strong class="text-success">
                                                UGX {{ number_format($item->quantity * $item->unit_price, 2) }}
                                            </strong>
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Category Breakdown -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-diagram-3 text-info me-2"></i>
                        Inventory by Category
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Items</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryStats as $category)
                                    <tr>
                                        <td>
                                            <strong>{{ $category->category ?: 'Uncategorized' }}</strong>
                                        </td>
                                        <td>{{ $category->item_count }}</td>
                                        <td class="fw-bold">UGX {{ number_format($category->total_value, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stock Movement Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-left-right text-success me-2"></i>
                        Stock Movement Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">{{ $movementStats['total_movements'] }}</h4>
                                <small class="text-muted">Total Movements</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ $movementStats['stock_ins'] }}</h4>
                                <small class="text-muted">Stock IN</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">{{ $movementStats['stock_outs'] }}</h4>
                                <small class="text-muted">Stock OUT</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-info mb-1">{{ $movementStats['adjustments'] }}</h4>
                                <small class="text-muted">Adjustments</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Total Value Moved: UGX {{ number_format($movementStats['total_value_moved'], 2) }}
                        </small>
                    </div>
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
                                        <small class="text-muted">
                                            {{ $movement->created_at->diffForHumans() }} • {{ $movement->item->store->display_name }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-activity display-4 d-block mb-2"></i>
                                <p>No recent movements</p>
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