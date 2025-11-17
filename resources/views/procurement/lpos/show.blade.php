@extends('procurement.layouts.app')

@section('title', 'LPO ' . $lpo->lpo_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">LPO: {{ $lpo->lpo_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('procurement.lpos.index') }}">LPO Management</a></li>
                    <li class="breadcrumb-item active">{{ $lpo->lpo_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('procurement.lpos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print LPO
            </button>
        </div>
    </div>

    <div class="row">
        <!-- LPO Details -->
        <div class="col-lg-8">
            <!-- LPO Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-primary">LOCAL PURCHASE ORDER</h4>
                            <p class="mb-1"><strong>LPO Number:</strong> {{ $lpo->lpo_number }}</p>
                            <p class="mb-1"><strong>Requisition:</strong> {{ $lpo->requisition->ref }}</p>
                            <p class="mb-1"><strong>Project:</strong> {{ $lpo->requisition->project->name }}</p>
                            <p class="mb-1"><strong>Prepared By:</strong> {{ $lpo->preparer->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1"><strong>Date:</strong> {{ $lpo->created_at->format('M d, Y') }}</p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-{{ $lpo->status === 'issued' ? 'success' : ($lpo->status === 'delivered' ? 'info' : 'warning') }}">
                                    {{ ucfirst($lpo->status) }}
                                </span>
                            </p>
                            @if($lpo->issue_date)
                                <p class="mb-1"><strong>Issue Date:</strong> {{ $lpo->issue_date->format('M d, Y') }}</p>
                            @endif
                            @if($lpo->delivery_date)
                                <p class="mb-1"><strong>Delivery Date:</strong> {{ $lpo->delivery_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Supplier Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Supplier:</strong> {{ $lpo->supplier->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Contact:</strong> {{ $lpo->supplier->contact_person ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $lpo->supplier->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Email:</strong> {{ $lpo->supplier->email ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $lpo->supplier->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LPO Items - FIXED SECTION -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Description</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lpo->items as $item)
                                    <tr>
                                        <td>{{ $item->description ?? 'No description' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td>UGX {{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-exclamation-circle display-4 d-block mb-2"></i>
                                            No items found in this LPO
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                    <td><strong>UGX {{ number_format($lpo->items->sum('total_price'), 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions -->
            @if($lpo->terms)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Terms & Conditions</h5>
                </div>
                <div class="card-body">
                    <p>{{ $lpo->terms }}</p>
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($lpo->notes)
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Additional Notes</h5>
                </div>
                <div class="card-body">
                    <p>{{ $lpo->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">LPO Actions</h5>
                </div>
                <div class="card-body">
                    @if($lpo->status === 'draft')
                        <div class="alert alert-warning">
                            <strong>Draft LPO:</strong> Ready to be issued to supplier.
                        </div>
                        
                        <form action="{{ route('procurement.lpos.issue', $lpo) }}" method="POST" class="d-grid mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success" 
                                    onclick="return confirm('Issue this LPO to supplier? This action cannot be undone.')">
                                <i class="bi bi-send"></i> Issue LPO to Supplier
                            </button>
                        </form>

                    @elseif($lpo->status === 'issued')
                        <div class="alert alert-info">
                            <strong>LPO Issued:</strong> Waiting for supplier delivery.
                        </div>
                        
                        @if($lpo->items->count() > 0)
                            <form action="{{ route('procurement.lpos.mark-delivered', $lpo) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-primary" 
                                        onclick="return confirm('Mark this LPO as delivered? This will add items to project store inventory.')">
                                    <i class="bi bi-check-circle"></i> Mark as Delivered
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning">
                                <small>Cannot mark as delivered: No items found in this LPO.</small>
                            </div>
                        @endif

                    @elseif($lpo->status === 'delivered')
                        <div class="alert alert-success">
                            <strong>Delivered:</strong> Items received from supplier.
                        </div>
                        
                        @if($lpo->delivery_date)
                            <p class="mb-1"><strong>Delivery Date:</strong> {{ $lpo->delivery_date->format('M d, Y') }}</p>
                        @else
                            <p class="mb-1"><strong>Delivery Date:</strong> <span class="text-muted">Not recorded</span></p>
                        @endif
                    @endif

                    <hr class="my-3">

                    <!-- Debug Info (remove in production) -->
                    @if($lpo->items->count() === 0)
                        <div class="alert alert-warning">
                            <small><strong>Debug:</strong> No LPO items found. Items may not have been created properly.</small>
                        </div>
                    @endif

                    <!-- Related Requisition -->
                    <div class="text-center">
                        <a href="{{ route('procurement.requisitions.show', $lpo->requisition) }}" 
                           class="btn btn-outline-primary w-100">
                            <i class="bi bi-file-earmark-text"></i> View Requisition
                        </a>
                    </div>
                </div>
            </div>

            <!-- LPO Timeline -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">LPO Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item {{ $lpo->status === 'draft' ? 'active' : 'completed' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Draft Created</strong>
                                <small class="text-muted d-block">{{ $lpo->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                        <div class="timeline-item {{ $lpo->status === 'issued' ? 'active' : ($lpo->status === 'delivered' ? 'completed' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Issued to Supplier</strong>
                                @if($lpo->issue_date)
                                    <small class="text-muted d-block">{{ $lpo->issue_date->format('M d, Y') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-item {{ $lpo->status === 'delivered' ? 'active' : '' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Delivered</strong>
                                @if($lpo->delivery_date)
                                    <small class="text-muted d-block">{{ $lpo->delivery_date->format('M d, Y') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requisition Summary -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Requisition Summary</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Reference:</strong> {{ $lpo->requisition->ref }}</p>
                    <p class="mb-1"><strong>Project:</strong> {{ $lpo->requisition->project->name }}</p>
                    <p class="mb-1"><strong>Requested By:</strong> {{ $lpo->requisition->requester->name }}</p>
                    <p class="mb-1"><strong>Urgency:</strong> 
                        <span class="badge {{ $lpo->requisition->getUrgencyBadgeClass() }}">
                            {{ ucfirst($lpo->requisition->urgency) }}
                        </span>
                    </p>
                    <p class="mb-1"><strong>Requisition Items:</strong> {{ $lpo->requisition->items->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    margin-bottom: 15px;
}

.timeline-marker {
    position: absolute;
    left: -20px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dee2e6;
    border: 2px solid #fff;
}

.timeline-item.active .timeline-marker {
    background: #6f42c1;
    border-color: #6f42c1;
}

.timeline-item.completed .timeline-marker {
    background: #198754;
    border-color: #198754;
}

.timeline-content {
    padding-bottom: 10px;
}

@media print {
    .sidebar, .mobile-toggle, .btn-group {
        display: none !important;
    }
    .content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}
</style>
@endsection