@extends('surveyor.layouts.app')

@section('title', 'Milestones - ' . $project->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Project Milestones</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('surveyor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $project->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('surveyor.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
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
                            <h5 class="mb-0 text-primary">{{ $milestones->where('status', 'pending')->count() }}</h5>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-4">
                            <h5 class="mb-0 text-info">{{ $milestones->where('status', 'in_progress')->count() }}</h5>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-4">
                            <h5 class="mb-0 text-success">{{ $milestones->where('status', 'completed')->count() }}</h5>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Milestones List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-flag me-2"></i>Construction Milestones
            </h5>
            <span class="badge bg-primary">{{ $milestones->count() }} milestones</span>
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
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('surveyor.milestones.show', ['project' => $project, 'milestone' => $milestone]) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('surveyor.milestones.edit', ['project' => $project, 'milestone' => $milestone]) }}" 
                                           class="btn btn-outline-warning" title="Update Progress">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Progress Summary -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Progress Summary</h6>
                </div>
                <div class="card-body">
                    @php
                        $total = $milestones->count();
                        $completed = $milestones->where('status', 'completed')->count();
                        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
                    @endphp
                    <div class="text-center">
                        <div class="position-relative d-inline-block">
                            <div class="progress-circle" data-progress="{{ $progress }}">
                                <span class="progress-circle-value">{{ $progress }}%</span>
                            </div>
                        </div>
                        <p class="mt-3 mb-0">
                            {{ $completed }} of {{ $total }} milestones completed
                        </p>
                    </div>
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
                        $upcoming = $milestones->where('status', '!=', 'completed')
                                            ->where('due_date', '>=', now())
                                            ->sortBy('due_date')
                                            ->take(3);
                    @endphp
                    @forelse($upcoming as $milestone)
                        <div class="border-start border-3 border-info ps-3 mb-3">
                            <h6 class="mb-1">{{ $milestone->title }}</h6>
                            <small class="text-muted">
                                Due: {{ $milestone->due_date->format('M d, Y') }}
                                ({{ $milestone->due_date->diffForHumans() }})
                            </small>
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
.progress-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#198754 var(--progress), #e9ecef 0deg);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progress-circle::before {
    content: '';
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.progress-circle-value {
    position: relative;
    font-weight: bold;
    font-size: 1.2rem;
    color: #198754;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set progress circle values
    document.querySelectorAll('.progress-circle').forEach(circle => {
        const progress = circle.getAttribute('data-progress');
        circle.style.setProperty('--progress', progress + '%');
    });
});
</script>
@endpush