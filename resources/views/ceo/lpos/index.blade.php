@extends('ceo.layouts.app')

@section('title', 'LPO Management - CEO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">LPO Management</h2>
            <p class="text-muted mb-0">Review and approve Local Purchase Orders</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <a href="{{ route('ceo.requisitions.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Requisitions
                @if($pendingLpos->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingLpos->count() }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $allLpos->count() }}</h4>
                    <small>Total LPOs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $pendingLpos->count() }}</h4>
                    <small>Pending Approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $allLpos->where('status', 'issued')->count() }}</h4>
                    <small>Issued LPOs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $allLpos->where('status', 'delivered')->count() }}</h4>
                    <small>Delivered</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>CEO LPO Management:</strong> As CEO, you can review and approve LPOs before they are issued to suppliers. 
                Approve LPOs here or through the requisitions approval process.
            </div>
        </div>
    </div>

    <!-- Pending LPOs for Approval -->
    @if($pendingLpos->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clock text-warning me-2"></i>
                LPOs Pending Your Approval
            </h5>
            <span class="badge bg-warning">{{ $pendingLpos->count() }} pending</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>LPO Number</th>
                            <th>Requisition Ref</th>
                            <th>Project</th>
                            <th>Supplier</th>
                            <th>Total Amount</th>
                            <th>Delivery Date</th>
                            <th>Prepared By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingLpos as $lpo)
                            <tr>
                                <td>
                                    <strong>{{ $lpo->lpo_number }}</strong>
                                </td>
                                <td>{{ $lpo->requisition->ref ?? 'N/A' }}</td>
                                <td>{{ $lpo->requisition->project->name ?? 'N/A' }}</td>
                                <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
                                <td>UGX {{ number_format($lpo->items->sum('total_price'), 2) }}</td>
                                <td>{{ $lpo->delivery_date ? $lpo->delivery_date->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $lpo->preparer->name ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('ceo.lpos.show', $lpo) }}" 
                                           class="btn btn-outline-primary" title="Review LPO">
                                            <i class="bi bi-eye"></i> Review
                                        </a>
                                        @if($lpo->requisition)
                                        <a href="{{ route('ceo.requisitions.show', $lpo->requisition) }}" 
                                           class="btn btn-outline-info" title="View Requisition">
                                            <i class="bi bi-file-text"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($pendingLpos->hasPages())
                <div class="card-footer bg-white">
                    {{ $pendingLpos->links() }}
                </div>
            @endif
        </div>
    </div>
    @else
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle text-success display-4 d-block mb-3"></i>
            <h4 class="text-success">No Pending LPOs</h4>
            <p class="text-muted">All LPOs have been approved. Check back later for new requests.</p>
            <a href="{{ route('ceo.requisitions.pending') }}" class="btn btn-primary mt-2">
                <i class="bi bi-clock"></i> Check Pending Requisitions
            </a>
        </div>
    </div>
    @endif

    <!-- All LPOs -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">All LPOs</h5>
        </div>
        <div class="card-body">
            @if($allLpos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>LPO Number</th>
                                <th>Requisition Ref</th>
                                <th>Supplier</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Delivery Date</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allLpos->take(10) as $lpo)
                                <tr>
                                    <td>
                                        <strong>{{ $lpo->lpo_number }}</strong>
                                    </td>
                                    <td>{{ $lpo->requisition->ref ?? 'N/A' }}</td>
                                    <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
                                    <td>UGX {{ number_format($lpo->items->sum('total_price'), 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $lpo->status === 'issued' ? 'success' : ($lpo->status === 'delivered' ? 'info' : 'warning') }}">
                                            {{ ucfirst($lpo->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $lpo->delivery_date ? $lpo->delivery_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $lpo->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('ceo.lpos.show', $lpo) }}" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($allLpos->count() > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('ceo.requisitions.index') }}" class="btn btn-outline-primary">
                            View All Requisitions with LPOs
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-receipt display-4 d-block mb-2"></i>
                    <p>No LPOs found in the system.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection