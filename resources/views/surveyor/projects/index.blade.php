@extends('surveyor.layouts.app')

@section('title', 'My Projects')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Projects</h2>
            <p class="text-muted mb-0">Projects assigned to you as Surveyor</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $projects->count() }}</h4>
                            <small>Total Projects</small>
                        </div>
                        <i class="bi bi-building fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            @php
                                $totalMilestones = $projects->sum('milestones_count');
                            @endphp
                            <h4 class="mb-0">{{ $totalMilestones }}</h4>
                            <small>Total Milestones</small>
                        </div>
                        <i class="bi bi-flag fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            @php
                                $completed = 0;
                                foreach ($projects as $project) {
                                    $completed += $project->milestones->where('status', 'completed')->count();
                                }
                            @endphp
                            <h4 class="mb-0">{{ $completed }}</h4>
                            <small>Completed Milestones</small>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            @php
                                $inProgress = 0;
                                foreach ($projects as $project) {
                                    $inProgress += $project->milestones->where('status', 'in_progress')->count();
                                }
                            @endphp
                            <h4 class="mb-0">{{ $inProgress }}</h4>
                            <small>In Progress</small>
                        </div>
                        <i class="bi bi-clock fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects List -->
    <div class="card shadow-sm">
        <div class="card-body">
            @forelse($projects as $project)
                <div class="card border mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="mb-2">{{ $project->name }}</h5>
                                <p class="text-muted mb-3">{{ $project->location }}</p>
                                
                                <!-- Progress -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">Overall Progress</small>
                                        <small class="text-muted">{{ $project->progress }}%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: {{ $project->progress }}%"></div>
                                    </div>
                                </div>

                                <!-- Milestone Summary -->
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <h6 class="mb-0 text-primary">{{ $project->milestones->where('status', 'pending')->count() }}</h6>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <h6 class="mb-0 text-info">{{ $project->milestones->where('status', 'in_progress')->count() }}</h6>
                                            <small class="text-muted">In Progress</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <h6 class="mb-0 text-success">{{ $project->milestones->where('status', 'completed')->count() }}</h6>
                                            <small class="text-muted">Completed</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="border rounded p-2">
                                            <h6 class="mb-0 text-danger">{{ $project->milestones->where('status', 'delayed')->count() }}</h6>
                                            <small class="text-muted">Delayed</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end ms-3">
                                <a href="{{ route('surveyor.milestones.index', $project) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-flag"></i> View Milestones
                                </a>
                                <div class="mt-2">
                                    <span class="badge bg-{{ $project->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No Projects Assigned</h4>
                    <p class="text-muted">You haven't been assigned to any projects yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection