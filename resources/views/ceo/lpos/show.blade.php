@extends('ceo.layouts.app')

@section('title', 'LPO ' . $lpo->lpo_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">LPO: {{ $lpo->lpo_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('ceo.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ceo.lpos.index') }}">LPO Management</a></li>
                    <li class="breadcrumb-item active">{{ $lpo->lpo_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.lpos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('ceo.requisitions.show', $lpo->requisition) }}" class="btn btn-outline-primary">
                <i class="bi bi-file-text"></i> View Requisition
            </a>
        </div>
    </div>

    <div class="row">
        <!-- LPO Details -->
        <div class="col-lg-8">
            <!-- Company Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="{{ asset('images/advanta.jpg') }}" alt="ADVANTA Logo" class="img-fluid" style="max-height: 80px;">
                        </div>
                        <div class="col-md-8">
                            <h3 class="text-primary mb-1">ADVANTA UGANDA LIMITED</h3>
                            <p class="text-muted mb-0">Project Management System</p>
                            <p class="text-muted mb-0">LOCAL PURCHASE ORDER</p>
                        </div>
                        <div class="col-md-2 text-end">
                            <h4 class="text-primary">{{ $lpo->lpo_number }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LPO Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
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
                            <p class="mb-1"><strong>Delivery Date:</strong> {{ $lpo->delivery_date->format('M d, Y') }}</p>
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
                                @forelse($lpo->items as $item)
                                    <tr>
                                        <td>{{ $item->description ?? 'No description' }}</td>
                                        <td>{{ number_format($item->quantity, 3) }}</td>
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
            <!-- CEO Approval Actions -->
            @if($lpo->status === 'draft' && $lpo->requisition->status === \App\Models\Requisition::STATUS_PROCUREMENT)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">CEO Approval</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Pending Your Approval:</strong> This LPO is waiting for your approval before it can be issued to the supplier.
                    </div>

                    <!-- Approve Requisition (which will approve the LPO) -->
                    <form action="{{ route('ceo.requisitions.approve', $lpo->requisition) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label for="comment" class="form-label">Approval Comment (Optional)</label>
                            <textarea name="comment" id="comment" class="form-control" rows="3" 
                                      placeholder="Add any comments for approval..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="approved_amount" class="form-label">Approved Amount</label>
                            <input type="number" name="approved_amount" id="approved_amount" 
                                   class="form-control" step="0.01" min="0"
                                   value="{{ $lpo->requisition->estimated_total }}"
                                   placeholder="Enter approved amount">
                        </div>
                        <button type="submit" class="btn btn-success w-100" 
                                onclick="return confirm('Approve this LPO and requisition?')">
                            <i class="bi bi-check-circle"></i> Approve LPO
                        </button>
                    </form>

                    <hr>

                    <!-- Reject Requisition -->
                    <form action="{{ route('ceo.requisitions.reject', $lpo->requisition) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="reject_comment" class="form-label">Rejection Reason (Required)</label>
                            <textarea name="comment" id="reject_comment" class="form-control" rows="2" 
                                      placeholder="Please provide reason for rejection..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100" 
                                onclick="return confirm('Reject this LPO and requisition? This action cannot be undone.')">
                            <i class="bi bi-x-circle"></i> Reject LPO
                        </button>
                    </form>
                </div>
            </div>
            @elseif($lpo->status === 'draft' && $lpo->requisition->status === \App\Models\Requisition::STATUS_CEO_APPROVED)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">LPO Status</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <strong>Approved:</strong> This LPO has been approved and is ready to be issued to the supplier by Procurement.
                    </div>
                </div>
            </div>
            @endif

            <!-- Requisition Summary -->
            <div class="card shadow-sm">
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
                    <p class="mb-1"><strong>Status:</strong> 
                        <span class="badge {{ $lpo->requisition->getStatusBadgeClass() }}">
                            {{ ucfirst(str_replace('_', ' ', $lpo->requisition->status)) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection