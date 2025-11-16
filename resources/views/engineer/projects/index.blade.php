@extends('engineer.layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Projects</h2>
            <p class="text-muted mb-0">Projects assigned to you</p>
        </div>
    </div>

    <div class="row">
        @forelse($projects as $project)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title">{{ $project->name }}</h5>
                            <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'secondary') }}">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>
                        
                        <p class="text-muted small mb-3">{{ $project->description ?: 'No description available' }}</p>
                        
                        <div class="project-details">
                            <div class="mb-2">
                                <i class="bi bi-geo-alt text-primary"></i>
                                <span class="ms-2">{{ $project->location }}</span>
                            </div>
                            <div class="mb-2">
                                <i class="bi bi-calendar text-primary"></i>
                                <span class="ms-2">{{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="mb-2">
                                <i class="bi bi-cash-coin text-primary"></i>
                                <span class="ms-2">UGX {{ number_format($project->budget, 2) }}</span>
                            </div>
                            @if($project->users->where('role', 'project_manager')->first())
                            <div class="mb-2">
                                <i class="bi bi-person text-primary"></i>
                                <span class="ms-2">PM: {{ $project->users->where('role', 'project_manager')->first()->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-grid">
                            <a href="{{ route('engineer.requisitions.create') }}?project_id={{ $project->id }}" 
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Create Requisition
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-x display-4 text-muted d-block mb-3"></i>
                        <h4 class="text-muted">No Projects Assigned</h4>
                        <p class="text-muted">You haven't been assigned to any projects yet.</p>
                        <p class="text-muted small">Please contact your Project Manager or Administrator to be assigned to projects.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection