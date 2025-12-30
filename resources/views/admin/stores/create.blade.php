@extends('admin.layouts.app')
@section('title', 'Create Store')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Create New Store</h2>
            <p class="text-muted mb-0">Add a new store to the system</p>
        </div>
        <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Stores
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.stores.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Store Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Store Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="e.g., STORE-MAIN" required>
                        <small class="text-muted">Unique identifier for the store</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Store Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="main" {{ old('type') === 'main' ? 'selected' : '' }}>Main Store</option>
                            <option value="project" {{ old('type') === 'project' ? 'selected' : '' }}>Project Store</option>
                            <option value="warehouse" {{ old('type') === 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Associated Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">No Project (Standalone)</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Required for project stores</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Create Store
                    </button>
                    <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
