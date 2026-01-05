@extends('stores.layouts.app')

@section('title', 'Store Manager Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Store Manager Dashboard</h2>
            <p class="text-muted mb-0">
                @if(isset($currentStore))
                    Managing: {{ $currentStore->display_name }}
                @else
                    Store Management Overview
                @endif
            </p>
        </div>
        <div class="store-badge badge px-3 py-2">
            <i class="bi bi-shop"></i> STORE MANAGEMENT
        </div>
    </div>

    @if(!isset($currentStore))
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            No stores assigned to your account. Please contact administration.
        </div>
    @else
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle text-muted mb-2">Total Items</h6>
                                <h2 class="fw-bold text-primary mb-1">{{ $stats['total_items'] ?? 0 }}</h2>
                                <small class="text-primary">Different items</small>
                            </div>
                            <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-box-seam fs-4 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle text-muted mb-2">Total Quantity</h6>
                                <h2 class="fw-bold text-success mb-1">{{ number_format($stats['total_quantity'] ?? 0) }}</h2>
                                <small class="text-success">Units in stock</small>
                            </div>
                            <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-layers fs-4 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle text-muted mb-2">Store Value</h6>
                                <h4 class="fw-bold text-info mb-1">UGX {{ number_format($stats['store_value'] ?? 0) }}</h4>
                                <small class="text-info">Total inventory value</small>
                            </div>
                            <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-currency-dollar fs-4 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle text-muted mb-2">Low Stock</h6>
                                <h2 class="fw-bold text-warning mb-1">{{ $stats['low_stock_items'] ?? 0 }}</h2>
                                <small class="text-warning">Need reordering</small>
                            </div>
                            <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle text-muted mb-2">Out of Stock</h6>
                                <h2 class="fw-bold text-danger mb-1">{{ $stats['out_of_stock_items'] ?? 0 }}</h2>
                                <small class="text-danger">Zero stock items</small>
                            </div>
                            <div class="icon-wrapper bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-x-circle fs-4 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle text-muted mb-2">Pending Releases</h6>
                                <h2 class="fw-bold text-secondary mb-1">{{ $pendingCount ?? 0 }}</h2>
                                <small class="text-secondary">Awaiting action</small>
                            </div>
                            <div class="icon-wrapper bg-secondary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-clock fs-4 text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats Row for LPOs -->
        <div class="row mb-4">
            <!-- Pending LPOs Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-warning h-100">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-truck"></i> LPOs Awaiting Delivery
                        </h6>
                    </div>
                    <div class="card-body">
                        <h3 class="text-warning">{{ $pendingLposCount ?? 0 }}</h3>
                        <p class="mb-2">LPOs waiting for delivery confirmation</p>
                        
                        @if($pendingLposCount > 0)
                            <a href="{{ route('stores.lpos.index', $currentStore) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-arrow-right"></i> Manage Deliveries
                            </a>
                        @else
                            <span class="text-muted">No pending deliveries</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm border-primary h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-lightning"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('stores.inventory.index', $currentStore) }}" class="btn btn-outline-primary btn-sm text-start">
                                <i class="bi bi-box-seam"></i> Manage Inventory
                            </a>
                            <a href="{{ route('stores.releases.index', $currentStore) }}" class="btn btn-outline-primary btn-sm text-start">
                                <i class="bi bi-arrow-up-right"></i> Process Releases
                            </a>
                            <a href="{{ route('stores.lpos.index', $currentStore) }}" class="btn btn-outline-primary btn-sm text-start">
                                <i class="bi bi-truck"></i> LPO Deliveries
                            </a>
                            <a href="{{ route('stores.movements.index', $currentStore) }}" class="btn btn-outline-primary btn-sm text-start">
                                <i class="bi bi-arrow-left-right"></i> Stock Movements
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Card -->
            <div class="col-xl-6 col-md-12">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bi bi-activity"></i> Recent Activity
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($recentReleases->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($recentReleases->take(3) as $release)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Release #{{ $release->id }}</h6>
                                                <small class="text-muted">For {{ $release->requisition->ref }}</small>
                                            </div>
                                            <span class="badge bg-success">{{ $release->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-info-circle display-6"></i>
                                <p class="mt-2 mb-0">No recent activity</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Current Inventory -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Current Inventory</h5>
                        <a href="{{ route('stores.inventory.index', $currentStore) }}" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        @forelse($inventoryItems as $item)
                            <div class="border-start border-3 border-primary ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        <p class="mb-1 text-muted small">{{ $item->description }}</p>
                                        <div class="d-flex align-items-center">
                                            <span class="badge {{ $item->getStockStatusBadgeClass() }} me-2">
                                                {{ ucfirst(str_replace('_', ' ', $item->stock_status)) }}
                                            </span>
                                            <span class="text-primary fw-bold">{{ $item->quantity }} {{ $item->unit }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">UGX {{ number_format($item->unit_price, 2) }}</small>
                                        <small class="text-success">UGX {{ number_format($item->quantity * $item->unit_price, 2) }}</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                No inventory items found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Low Stock Alerts</h5>
                        <span class="badge bg-warning">{{ $lowStockItems->count() }} Items</span>
                    </div>
                    <div class="card-body">
                        @forelse($lowStockItems as $item)
                            <div class="border-start border-3 border-warning ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        <p class="mb-1 text-muted small">Current: {{ $item->quantity }} {{ $item->unit }}</p>
                                        <p class="mb-0 text-warning small">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Reorder level: {{ $item->reorder_level }} {{ $item->unit }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Need to order</small>
                                        <span class="badge bg-warning">Low Stock</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                                No low stock alerts. Good job!
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Store Requisitions -->
        @if($pendingRequisitions->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Pending Store Requisitions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Requisition</th>
                                        <th>Project</th>
                                        <th>Requested By</th>
                                        <th>Items</th>
                                        <th>Urgency</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequisitions as $requisition)
                                        <tr>
                                            <td><strong>{{ $requisition->ref }}</strong></td>
                                            <td>{{ $requisition->project->name ?? 'N/A' }}</td>
                                            <td>{{ $requisition->requester->name }}</td>
                                            <td>{{ $requisition->items->count() }} items</td>
                                            <td>
                                                <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                                    {{ ucfirst($requisition->urgency) }}
                                                </span>
                                            </td>
                                            <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('stores.releases.create', ['store' => $currentStore, 'requisition' => $requisition]) }}" 
                                                   class="btn btn-sm btn-outline-primary">Process</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
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