@extends('project_manager.layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">My Projects</h2>
    </div>

    <div class="row">
        @forelse($projects as $project)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ $project->code }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $project->name }}
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-calendar me-1"></i>
                                {{ $project->start_date ? $project->start_date->format('M d, Y') : 'No start date' }}
                            </div>
                            @if($project->status)
                            <div class="mt-2">
                                <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'warning') }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-folder2-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="bi bi-file-text me-1"></i>
                                {{ $project->requisitions_count ?? 0 }} reqs
                            </small>
                        </div>
                        <div class="col-6 text-end">
                            <a href="{{ route('project_manager.requisitions.index', ['project' => $project->id]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                View Requisitions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="bi bi-folder-x display-4 text-muted"></i>
                    <h4 class="text-muted mt-3">No Projects Assigned</h4>
                    <p class="text-muted">You haven't been assigned to any projects yet.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($projects->hasPages())
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection