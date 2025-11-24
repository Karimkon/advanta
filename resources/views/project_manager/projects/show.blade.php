@extends('project_manager.layouts.app')

@section('title', $project->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">{{ $project->name }}</h2>
            <p class="text-muted mb-0">{{ $project->code }}</p>
        </div>
        <a href="{{ route('project_manager.projects.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Projects
        </a>
    </div>

    <!-- Project Details -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Project Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Project Code:</strong> {{ $project->code }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'warning') }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Start Date:</strong> 
                                {{ $project->start_date ? $project->start_date->format('M d, Y') : 'Not set' }}
                            </p>
                            <p><strong>End Date:</strong> 
                                {{ $project->end_date ? $project->end_date->format('M d, Y') : 'Not set' }}
                            </p>
                        </div>
                    </div>
                    @if($project->description)
                    <div class="mt-3">
                        <strong>Description:</strong>
                        <p class="text-muted">{{ $project->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-xl-4">
            <div class="row">
                <div class="col-sm-6 col-xl-12 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Requisitions
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $requisitionStats['total'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-file-text fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-12 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $requisitionStats['pending'] }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Requisitions -->
    <div class="card shadow">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Recent Requisitions</h6>
            <a href="{{ route('project_manager.requisitions.create') }}?project={{ $project->id }}" 
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> New Requisition
            </a>
        </div>
        <div class="card-body">
            @if($project->requisitions->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->requisitions as $requisition)
                        <tr>
                            <td>{{ $requisition->ref }}</td>
                            <td>
                                <span class="badge bg-{{ $requisition->type === 'purchase' ? 'info' : 'secondary' }}">
                                    {{ ucfirst($requisition->type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $requisition->status === 'approved' ? 'success' : ($requisition->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ str_replace('_', ' ', ucfirst($requisition->status)) }}
                                </span>
                            </td>
                            <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
                            <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('project_manager.requisitions.show', $requisition) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4">
                <i class="bi bi-file-text display-4 text-muted"></i>
                <p class="text-muted mt-3">No requisitions found for this project.</p>
                <a href="{{ route('project_manager.requisitions.create') }}?project={{ $project->id }}" 
                   class="btn btn-primary">
                    Create First Requisition
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection