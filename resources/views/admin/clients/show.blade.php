@extends('admin.layouts.app')
@section('title', 'Client Details')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $client->name }}</h2>
            <p class="text-muted mb-0">Client Details</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Client Info -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Client Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Name:</td>
                            <td><strong>{{ $client->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>{{ $client->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone:</td>
                            <td>{{ $client->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Company:</td>
                            <td>{{ $client->company ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Address:</td>
                            <td>{{ $client->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                <span class="badge bg-{{ $client->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Login:</td>
                            <td>
                                @if($client->last_login_at)
                                    {{ $client->last_login_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $client->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assigned Projects -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-folder"></i> Assigned Projects</h5>
                    <span class="badge bg-white text-info">{{ $client->projects->count() }} project(s)</span>
                </div>
                <div class="card-body p-0">
                    @if($client->projects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Location</th>
                                        <th>Milestones</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->projects as $project)
                                        @php
                                            $totalMilestones = $project->milestones->count();
                                            $completedMilestones = $project->milestones->where('status', 'completed')->count();
                                            $progress = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $project->name }}</strong>
                                                <br><small class="text-muted">{{ $project->code }}</small>
                                            </td>
                                            <td>{{ $project->location }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $completedMilestones }}</span> /
                                                <span class="badge bg-secondary">{{ $totalMilestones }}</span>
                                            </td>
                                            <td style="width: 150px;">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-{{ $progress >= 75 ? 'success' : ($progress >= 50 ? 'info' : ($progress >= 25 ? 'warning' : 'danger')) }}"
                                                         style="width: {{ $progress }}%">
                                                        {{ $progress }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-folder-x display-4 text-muted d-block mb-2"></i>
                            <p class="text-muted mb-0">No projects assigned to this client.</p>
                            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary mt-3">
                                Assign Projects
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
