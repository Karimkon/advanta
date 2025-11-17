@extends('stores.layouts.app')

@section('title', $store->display_name . ' - Stock Movements')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $store->display_name }} - Stock Movements</h2>
            <p class="text-muted mb-0">Track all inventory transactions and movements</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('stores.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-download"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $summary['total_movements'] }}</h4>
                    <small>Total Movements</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $summary['stock_ins'] }}</h4>
                    <small>Stock IN</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $summary['stock_outs'] }}</h4>
                    <small>Stock OUT</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $summary['adjustments'] }}</h4>
                    <small>Adjustments</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Value Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-body text-center">
                    <h3 class="mb-1">UGX {{ number_format($summary['total_value_moved'], 2) }}</h3>
                    <small class="opacity-75">Total Value of All Stock Movements</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> Filters
            </h5>
        </div>
        <div class="card-body">
            <form id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Movement Type</label>
                        <select name="type" class="form-select" onchange="applyFilters()">
                            <option value="all">All Types</option>
                            <option value="in">Stock IN</option>
                            <option value="out">Stock OUT</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item</label>
                        <select name="item_id" class="form-select" onchange="applyFilters()">
                            <option value="">All Items</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" onchange="applyFilters()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" onchange="applyFilters()">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Stock Movement History
            </h5>
            <div class="text-muted small">
                Showing {{ $movements->count() }} of {{ $movements->total() }} records
            </div>
        </div>
        <div class="card-body p-0">
            <div id="movementsTable">
                @include('stores.movements.partials.movements_table')
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-download"></i> Export Stock Movements
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Export all stock movements to CSV format for reporting and analysis.</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    The export will include:
                    <ul class="mb-0 mt-2">
                        <li>All movement types (IN, OUT, Adjustments)</li>
                        <li>Complete transaction details</li>
                        <li>Financial values and quantities</li>
                        <li>User and timestamp information</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('stores.movements.export', $store) }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Export to CSV
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Show loading
    document.getElementById('movementsTable').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading movements...</p>
        </div>
    `;

    fetch('{{ route("stores.movements.filter", $store) }}?' + new URLSearchParams(formData))
        .then(response => response.text())
        .then(html => {
            document.getElementById('movementsTable').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('movementsTable').innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle"></i> Error loading movements
                </div>
            `;
        });
}

// Set default dates (last 30 days)
document.addEventListener('DOMContentLoaded', function() {
    const dateTo = new Date();
    const dateFrom = new Date();
    dateFrom.setDate(dateFrom.getDate() - 30);
    
    document.querySelector('input[name="date_from"]').value = dateFrom.toISOString().split('T')[0];
    document.querySelector('input[name="date_to"]').value = dateTo.toISOString().split('T')[0];
    
    // Load initial filtered data
    applyFilters();
});
</script>
@endpush