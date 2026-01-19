@extends('admin.layouts.app')
@section('title', 'Clients Management')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Clients Management</h2>
            <p class="text-muted mb-0">Manage client accounts and project access</p>
        </div>
        <div>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Client
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Clients</h6>
                            <h3 class="mb-0">{{ $clients->total() }}</h3>
                        </div>
                        <i class="bi bi-people fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Active Clients</h6>
                            <h3 class="mb-0">{{ $clients->where('status', 'active')->count() }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Phone</th>
                            <th>Projects</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>
                                    <strong>{{ $client->name }}</strong>
                                </td>
                                <td>{{ $client->email }}</td>
                                <td>{{ $client->company ?? '-' }}</td>
                                <td>{{ $client->phone ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $client->projects_count }} project(s)</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $client->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($client->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($client->last_login_at)
                                        {{ $client->last_login_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this client?')">
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
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people display-4 d-block mb-2"></i>
                                        No clients found.
                                        <p class="mt-2">
                                            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                                                Add First Client
                                            </a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div class="card-footer bg-white">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
