@extends('stores.layouts.app')

@section('title', 'Add New Inventory Item - ' . $store->project->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Add New Inventory Item</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('stores.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('stores.inventory.index', $store) }}">Inventory</a></li>
                    <li class="breadcrumb-item active">Add Item</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('stores.inventory.index', $store) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Item Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stores.inventory.store', $store) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Item Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" 
                                       placeholder="Enter item name" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <input type="text" name="category" class="form-control" value="{{ old('category') }}" 
                                       placeholder="e.g., Construction, Electrical, Plumbing" required>
                                @error('category')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" 
                                          placeholder="Item description...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit *</label>
                                <input type="text" name="unit" class="form-control" value="{{ old('unit') }}" 
                                       placeholder="e.g., bags, pieces, kg" required>
                                @error('unit')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Initial Quantity *</label>
                                <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 0) }}" 
                                       step="0.01" min="0" required>
                                @error('quantity')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit Price (UGX) *</label>
                                <input type="number" name="unit_price" class="form-control" value="{{ old('unit_price', 0) }}" 
                                       step="0.01" min="0" required>
                                @error('unit_price')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reorder Level *</label>
                                <input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level', 10) }}" 
                                       step="0.01" min="0" required>
                                <div class="form-text">Alert when stock falls below this level</div>
                                @error('reorder_level')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="track_per_project" value="1" 
                                           id="trackPerProject" {{ old('track_per_project', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="trackPerProject">
                                        Track inventory per project
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Inventory Item
                            </button>
                            <a href="{{ route('stores.inventory.index', $store) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection