@extends('stores.layouts.app')

@section('title', 'LPO ' . $lpo->lpo_number . ' - ' . $store->name)

@section('content')
<div class="container-fluid">
    <div class="row">
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
                            <p class="mb-1"><strong>Store:</strong> {{ $store->name }}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-{{ $lpo->status === 'issued' ? 'warning' : 'success' }}">
                                    {{ ucfirst($lpo->status) }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Created Date:</strong> {{ $lpo->created_at->format('M d, Y') }}</p>
                            @if($lpo->issue_date)
                                <p class="mb-1"><strong>Issue Date:</strong> {{ $lpo->issue_date->format('M d, Y') }}</p>
                            @else
                                <p class="mb-1"><strong>Issue Date:</strong> <span class="text-muted">Not issued yet</span></p>
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

            <!-- LPO Items -->
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
                                @foreach($lpo->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td>UGX {{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
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
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Draft LPO:</strong> Not yet issued to supplier.
                        </div>

                    @elseif($lpo->status === 'issued')
                        <div class="alert alert-info">
                            <i class="bi bi-truck"></i>
                            <strong>LPO Issued:</strong> Waiting for supplier delivery.
                        </div>
                        
                        <a href="{{ route('stores.lpos.confirm-delivery', ['store' => $store, 'lpo' => $lpo]) }}" 
                           class="btn btn-success w-100 mb-3">
                            <i class="bi bi-check-circle"></i> Confirm Delivery
                        </a>

                    @elseif($lpo->status === 'delivered')
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <strong>Delivered:</strong> Items received in store inventory.
                        </div>
                        
                        @if($lpo->delivery_date)
                            <p class="mb-1"><strong>Delivery Date:</strong> {{ $lpo->delivery_date->format('M d, Y') }}</p>
                        @endif
                        @if($lpo->delivery_notes)
                            <p class="mb-1"><strong>Delivery Notes:</strong> {{ $lpo->delivery_notes }}</p>
                        @endif
                    @endif

                    <hr>

                    <!-- Navigation -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('stores.lpos.index', $store) }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Back to LPOs
                        </a>
                        <a href="{{ route('stores.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> Store Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- LPO Timeline -->
            <div class="card shadow-sm mb-4">
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

            <!-- Store Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Store Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Store:</strong> {{ $store->name }}</p>
                    <p class="mb-1"><strong>Type:</strong> {{ $store->type }}</p>
                    <p class="mb-1"><strong>Project:</strong> {{ $store->project->name }}</p>
                    <p class="mb-1"><strong>Location:</strong> {{ $store->location ?? 'N/A' }}</p>
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
</style>
@endsection