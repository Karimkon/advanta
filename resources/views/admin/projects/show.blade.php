@extends('admin.layouts.app')
@section('title', 'Project Details - ' . $project->name)
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Project Information</h5>
                    <div class="btn-group">
                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="140">Name:</th>
                                    <td>{{ $project->name }}</td>
                                </tr>
                                <tr>
                                    <th>Code:</th>
                                    <td>{{ $project->code }}</td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>{{ $project->location }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $project->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Timeline & Budget</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="140">Start Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>End Date:</th>
                                    <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <th>Budget:</th>
                                    <td>UGX {{ number_format($project->budget, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($project->description)
                    <div class="mt-3">
                        <h6>Description</h6>
                        <p class="text-muted">{{ $project->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Staff Assignments -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Staff Assignments</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Project Manager</h6>
                            @if($projectManager)
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle p-2 me-3">
                                        <i class="bi bi-person-fill text-white"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $projectManager->name }}</strong>
                                        <div class="text-muted small">{{ $projectManager->email }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-exclamation-triangle"></i> No project manager assigned
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Store Manager</h6>
                            @if($storeManager)
                                <div class="d-flex align-items-center">
                                    <div class="bg-success rounded-circle p-2 me-3">
                                        <i class="bi bi-person-fill text-white"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $storeManager->name }}</strong>
                                        <div class="text-muted small">{{ $storeManager->email }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-exclamation-triangle"></i> No store manager assigned
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Project Store -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Project Store</h6>
                </div>
                <div class="card-body">
                    @if($project->stores->count() > 0)
                        @foreach($project->stores as $store)
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-info rounded-circle p-2 me-3">
                                    <i class="bi bi-shop text-white"></i>
                                </div>
                                <div>
                                    <strong>{{ $store->name }}</strong>
                                    <div class="text-muted small">Code: {{ $store->code }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted text-center">
                            <i class="bi bi-shop display-4 d-block mb-2"></i>
                            No store created for this project
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Project Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="text-primary">
                                <i class="bi bi-file-text display-6 d-block"></i>
                                <strong>{{ $project->requisitions->count() }}</strong>
                                <div class="small">Requisitions</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="text-success">
                                <i class="bi bi-shop display-6 d-block"></i>
                                <strong>{{ $project->stores->count() }}</strong>
                                <div class="small">Stores</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection