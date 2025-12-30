@extends('admin.layouts.app')
@section('title', 'Edit Store')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit Store</h2>
            <p class="text-muted mb-0">{{ $store->name }}</p>
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
            <form action="{{ route('admin.stores.update', $store) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Store Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $store->name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Store Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $store->code) }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Store Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="project" {{ old('type', $store->type) === 'project' ? 'selected' : '' }}>Project Store</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Associated Project</label>
                        <select name="project_id" class="form-select">
                            <option value="">No Project (Standalone)</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $store->project_id) == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $store->address) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Store Manager</label>
                    <select name="manager_id" class="form-select">
                        <option value="">No Manager Assigned</option>
                        @foreach($availableManagers as $manager)
                            <option value="{{ $manager->id }}" {{ old('manager_id', $store->manager?->id) == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }} ({{ $manager->email }})
                                @if($manager->shop_id == $store->id)
                                    - Current Manager
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Only users with 'stores' role who are not assigned to other stores are shown</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Update Store
                    </button>
                    <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
