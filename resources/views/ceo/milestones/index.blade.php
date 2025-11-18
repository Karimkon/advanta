@extends('ceo.layouts.app')

@section('title', 'Project Milestones - CEO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Project Milestones Overview</h2>
            <p class="text-muted mb-0">Track construction progress across all projects</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $stats['total_projects'] }}</h4>
                    <small>Active Projects</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $stats['total_milestones'] }}</h4>
                    <small>Total Milestones</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $stats['completed_milestones'] }}</h4>
                    <small>Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $stats['overdue_milestones'] }}</h4>
                    <small>Overdue</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-building me-2"></i>Projects Milestone Progress
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Project</th>
                            <th>Location</th>
                            <th>Total Milestones</th>
                            <th>Completed</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Latest Update</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            @php
                                $milestones = $project->milestones;
                                $total = $milestones->count();
                                $completed = $milestones->where('status', 'completed')->count();
                                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                                $overdue = $milestones->where('due_date', '<', now())->where('status', '!=', 'completed')->count();
                                $latestUpdate = $milestones->sortByDesc('updated_at')->first();
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $project->name }}</strong>
                                    @if($overdue > 0)
                                        <span class="badge bg-danger ms-1">{{ $overdue }} overdue</span>
                                    @endif
                                </td>
                                <td>{{ $project->location }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $total }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $completed }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <small>{{ $progress }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $project->status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($latestUpdate)
                                        <small class="text-muted">
                                            {{ $latestUpdate->updated_at->diffForHumans() }}
                                        </small>
                                    @else
                                        <small class="text-muted">No updates</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('ceo.milestones.project', $project) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-flag display-4 d-block mb-2"></i>
                                        No projects with milestones found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Overview -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Milestone Status Distribution</h6>
                </div>
                <div class="card-body">
                    @php
                        $allMilestones = collect();
                        foreach ($projects as $project) {
                            $allMilestones = $allMilestones->merge($project->milestones);
                        }
                        $statusCounts = $allMilestones->groupBy('status')->map->count();
                    @endphp
                    @foreach($statusCounts as $status => $count)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-capitalize">{{ str_replace('_', ' ', $status) }}</span>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">{{ $count }}</span>
                                <span class="badge bg-{{ \App\Models\ProjectMilestone::getStatusBadgeClassStatic($status) }}">
                                    {{ round(($count / $allMilestones->count()) * 100) }}%
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Upcoming Deadlines</h6>
                </div>
                <div class="card-body">
                    @php
                        $upcoming = $allMilestones->where('due_date', '>=', now())
                            ->where('status', '!=', 'completed')
                            ->sortBy('due_date')
                            ->take(5);
                    @endphp
                    @forelse($upcoming as $milestone)
                        <div class="border-start border-3 border-info ps-3 mb-3">
                            <h6 class="mb-1">{{ $milestone->title }}</h6>
                            <small class="text-muted">{{ $milestone->project->name }}</small>
                            <div class="mt-1">
                                <small class="text-info">
                                    Due: {{ $milestone->due_date->format('M d, Y') }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No upcoming deadlines</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.progress {
    border-radius: 10px;
}
</style>
@endpush