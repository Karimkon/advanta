@extends('client.layouts.app')
@section('title', 'My Projects')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <h2 class="mb-2">Welcome back, {{ Auth::guard('client')->user()->name }}! 👋</h2>
                    <p class="mb-0 opacity-75">Track your project progress and milestones in real-time</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-briefcase-fill text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Total Projects</h6>
                            <h3 class="mb-0">{{ $totalProjects }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Active Projects</h6>
                            <h3 class="mb-0">{{ $activeProjects }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="bi bi-flag-fill text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Total Milestones</h6>
                            <h3 class="mb-0">{{ $totalMilestones }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="bi bi-graph-up text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Avg. Progress</h6>
                            <h3 class="mb-0">{{ number_format($averageProgress, 1) }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>My Projects</h5>
                </div>
                <div class="card-body p-0">
                    @if($projects->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No projects assigned yet</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Project Name</th>
                                        <th class="border-0">Location</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Progress</th>
                                        <th class="border-0">Milestones</th>
                                        <th class="border-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $project)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $project->name }}</div>
                                            <small class="text-muted">{{ $project->code }}</small>
                                        </td>
                                        <td>{{ $project->location ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'planning' => 'secondary',
                                                    'in_progress' => 'primary',
                                                    'on_hold' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $color = $statusColors[$project->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1" style="height: 8px; min-width: 100px;">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{ $project->progress ?? 0 }}%"
                                                         aria-valuenow="{{ $project->progress ?? 0 }}"
                                                         aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="ms-2 small">{{ number_format($project->progress ?? 0, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $completedMilestones = $project->milestones()->where('status', 'completed')->count();
                                                $totalMilestones = $project->milestones()->count();
                                            @endphp
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-flag-fill"></i> {{ $completedMilestones }}/{{ $totalMilestones }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('client.projects.milestones', $project->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
@endsection
