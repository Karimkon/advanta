@extends('admin.layouts.app')

@section('title', 'Manage Project Milestones')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Project Milestones Management</h2>
            <p class="text-muted mb-0">Manage all project milestones across the organization</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
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
                    <small>Projects with Milestones</small>
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
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-building me-2"></i>Projects Milestone Overview
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
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.milestones.project', $project) }}" 
                                           class="btn btn-outline-primary" title="View Milestones">
                                            <i class="bi bi-list-ul"></i>
                                        </a>
                                        <a href="{{ route('admin.milestones.create', $project) }}" 
                                           class="btn btn-outline-success" title="Add Milestone">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                    </div>
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
</div>
@endsection