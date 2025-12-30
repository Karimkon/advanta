@extends('admin.layouts.app')
@section('title', 'Inventory Management')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Inventory Management</h2>
            <p class="text-muted mb-0">Overview of all inventory across stores</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-circle me-1"></i> Add New Item
        </button>
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-boxes text-primary fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $inventoryItems->count() }}</h3>
                            <small class="text-muted">Total Items</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-stack text-success fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ number_format($inventoryItems->sum('quantity'), 0) }}</h3>
                            <small class="text-muted">Total Quantity</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $inventoryItems->filter(fn($i) => $i->quantity < $i->reorder_level)->count() }}</h3>
                            <small class="text-muted">Low Stock Items</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-currency-dollar text-info fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ number_format($inventoryItems->sum(fn($i) => $i->quantity * $i->unit_price), 0) }}</h3>
                            <small class="text-muted">Total Value (UGX)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Filter by Store</label>
                    <select id="storeFilter" class="form-select">
                        <option value="">All Stores</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name or SKU...">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="clearFilters()">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="inventoryTable">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>SKU</th>
                            <th>Store</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryItems as $item)
                            @php
                                $status = 'in_stock';
                                if ($item->quantity <= 0) {
                                    $status = 'out_of_stock';
                                } elseif ($item->quantity < $item->reorder_level) {
                                    $status = 'low_stock';
                                }
                            @endphp
                            <tr data-store="{{ $item->store_id }}" data-status="{{ $status }}">
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if($item->description)
                                        <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                    @endif
                                </td>
                                <td><code>{{ $item->sku }}</code></td>
                                <td>
                                    @if($item->store)
                                        <span class="badge bg-{{ $item->store->type === 'main' ? 'success' : 'info' }}">
                                            {{ $item->store->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item->category }}</td>
                                <td>
                                    <strong>{{ number_format($item->quantity, 0) }}</strong> {{ $item->unit }}
                                    <br><small class="text-muted">Reorder: {{ number_format($item->reorder_level, 0) }}</small>
                                </td>
                                <td>UGX {{ number_format($item->unit_price, 0) }}</td>
                                <td><strong>UGX {{ number_format($item->quantity * $item->unit_price, 0) }}</strong></td>
                                <td>
                                    @if($item->quantity <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($item->quantity < $item->reorder_level)
                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" title="Adjust Stock"
                                                data-bs-toggle="modal" data-bs-target="#adjustModal"
                                                data-item-id="{{ $item->id }}"
                                                data-item-name="{{ $item->name }}"
                                                data-item-quantity="{{ $item->quantity }}"
                                                data-item-unit="{{ $item->unit }}">
                                            <i class="bi bi-sliders"></i>
                                        </button>
                                        <form action="{{ route('admin.inventory.delete', $item) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this item?');" style="display:inline;">
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
                            <tr id="noItemsRow">
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="bi bi-box-seam display-4 d-block mb-2"></i>
                                    No inventory items found across stores.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.inventory.add') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Store <span class="text-danger">*</span></label>
                        <select name="store_id" class="form-select" required>
                            <option value="">Select Store</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" placeholder="Auto-generated if empty">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" value="General">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" placeholder="e.g., pieces, bags" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="0" step="0.001" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" name="unit_price" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" class="form-control" min="0" value="10">
                        <small class="text-muted">Alert when quantity falls below this level</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="adjustForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-sliders me-2"></i>Adjust Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong id="adjustItemName"></strong><br>
                        Current Stock: <strong id="adjustCurrentQty"></strong> <span id="adjustUnit"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="adjustment_type" class="form-select" required>
                            <option value="add">Add Stock</option>
                            <option value="reduce">Reduce Stock</option>
                            <option value="set">Set Exact Quantity</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="0" step="0.001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Reason for adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Filter functionality
document.getElementById('storeFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);
document.getElementById('searchInput').addEventListener('input', filterTable);

function filterTable() {
    const storeValue = document.getElementById('storeFilter').value;
    const statusValue = document.getElementById('statusFilter').value;
    const searchValue = document.getElementById('searchInput').value.toLowerCase();

    const rows = document.querySelectorAll('#inventoryTable tbody tr:not(#noItemsRow)');
    let visibleCount = 0;

    rows.forEach(row => {
        const storeMatch = !storeValue || row.dataset.store === storeValue;
        const statusMatch = !statusValue || row.dataset.status === statusValue;
        const text = row.textContent.toLowerCase();
        const searchMatch = !searchValue || text.includes(searchValue);

        if (storeMatch && statusMatch && searchMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
}

function clearFilters() {
    document.getElementById('storeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('searchInput').value = '';
    filterTable();
}

// Adjust Modal
const adjustModal = document.getElementById('adjustModal');
adjustModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const itemId = button.getAttribute('data-item-id');
    const itemName = button.getAttribute('data-item-name');
    const itemQty = button.getAttribute('data-item-quantity');
    const itemUnit = button.getAttribute('data-item-unit');

    document.getElementById('adjustItemName').textContent = itemName;
    document.getElementById('adjustCurrentQty').textContent = parseFloat(itemQty).toLocaleString();
    document.getElementById('adjustUnit').textContent = itemUnit;
    document.getElementById('adjustForm').action = `/admin/inventory/${itemId}/adjust`;
});
</script>
@endpush
@endsection
