@extends('surveyor.layouts.app')

@section('title', 'Surveyor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Surveyor Dashboard</h2>
            <p class="text-muted mb-0">Track project milestones and construction progress</p>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $projects->count() }}</h4>
                            <small>Assigned Projects</small>
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
                                $totalMilestones = 0;
                                foreach ($projects as $project) {
                                    $totalMilestones += $project->milestones->count();
                                }
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
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $attentionMilestones->count() }}</h4>
                            <small>Need Attention</small>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
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
                            <small>Completed</small>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Projects Overview -->
        <div class="col-lg-8">
            <!-- Projects List -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>My Projects
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($projects as $project)
                        <div class="card border mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $project->name }}</h6>
                                        <p class="text-muted mb-2">{{ $project->location }}</p>
                                        
                                        <!-- Progress Overview -->
                                        <div class="mb-3">
                                            @php
                                                $projectMilestones = $project->milestones;
                                                $total = $projectMilestones->count();
                                                $completed = $projectMilestones->where('status', 'completed')->count();
                                                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                                            @endphp
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">Overall Progress</small>
                                                <small class="text-muted">{{ $progress }}%</small>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                                            </div>
                                        </div>

                                        <!-- Milestone Status -->
                                        <div class="row text-center">
                                            <div class="col-3">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-primary">{{ $projectMilestones->where('status', 'pending')->count() }}</h6>
                                                    <small class="text-muted">Pending</small>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-info">{{ $projectMilestones->where('status', 'in_progress')->count() }}</h6>
                                                    <small class="text-muted">In Progress</small>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-success">{{ $completed }}</h6>
                                                    <small class="text-muted">Completed</small>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="border rounded p-2">
                                                    <h6 class="mb-0 text-danger">{{ $projectMilestones->where('status', 'delayed')->count() }}</h6>
                                                    <small class="text-muted">Delayed</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <a href="{{ route('surveyor.milestones.index', $project) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-arrow-right"></i> View Milestones
                                        </a>
                                        <div class="mt-2">
                                            <span class="badge bg-{{ $project->status === 'active' ? 'success' : 'warning' }}">
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

        <!-- Sidebar - Attention Needed -->
        <div class="col-lg-4">
            <!-- Attention Needed -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>Attention Needed
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($attentionMilestones as $milestone)
                        <div class="border-start border-3 border-warning ps-3 mb-3">
                            <h6 class="mb-1">{{ $milestone->title }}</h6>
                            <p class="text-muted mb-1 small">{{ $milestone->project->name }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-warning">
                                    <i class="bi bi-calendar me-1"></i>
                                    Due: {{ $milestone->due_date->format('M d, Y') }}
                                </small>
                                <a href="{{ route('surveyor.milestones.edit', ['project' => $milestone->project, 'milestone' => $milestone]) }}" 
                                   class="btn btn-warning btn-sm">
                                    Update
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle display-4"></i>
                            <p class="mt-2 mb-0">All milestones are up to date!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($projects->count() > 0)
                            <a href="{{ route('surveyor.milestones.index', $projects->first()) }}" 
                               class="btn btn-outline-primary text-start">
                                <i class="bi bi-flag me-2"></i> Update Milestones
                            </a>
                        @endif
                        <button class="btn btn-outline-secondary text-start" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i> Print Progress Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.progress {
    border-radius: 10px;
}
.border-start {
    border-left-width: 4px !important;
}
</style>
@endpush