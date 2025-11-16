@extends('finance.layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Payment Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.payments.index') }}">Payments</a></li>
                    <li class="breadcrumb-item active">Payment #{{ $payment->id }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Payment Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Payment ID:</strong> #{{ $payment->id }}</p>
                            <p><strong>Payment Date:</strong> {{ $payment->paid_on->format('M d, Y') }}</p>
                            <p><strong>Payment Method:</strong> 
                                <span class="badge bg-secondary text-capitalize">
                                    {{ str_replace('_', ' ', $payment->payment_method) }}
                                </span>
                            </p>
                            <p><strong>Reference Number:</strong> {{ $payment->reference ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </p>
                            <p><strong>Amount:</strong> UGX {{ number_format($payment->amount, 2) }}</p>
                            <p><strong>Processed By:</strong> {{ $payment->paidBy->name ?? 'N/A' }}</p>
                            <p><strong>Created:</strong> {{ $payment->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($payment->notes)
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p class="mt-1">{{ $payment->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Related Requisition -->
            @if($payment->requisition)
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Related Requisition</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Requisition Ref:</strong> {{ $payment->requisition->ref }}</p>
                            <p><strong>Project:</strong> {{ $payment->requisition->project->name ?? 'N/A' }}</p>
                            <p><strong>Supplier:</strong> {{ $payment->requisition->supplier->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Type:</strong> 
                                <span class="badge bg-{{ $payment->requisition->type === 'store' ? 'info' : 'primary' }}">
                                    {{ ucfirst($payment->requisition->type) }}
                                </span>
                            </p>
                            <p><strong>Total Amount:</strong> UGX {{ number_format($payment->requisition->estimated_total, 2) }}</p>
                            <p><strong>Created:</strong> {{ $payment->requisition->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($payment->status === 'pending')
                            <button class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Mark as Completed
                            </button>
                        @endif
                        <button onclick="printReceipt()" class="btn btn-outline-primary">
                            <i class="bi bi-printer"></i> Print Receipt
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Timeline -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item active">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Payment Created</strong>
                                <small class="text-muted d-block">{{ $payment->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @if($payment->status === 'completed')
                        <div class="timeline-item active">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Payment Completed</strong>
                                <small class="text-muted d-block">{{ $payment->updated_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Printable Receipt -->
<div id="printable-receipt" style="display: none;">
    <div style="font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; border: 2px solid #000;">
        <!-- Header -->
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px;">
            <h1 style="margin: 0; color: #2c5aa0; font-size: 24px;">ADVANTA UGANDA LTD</h1>
            <p style="margin: 5px 0; font-size: 14px;">Project Management System</p>
            <p style="margin: 5px 0; font-size: 14px;">Payment Receipt</p>
        </div>

        <!-- Receipt Details -->
        <div style="margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Receipt No:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right;">PAY-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Date:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right;">{{ $payment->paid_on->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Time:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right;">{{ $payment->created_at->format('H:i') }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment Details -->
        <div style="margin-bottom: 20px;">
            <h3 style="color: #2c5aa0; border-bottom: 1px solid #000; padding-bottom: 5px;">PAYMENT DETAILS</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ddd;"><strong>Amount Paid:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #ddd; text-align: right; font-weight: bold; font-size: 18px;">
                        UGX {{ number_format($payment->amount, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Payment Method:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right; text-transform: capitalize;">
                        {{ str_replace('_', ' ', $payment->payment_method) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Reference:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right;">
                        {{ $payment->reference ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Related Information -->
        <div style="margin-bottom: 20px;">
            <h3 style="color: #2c5aa0; border-bottom: 1px solid #000; padding-bottom: 5px;">RELATED INFORMATION</h3>
            <table style="width: 100%; border-collapse: collapse;">
                @if($payment->requisition)
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Requisition:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right;">{{ $payment->requisition->ref }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Project:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right;">{{ $payment->requisition->project->name ?? 'N/A' }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Supplier:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right;">{{ $payment->supplier->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd;"><strong>Processed By:</strong></td>
                    <td style="padding: 5px 0; border-bottom: 1px solid #ddd; text-align: right;">{{ $payment->paidBy->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($payment->notes)
        <div style="margin-bottom: 20px;">
            <h3 style="color: #2c5aa0; border-bottom: 1px solid #000; padding-bottom: 5px;">NOTES</h3>
            <p style="font-style: italic; margin: 10px 0;">{{ $payment->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div style="text-align: center; border-top: 2px solid #000; padding-top: 15px; margin-top: 20px;">
            <p style="margin: 5px 0; font-size: 12px;">Thank you for your business!</p>
            <p style="margin: 5px 0; font-size: 10px; color: #666;">This is an computer generated receipt</p>
            <p style="margin: 5px 0; font-size: 10px; color: #666;">Advanta Uganda Ltd - {{ config('app.url') }}</p>
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
    background: #10b981;
    border-color: #10b981;
}

.timeline-content {
    padding-bottom: 10px;
}

@media print {
    body * {
        visibility: hidden;
    }
    #printable-receipt, #printable-receipt * {
        visibility: visible;
    }
    #printable-receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>

<script>
function printReceipt() {
    // Get the printable content
    var printContent = document.getElementById('printable-receipt').innerHTML;
    
    // Create a new window for printing
    var printWindow = window.open('', '_blank', 'width=600,height=700');
    
    // Write the receipt content
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt - PAY-${String({{ $payment->id }}).padStart(6, '0')}</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 20px;
                    background: white;
                }
                @media print {
                    body { margin: 0; }
                }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            ${printContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
}

// Alternative simple print function (if above doesn't work)
function simplePrint() {
    window.print();
}
</script>
@endsection