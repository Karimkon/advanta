@extends('admin.layouts.app')

@section('title', $equipment->name . ' - Equipment Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.equipments.index') }}">Equipments</a></li>
                    <li class="breadcrumb-item active">{{ $equipment->name }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">{{ $equipment->name }}</h2>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.equipments.edit', $equipment) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
            <a href="{{ route('admin.equipments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Images Carousel -->
            @if($equipment->images && count($equipment->images) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-0">
                        <div id="equipmentCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach($equipment->images as $index => $image)
                                    <button type="button" data-bs-target="#equipmentCarousel" data-bs-slide-to="{{ $index }}" 
                                            class="{{ $index === 0 ? 'active' : '' }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach($equipment->images as $index => $image)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $image) }}" class="d-block w-100 rounded-top" 
                                             alt="{{ $equipment->name }}" style="height: 400px; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                            @if(count($equipment->images) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#equipmentCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#equipmentCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                        <p class="text-muted mt-3">No images available</p>
                    </div>
                </div>
            @endif

            <!-- Equipment Details -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Equipment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-1 small">Equipment Name</p>
                            <p class="fw-semibold mb-0">{{ $equipment->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 small">Model / Specification</p>
                            <p class="fw-semibold mb-0">
                                <span class="badge bg-dark fs-6">{{ $equipment->model }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 small">Category</p>
                            <p class="fw-semibold mb-0">
                                <span class="badge bg-secondary">{{ $equipment->category_label }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 small">Serial Number</p>
                            <p class="fw-semibold mb-0">{{ $equipment->serial_number ?: 'N/A' }}</p>
                        </div>
                        @if($equipment->description)
                            <div class="col-12">
                                <p class="text-muted mb-1 small">Description & Use Case</p>
                                <p class="mb-0">{{ $equipment->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Value Card -->
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">Equipment Value</p>
                    <h2 class="fw-bold text-success mb-0">UGX {{ number_format($equipment->value, 0) }}</h2>
                </div>
            </div>

            <!-- Status & Condition -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Status & Condition</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Status</span>
                        @php
                            $statusColors = [
                                'active' => 'success',
                                'inactive' => 'secondary',
                                'maintenance' => 'warning',
                                'disposed' => 'danger'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$equipment->status] ?? 'secondary' }} fs-6">
                            {{ $equipment->status_label }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Condition</span>
                        @php
                            $conditionColors = [
                                'new' => 'success',
                                'good' => 'primary',
                                'fair' => 'warning',
                                'poor' => 'danger',
                                'needs_repair' => 'dark'
                            ];
                        @endphp
                        <span class="badge bg-{{ $conditionColors[$equipment->condition] ?? 'secondary' }} fs-6">
                            {{ $equipment->condition_label }}
                        </span>
                    </div>
                    @if($equipment->purchase_date)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Purchase Date</span>
                            <span>{{ $equipment->purchase_date->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Location & Assignment -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Location</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted mb-1 small">Assigned Project</p>
                        @if($equipment->project)
                            <a href="{{ route('admin.projects.show', $equipment->project) }}" class="fw-semibold text-primary text-decoration-none">
                                <i class="bi bi-building me-1"></i>{{ $equipment->project->name }}
                            </a>
                        @else
                            <p class="text-muted mb-0">Not assigned to any project</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Current Location</p>
                        <p class="fw-semibold mb-0">
                            <i class="bi bi-pin-map me-1"></i>{{ $equipment->location ?: 'Not specified' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Record Info -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Record Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted mb-1 small">Added By</p>
                        <p class="fw-semibold mb-0">{{ $equipment->addedBy->name ?? 'Unknown' }}</p>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted mb-1 small">Created</p>
                        <p class="mb-0">{{ $equipment->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Last Updated</p>
                        <p class="mb-0">{{ $equipment->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <a href="{{ route('admin.equipments.edit', $equipment) }}" class="btn btn-warning w-100">
                        <i class="bi bi-pencil me-2"></i>Edit Equipment
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
