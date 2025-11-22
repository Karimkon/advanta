@extends('admin.layouts.app')

@section('title', 'Product Catalog')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Product Catalog</h2>
        <div>
            <a href="{{ route('admin.product-catalog.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload"></i> Bulk Import
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.product-catalog.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Usage Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        @if($product->description)
                                            <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->sku)
                                            <code>{{ $product->sku }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $product->category }}</span>
                                    </td>
                                    <td>{{ $product->unit }}</td>
                                    <td>
                                        <div class="d-flex gap-3">
                                            <span class="text-primary" title="Requisition Usage">
                                                <i class="bi bi-file-text"></i> {{ $product->requisition_items_count }}
                                            </span>
                                            <span class="text-success" title="Inventory Usage">
                                                <i class="bi bi-box"></i> {{ $product->inventory_items_count }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($product->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.product-catalog.edit', $product) }}" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $product->id }}"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete <strong>{{ $product->name }}</strong>?
                                                        @if(!$product->canBeDeleted())
                                                            <div class="alert alert-warning mt-2">
                                                                <i class="bi bi-exclamation-triangle"></i>
                                                                This product is being used in {{ $product->requisition_items_count }} requisitions 
                                                                and {{ $product->inventory_items_count }} inventory items.
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('admin.product-catalog.destroy', $product) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" 
                                                                    {{ !$product->canBeDeleted() ? 'disabled' : '' }}>
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                    </div>
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No products found</h4>
                    <p class="text-muted">Get started by adding your first product to the catalog.</p>
                    <a href="{{ route('admin.product-catalog.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add First Product
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Import Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.product-catalog.bulk-import') }}" method="POST" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Import Format:</strong> Upload a CSV file with columns: name, category, unit, description (optional), sku (optional), specifications (optional)
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Upload CSV File</label>
                        <input type="file" class="form-control" id="csvFile" accept=".csv">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Or Paste CSV Data</label>
                        <textarea class="form-control" rows="10" placeholder="name,category,unit,description,sku,specifications&#10;Cement 50kg,Building Materials,bag,Portland Cement 50kg bag,CEMENT-50KG,Grade 42.5&#10;Steel Bar 12mm,Building Materials,piece,12mm Steel Bar 6m length,STEEL-12MM,Grade 60" id="csvData"></textarea>
                    </div>

                    <div id="previewSection" style="display: none;">
                        <h6>Preview (First 5 rows):</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="previewTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Unit</th>
                                        <th>Description</th>
                                        <th>SKU</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="processImport">Process Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSV Import functionality
    const csvFile = document.getElementById('csvFile');
    const csvData = document.getElementById('csvData');
    const previewSection = document.getElementById('previewSection');
    const previewTable = document.getElementById('previewTable');
    const processImport = document.getElementById('processImport');

    function parseCSV(text) {
        const lines = text.split('\n').filter(line => line.trim());
        const headers = lines[0].split(',').map(h => h.trim());
        
        const data = [];
        for (let i = 1; i < Math.min(lines.length, 6); i++) {
            const values = lines[i].split(',').map(v => v.trim());
            const row = {};
            headers.forEach((header, index) => {
                row[header] = values[index] || '';
            });
            data.push(row);
        }
        return data;
    }

    function updatePreview(data) {
        const tbody = previewTable.querySelector('tbody');
        tbody.innerHTML = '';
        
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.name || ''}</td>
                <td>${row.category || ''}</td>
                <td>${row.unit || ''}</td>
                <td>${row.description || ''}</td>
                <td>${row.sku || ''}</td>
            `;
            tbody.appendChild(tr);
        });
        
        previewSection.style.display = 'block';
    }

    csvFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                csvData.value = e.target.result;
                const data = parseCSV(e.target.result);
                updatePreview(data);
            };
            reader.readAsText(file);
        }
    });

    csvData.addEventListener('input', function() {
        if (csvData.value.trim()) {
            const data = parseCSV(csvData.value);
            updatePreview(data);
        } else {
            previewSection.style.display = 'none';
        }
    });

    processImport.addEventListener('click', function() {
        const csvContent = csvData.value.trim();
        if (!csvContent) {
            alert('Please provide CSV data');
            return;
        }

        const lines = csvContent.split('\n').filter(line => line.trim());
        const headers = lines[0].split(',').map(h => h.trim());
        
        if (!headers.includes('name') || !headers.includes('category') || !headers.includes('unit')) {
            alert('CSV must contain name, category, and unit columns');
            return;
        }

        const products = [];
        for (let i = 1; i < lines.length; i++) {
            const values = lines[i].split(',').map(v => v.trim());
            const product = {};
            headers.forEach((header, index) => {
                product[header] = values[index] || '';
            });
            products.push(product);
        }

        // Submit via AJAX
        fetch('{{ route("admin.product-catalog.bulk-import") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ products: products })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                $('#importModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    });
});
</script>
@endpush
@endsection