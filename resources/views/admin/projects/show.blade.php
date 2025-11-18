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
                                        <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'secondary') }}">
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
                    <h6 class="mb-0"><i class="bi bi-people-fill"></i> Staff Assignments</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-person-badge text-primary"></i> Project Manager</h6>
                            @if($projectManager)
                                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
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
                            <h6><i class="bi bi-shop text-success"></i> Store Manager</h6>
                            @if($storeManager)
                                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
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

                    <!-- Engineers Section -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><i class="bi bi-person-gear text-warning"></i> Assigned Engineers</h6>
                            @if($engineers->count() > 0)
                                <div class="row">
                                    @foreach($engineers as $engineer)
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                <div class="bg-warning rounded-circle p-2 me-3">
                                                    <i class="bi bi-person-gear text-white"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $engineer->name }}</strong>
                                                    <div class="text-muted small">{{ $engineer->email }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted text-center py-3 bg-light rounded">
                                    <i class="bi bi-people display-6 d-block mb-2"></i>
                                    No engineers assigned to this project
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Surveyors Section - NEW -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><i class="bi bi-building text-info"></i> Assigned Surveyors</h6>
                            @if($surveyors->count() > 0)
                                <div class="row">
                                    @foreach($surveyors as $surveyor)
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex align-items-center p-2 bg-light rounded">
                                                <div class="bg-info rounded-circle p-2 me-3">
                                                    <i class="bi bi-building text-white"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $surveyor->name }}</strong>
                                                    <div class="text-muted small">{{ $surveyor->email }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted text-center py-3 bg-light rounded">
                                    <i class="bi bi-building display-6 d-block mb-2"></i>
                                    No surveyors assigned to this project
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
                    <h6 class="mb-0"><i class="bi bi-shop"></i> Project Store</h6>
                </div>
                <div class="card-body">
                    @if($project->stores->count() > 0)
                        @foreach($project->stores as $store)
                            <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                                <div class="bg-info rounded-circle p-2 me-3">
                                    <i class="bi bi-shop text-white"></i>
                                </div>
                                <div>
                                    <strong>{{ $store->name }}</strong>
                                    <div class="text-muted small">Code: {{ $store->code }}</div>
                                    <div class="text-muted small">Location: {{ $store->address }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted text-center py-3 bg-light rounded">
                            <i class="bi bi-shop display-4 d-block mb-2"></i>
                            No store created for this project
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Project Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <i class="bi bi-file-text text-primary display-6 d-block"></i>
                                <strong class="d-block mt-2">{{ $project->requisitions->count() }}</strong>
                                <small class="text-muted">Requisitions</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <i class="bi bi-shop text-success display-6 d-block"></i>
                                <strong class="d-block mt-2">{{ $project->stores->count() }}</strong>
                                <small class="text-muted">Stores</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <i class="bi bi-person-gear text-warning display-6 d-block"></i>
                                <strong class="d-block mt-2">{{ $engineers->count() }}</strong>
                                <small class="text-muted">Engineers</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <i class="bi bi-building text-info display-6 d-block"></i>
                                <strong class="d-block mt-2">{{ $surveyors->count() }}</strong>
                                <small class="text-muted">Surveyors</small>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <i class="bi bi-people text-secondary display-6 d-block"></i>
                                <strong class="d-block mt-2">{{ $project->users->count() }}</strong>
                                <small class="text-muted">Total Staff</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection