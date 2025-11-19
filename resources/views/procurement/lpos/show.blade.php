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
        <!-- LPO Details - Main Content -->
        <div class="col-lg-8">
            <!-- Company Header -->
            <div class="card shadow-sm mb-4 printable-section">
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
            <div class="card shadow-sm mb-4 printable-section">
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
            <div class="card shadow-sm mb-4 printable-section">
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
            <div class="card shadow-sm mb-4 printable-section">
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
            <div class="card shadow-sm mb-4 printable-section">
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
            <div class="card shadow-sm printable-section">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Additional Notes</h5>
                </div>
                <div class="card-body">
                    <p>{{ $lpo->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Actions - Hidden when printing -->
        <div class="col-lg-4 non-printable">
           <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">LPO Actions</h5>
                </div>
                <div class="card-body">
                    @if($lpo->status === 'draft')
                        @if($lpo->requisition->status === \App\Models\Requisition::STATUS_CEO_APPROVED)
                            <div class="alert alert-success">
                                <strong>CEO Approved:</strong> Ready to issue LPO to supplier.
                            </div>
                            
                            <form action="{{ route('procurement.lpos.issue', $lpo) }}" method="POST" class="d-grid mb-3">
                                @csrf
                                <button type="submit" class="btn btn-success" 
                                        onclick="return confirm('Issue this LPO to supplier? This action cannot be undone.')">
                                    <i class="bi bi-send"></i> Issue LPO to Supplier
                                </button>
                            </form>
                        @else
                            <div class="alert alert-info">
                                <strong>Waiting for CEO Approval:</strong> 
                                This LPO has been sent to CEO for approval. The "Issue LPO" button will appear after CEO approval.
                            </div>
                            
                            <div class="text-center">
                                <a href="{{ route('procurement.requisitions.show', $lpo->requisition) }}" 
                                   class="btn btn-outline-primary w-100">
                                    <i class="bi bi-eye"></i> View Requisition Status
                                </a>
                            </div>
                        @endif

                    @elseif($lpo->status === 'issued')
                        <div class="alert alert-info">
                            <strong>LPO Issued:</strong> Waiting for supplier delivery.
                        </div>
                        

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
                    <h5 class="mb-0">Delivery Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item {{ $lpo->status === 'draft' ? 'active' : 'completed' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>LPO Created</strong>
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
                                <strong>Delivery Expected</strong>
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

/* Print Styles */
@media print {
    /* Hide non-essential elements */
    .non-printable,
    .btn-group,
    .breadcrumb,
    .sidebar,
    .mobile-toggle,
    .navbar,
    .alert,
    .timeline,
    .card-header h5 {
        display: none !important;
    }
    
    /* Show only printable sections */
    .printable-section {
        display: block !important;
        break-inside: avoid;
    }
    
    /* Adjust layout for printing */
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    
    .row {
        display: block !important;
        margin: 0 !important;
    }
    
    .col-lg-8 {
        width: 100% !important;
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
    
    .col-lg-4 {
        display: none !important;
    }
    
    /* Card styling for print */
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        margin-bottom: 10px !important;
        page-break-inside: avoid;
    }
    
    .card-body {
        padding: 10px !important;
    }
    
    /* Table styling for print */
    .table {
        font-size: 12px !important;
    }
    
    .table th,
    .table td {
        padding: 4px 8px !important;
    }
    
    /* Reduce spacing */
    .mb-4, .mb-1, .mb-0 {
        margin-bottom: 5px !important;
    }
    
    /* Header styling */
    h2, h3, h4 {
        margin: 5px 0 !important;
    }
    
    /* Ensure single page */
    body {
        margin: 0.5cm !important;
        font-size: 12px !important;
        line-height: 1.2 !important;
    }
    
    /* Optimize text for printing */
    .text-primary {
        color: #000 !important;
    }
    
    .text-muted {
        color: #666 !important;
    }
    
    /* Badge styling for print */
    .badge {
        border: 1px solid #000 !important;
        background: white !important;
        color: black !important;
        padding: 2px 6px !important;
    }
}

/* Additional compact styling for better fit */
.print-compact .card-body {
    padding: 8px !important;
}

.print-compact .table {
    margin-bottom: 0 !important;
}

.print-compact h3 {
    font-size: 1.2rem !important;
}

.print-compact h4 {
    font-size: 1.1rem !important;
}
</style>

<script>
// Add compact class for printing
document.addEventListener('DOMContentLoaded', function() {
    // Add compact class to all printable sections before printing
    window.addEventListener('beforeprint', function() {
        document.querySelectorAll('.printable-section').forEach(function(section) {
            section.classList.add('print-compact');
        });
    });
    
    // Remove compact class after printing
    window.addEventListener('afterprint', function() {
        document.querySelectorAll('.printable-section').forEach(function(section) {
            section.classList.remove('print-compact');
        });
    });
});
</script>
@endsection