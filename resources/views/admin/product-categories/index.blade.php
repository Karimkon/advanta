@extends('admin.layouts.app')

@section('title', 'Product Categories')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><strong>Product Categories</strong></h5>
            <a href="{{ route('admin.product-categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Category
            </a>
        </div>
        <div class="card-body">
            @if($categories->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-folder-x display-1 text-muted"></i>
                    <h5 class="mt-3">No categories found</h5>
                    <p class="text-muted">Get started by creating your first product category.</p>
                    <a href="{{ route('admin.product-categories.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Category
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Parent</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-folder me-2 text-warning"></i>
                                            <strong>{{ $category->name }}</strong>
                                        </div>
                                        @if($category->description)
                                            <small class="text-muted d-block">{{ Str::limit($category->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($category->parent)
                                            <span class="badge bg-light text-dark">{{ $category->parent->name }}</span>
                                        @else
                                            <span class="badge bg-success">Root</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $category->products_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $category->sort_order }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.product-categories.edit', $category) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $category->id }})"
                                                    {{ !$category->canBeDeleted() ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        @if(!$category->canBeDeleted())
                                            <small class="text-muted d-block mt-1">Cannot delete</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
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
                Are you sure you want to delete this category? This action cannot be undone.
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
function confirmDelete(categoryId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/product-categories/${categoryId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush