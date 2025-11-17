@extends('ceo.layouts.app')

@section('title', 'Stock Movements - CEO Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Stock Movements</h2>
            <p class="text-muted mb-0">Track all inventory transactions across all stores</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.inventory.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-funnel text-primary me-2"></i>
                Filters
            </h5>
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('ceo.inventory.movements') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Store</label>
                        <select name="store_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Movement Type</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock IN</option>
                            <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock OUT</option>
                            <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-arrow-left-right text-success me-2"></i>
                Stock Movement History
            </h5>
            <div class="text-muted small">
                Showing {{ $movements->count() }} of {{ $movements->total() }} records
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Store</th>
                            <th>Item Details</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Balance After</th>
                            <th>User</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">{{ $movement->created_at->format('M d, Y') }}</small>
                                        <small class="text-muted">{{ $movement->created_at->format('H:i:s') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $movement->item->store->display_name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $movement->item->store->project->name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong>{{ $movement->item->name ?? 'Unknown Item' }}</strong>
                                        <small class="text-muted">SKU: {{ $movement->item->sku ?? 'N/A' }}</small>
                                        <small class="text-muted">Category: {{ $movement->item->category ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($movement->type === 'in')
                                        <span class="badge bg-success">
                                            <i class="bi bi-arrow-down-circle"></i> IN
                                        </span>
                                    @elseif($movement->type === 'out')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-arrow-up-circle"></i> OUT
                                        </span>
                                    @else
                                        <span class="badge bg-info">
                                            <i class="bi bi-arrow-left-right"></i> ADJUST
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="{{ $movement->type === 'in' ? 'text-success' : ($movement->type === 'out' ? 'text-warning' : 'text-info') }}">
                                            {{ $movement->type === 'in' ? '+' : ($movement->type === 'out' ? '-' : '±') }}{{ $movement->quantity }}
                                        </strong>
                                        <small class="text-muted">{{ $movement->item->unit ?? '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small>UGX {{ number_format($movement->unit_price, 2) }}</small>
                                </td>
                                <td>
                                    <strong class="{{ $movement->type === 'in' ? 'text-success' : ($movement->type === 'out' ? 'text-warning' : 'text-info') }}">
                                        UGX {{ number_format($movement->quantity * $movement->unit_price, 2) }}
                                    </strong>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong>{{ $movement->balance_after }}</strong>
                                        <small class="text-muted">{{ $movement->item->unit ?? '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small>{{ $movement->user->name ?? 'System' }}</small>
                                </td>
                                <td>
                                    @if($movement->notes)
                                        <small class="text-muted" data-bs-toggle="tooltip" title="{{ $movement->notes }}">
                                            <i class="bi bi-chat-left-text"></i> {{ Str::limit($movement->notes, 30) }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No stock movements found for the selected filters.
                                        <p class="mt-2 small">Try adjusting your filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($movements->hasPages())
                <div class="card-footer bg-white">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Statistics -->
    @if($movements->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-info me-2"></i>
                        Movement Summary
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $totalIn = $movements->where('type', 'in')->sum('quantity');
                        $totalOut = $movements->where('type', 'out')->sum('quantity');
                        $totalAdjustments = $movements->where('type', 'adjustment')->count();
                        $totalValueIn = $movements->where('type', 'in')->sum(function($m) { return $m->quantity * $m->unit_price; });
                        $totalValueOut = $movements->where('type', 'out')->sum(function($m) { return $m->quantity * $m->unit_price; });
                    @endphp
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ $totalIn }}</h4>
                                <small class="text-muted">Total Stock IN</small>
                                <br>
                                <small class="text-success">UGX {{ number_format($totalValueIn, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">{{ $totalOut }}</h4>
                                <small class="text-muted">Total Stock OUT</small>
                                <br>
                                <small class="text-warning">UGX {{ number_format($totalValueOut, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-info mb-1">{{ $totalAdjustments }}</h4>
                                <small class="text-muted">Adjustments</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">{{ $movements->total() }}</h4>
                                <small class="text-muted">Total Movements</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
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

.border {
    border: 1px solid #dee2e6 !important;
    border-radius: 8px;
}
</style>

@push('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Set default dates (last 30 days) if not set
    const dateFrom = document.querySelector('input[name="date_from"]');
    const dateTo = document.querySelector('input[name="date_to"]');
    
    if (!dateFrom.value) {
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        dateFrom.value = thirtyDaysAgo.toISOString().split('T')[0];
    }
    
    if (!dateTo.value) {
        dateTo.value = new Date().toISOString().split('T')[0];
    }
});
</script>
@endpush
@endsection