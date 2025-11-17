@extends('stores.layouts.app')

@section('title', $store->display_name . ' - Store Releases')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $store->display_name }} - Store Releases</h2>
            <p class="text-muted mb-0">Manage material releases from store</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('stores.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Pending Requisitions -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Pending Requisitions</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Requisition</th>
                            <th>Project</th>
                            <th>Requested By</th>
                            <th>Items</th>
                            <th>Urgency</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingRequisitions as $requisition)
                            <tr>
                                <td><strong>{{ $requisition->ref }}</strong></td>
                                <td>{{ $requisition->project->name ?? 'N/A' }}</td>
                                <td>{{ $requisition->requester->name }}</td>
                                <td>
                                    @foreach($requisition->items as $item)
                                        <div>{{ $item->name }} ({{ $item->quantity }} {{ $item->unit }})</div>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                        {{ ucfirst($requisition->urgency) }}
                                    </span>
                                </td>
                                <td>{{ $requisition->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('stores.releases.create', [$store, $requisition]) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-box-arrow-up"></i> Process Release
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-check-circle display-4 d-block mb-2"></i>
                                        No pending requisitions found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Release History -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Release History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Release ID</th>
                            <th>Requisition</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Released By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($releases as $release)
                            <tr>
                                <td><strong>#{{ $release->id }}</strong></td>
                                <td>{{ $release->requisition->ref }}</td>
                                <td>{{ $release->requisition->project->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ ucfirst($release->status) }}
                                    </span>
                                </td>
                                <td>{{ $release->releasedBy->name ?? 'N/A' }}</td>
                                <td>{{ $release->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('stores.releases.show', [$store, $release]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-clock-history display-4 d-block mb-2"></i>
                                        No release history found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($releases->hasPages())
                <div class="card-footer bg-white">
                    {{ $releases->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection