@extends('admin.layouts.app')

@section('title', 'Product Catalog')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><strong>Product Catalog</strong></h5>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.product-catalog.export') }}">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Download Template
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.product-catalog.export-data') }}">
                        <i class="bi bi-database"></i> Export All Data
                    </a></li>
                </ul>
                
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload"></i> Bulk Import
                </button>
                
                <a href="{{ route('admin.product-catalog.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Product
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('admin.product-catalog.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               value="{{ request('search') }}" placeholder="Search products...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
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
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>

            @if($products->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-box display-1 text-muted"></i>
                    <h5 class="mt-3">No products found</h5>
                    <p class="text-muted">Get started by adding your first product to the catalog.</p>
                    <a href="{{ route('admin.product-catalog.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Product
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>SKU</th>
                                <th>Usage</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="bi bi-box text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->description)
                                                    <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
    @if($product->category_id && $product->category && $product->category->name)
        <span class="badge bg-info">{{ $product->category->name }}</span>
    @else
        <span class="text-muted">No Category</span>
    @endif
</td>
                                    <td>{{ $product->unit }}</td>
                                    <td>
                                        @if($product->sku)
                                            <code>{{ $product->sku }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div>Requisitions: <strong>{{ $product->requisition_items_count }}</strong></div>
                                            <div>Inventory: <strong>{{ $product->inventory_items_count }}</strong></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.product-catalog.edit', $product) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $product->id }})"
                                                    {{ !$product->canBeDeleted() ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        @if(!$product->canBeDeleted())
                                            <small class="text-muted d-block mt-1">In use</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
                    </div>
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Import Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.product-catalog.bulk-import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Import Instructions:</h6>
                        <ul class="mb-0 small">
                            <li>Download the template first to see the required format</li>
                            <li>Required fields: <strong>name, category, unit</strong></li>
                            <li>Categories will be created automatically if they don't exist</li>
                            <li>Existing products with same name will be updated</li>
                            <li>Supported formats: <strong>Excel (.xlsx, .xls) or CSV (.csv)</strong></li>
                            <li>Make sure your file has data starting from row 2 (after headers)</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Select File <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" id="import_file" 
                               class="form-control @error('import_file') is-invalid @enderror" 
                               accept=".csv,.txt,.xlsx,.xls" required>
                        <div class="form-text">Accepted formats: CSV, Excel (.xlsx, .xls)</div>
                        @error('import_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- File preview -->
                        <div id="filePreview" class="mt-2 d-none">
                            <div class="card card-body bg-light">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-spreadsheet text-success fs-3 me-3"></i>
                                    <div>
                                        <strong id="fileName"></strong>
                                        <div class="small text-muted" id="fileSize"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session('import_errors'))
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Import Warnings:</h6>
                            <ul class="mb-0 small" style="max-height: 200px; overflow-y: auto;">
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Progress indicator -->
                    <div id="importProgress" class="d-none">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span>Importing products... Please wait.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="importBtn">
                        <i class="bi bi-upload"></i> Import Products
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input preview
    const fileInput = document.getElementById('import_file');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                filePreview.classList.remove('d-none');
            } else {
                filePreview.classList.add('d-none');
            }
        });
    }
    
    // Show progress on form submit
    const importForm = document.getElementById('importForm');
    const importBtn = document.getElementById('importBtn');
    const importProgress = document.getElementById('importProgress');
    
    if (importForm) {
        importForm.addEventListener('submit', function() {
            importBtn.disabled = true;
            importBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Importing...';
            importProgress.classList.remove('d-none');
        });
    }
    
    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    // Auto-show modal if there are import errors
    @if(session('import_errors'))
        const importModal = new bootstrap.Modal(document.getElementById('importModal'));
        importModal.show();
    @endif
});
</script>
@endpush