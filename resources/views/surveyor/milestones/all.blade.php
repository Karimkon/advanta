@extends('surveyor.layouts.app')

@section('title', 'All Milestones')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">All Milestones</h2>
            <p class="text-muted mb-0">Track all milestones across your projects</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $milestones->count() }}</h4>
                            <small>Total Milestones</small>
                        </div>
                        <i class="bi bi-flag fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statusCounts['completed'] }}</h4>
                            <small>Completed</small>
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
                            <h4 class="mb-0">{{ $statusCounts['in_progress'] }}</h4>
                            <small>In Progress</small>
                        </div>
                        <i class="bi bi-clock fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $statusCounts['delayed'] }}</h4>
                            <small>Delayed</small>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Milestones Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Milestone</th>
                            <th>Project</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($milestones as $milestone)
                            <tr>
                                <td>
                                    <strong>{{ $milestone->title }}</strong>
                                    @if($milestone->description)
                                        <br><small class="text-muted">{{ Str::limit($milestone->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>{{ $milestone->project->name }}</td>
                                <td>
                                    {{ $milestone->due_date->format('M d, Y') }}
                                    @if($milestone->due_date->isPast() && $milestone->status !== 'completed')
                                        <br><small class="text-danger">Overdue</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $milestone->status === 'completed' ? 'success' : 
                                        ($milestone->status === 'in_progress' ? 'info' : 
                                        ($milestone->status === 'delayed' ? 'danger' : 'secondary')) 
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: {{ $milestone->completion_percentage }}%"></div>
                                        </div>
                                        <small>{{ $milestone->completion_percentage }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('surveyor.milestones.edit', ['project' => $milestone->project, 'milestone' => $milestone]) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-pencil"></i> Update
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-flag display-1 text-muted"></i>
                                    <h4 class="text-muted mt-3">No Milestones Found</h4>
                                    <p class="text-muted">You don't have any milestones assigned yet.</p>
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