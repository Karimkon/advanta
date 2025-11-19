@extends('admin.layouts.app')

@section('title', $project->name . ' - Milestones Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $project->name }} - Milestones</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.milestones.index') }}">All Milestones</a></li>
                    <li class="breadcrumb-item active">{{ $project->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.milestones.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to All
            </a>
            <a href="{{ route('admin.milestones.create', $project) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Milestone
            </a>
        </div>
    </div>

    <!-- Project Overview -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-1">{{ $project->name }}</h4>
                    <p class="text-muted mb-2">{{ $project->location }}</p>
                    <p class="mb-0">{{ $project->description }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="row text-center">
                        <div class="col-4">
                            <h5 class="mb-0 text-primary">{{ $projectStats['total'] }}</h5>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-4">
                            <h5 class="mb-0 text-success">{{ $projectStats['completed'] }}</h5>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col-4">
                            <h5 class="mb-0 text-warning">{{ $projectStats['overdue'] }}</h5>
                            <small class="text-muted">Overdue</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        @php
                            $progress = $projectStats['total'] > 0 ? 
                                round(($projectStats['completed'] / $projectStats['total']) * 100) : 0;
                        @endphp
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                        </div>
                        <small class="text-muted">{{ $progress }}% Overall Progress</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Milestones List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-flag me-2"></i>Construction Milestones
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Milestone</th>
                            <th>Description</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Cost</th>
                            <th>Last Update</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($milestones as $milestone)
                            <tr class="{{ $milestone->isOverdue() ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $milestone->title }}</strong>
                                    @if($milestone->isOverdue())
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    @endif
                                    @if($milestone->hasPhoto())
                                        <i class="bi bi-camera text-info ms-1" title="Has photo"></i>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($milestone->description, 50) }}</small>
                                </td>
                                <td>
                                    {{ $milestone->due_date->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">
                                        @if($milestone->due_date->isPast() && $milestone->status !== 'completed')
                                            <i class="bi bi-clock text-danger"></i> 
                                            {{ $milestone->due_date->diffForHumans() }}
                                        @else
                                            <i class="bi bi-clock text-muted"></i>
                                            {{ $milestone->due_date->diffForHumans() }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $milestone->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $milestone->status === 'completed' ? 'success' : 'primary' }}" 
                                                 style="width: {{ $milestone->getProgressPercentage() }}%"></div>
                                        </div>
                                        <small>{{ $milestone->getProgressPercentage() }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <small>
                                        UGX {{ number_format($milestone->cost_estimate) }}
                                        @if($milestone->actual_cost)
                                            <br>
                                            <strong class="text-success">UGX {{ number_format($milestone->actual_cost) }}</strong>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $milestone->updated_at->format('M d, Y') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.milestones.show', ['project' => $project, 'milestone' => $milestone]) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.milestones.edit', ['project' => $project, 'milestone' => $milestone]) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Delete"
                                                onclick="confirmDelete({{ $milestone->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $milestone->id }}" 
                                          action="{{ route('admin.milestones.destroy', ['project' => $project, 'milestone' => $milestone]) }}" 
                                          method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(milestoneId) {
    if (confirm('Are you sure you want to delete this milestone? This action cannot be undone.')) {
        document.getElementById('delete-form-' + milestoneId).submit();
    }
}
</script>
@endpush