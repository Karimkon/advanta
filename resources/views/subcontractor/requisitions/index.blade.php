@extends('subcontractor.layouts.app')

@section('title', 'My Requisitions')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Requisitions</h2>
            <p class="text-muted mb-0">Track all your material and purchase requests</p>
        </div>
        <a href="{{ route('subcontractor.requisitions.create') }}" class="btn btn-warning">
            <i class="bi bi-plus-circle me-1"></i> New Requisition
        </a>
    </div>

    <!-- Requisitions Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Project</th>
                            <th>Items</th>
                            <th>Est. Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $requisition)
                            <tr>
                                <td>
                                    <a href="{{ route('subcontractor.requisitions.show', $requisition) }}" class="fw-bold text-decoration-none">
                                        {{ $requisition->ref }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge {{ $requisition->type === 'store' ? 'bg-info' : 'bg-primary' }}">
                                        {{ ucfirst($requisition->type) }}
                                    </span>
                                </td>
                                <td>{{ $requisition->project->name ?? 'N/A' }}</td>
                                <td>{{ $requisition->items->count() }} items</td>
                                <td>UGX {{ number_format($requisition->estimated_total) }}</td>
                                <td>
                                    <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                        {{ $requisition->getCurrentStage() }}
                                    </span>
                                </td>
                                <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('subcontractor.requisitions.show', $requisition) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($requisition->canBeEdited())
                                            <a href="{{ route('subcontractor.requisitions.edit', $requisition) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('subcontractor.requisitions.destroy', $requisition) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this requisition?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2 text-muted"></i>
                                    <span class="text-muted">No requisitions found.</span>
                                    <a href="{{ route('subcontractor.requisitions.create') }}" class="d-block mt-2">
                                        Create your first requisition
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $requisitions->links() }}
        </div>
    </div>
</div>
@endsection
