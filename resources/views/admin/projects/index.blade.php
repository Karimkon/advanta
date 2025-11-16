@extends('admin.layouts.app')
@section('title', 'Projects')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Projects Management</h2>
            <p class="text-muted mb-0">Manage all projects in the system</p>
        </div>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Project
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Location</th>
                            <th>Project Manager</th>
                            <th>Engineers</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.projects.show', $project) }}" class="text-decoration-none">
                                        <strong>{{ $project->name }}</strong>
                                    </a>
                                </td>
                                <td>{{ $project->code }}</td>
                                <td>{{ $project->location }}</td>
                                <td>
                                    @php
                                        $projectManager = $project->users()->where('role', 'project_manager')->first();
                                    @endphp
                                    @if($projectManager)
                                        {{ $projectManager->name }}
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $engineersCount = $project->users()->where('role', 'engineer')->count();
                                    @endphp
                                    @if($engineersCount > 0)
                                        <span class="badge bg-warning">{{ $engineersCount }} engineer(s)</span>
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                                <td>UGX {{ number_format($project->budget, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($project->status) }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.projects.show', $project) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.projects.edit', $project) }}" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" 
                                              class="d-inline" onsubmit="return confirm('Delete this project?');">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-folder-x display-4 d-block mb-2"></i>
                                        No projects found.
                                        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary mt-2">
                                            Create First Project
                                        </a>
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