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
        <div class="col-lg-8" id="print-area">
            <div class="print-container">
                <!-- Professional Company Header -->
                <div class="lpo-header printable-section mb-4">
                    <div class="row align-items-center">
                        <div class="col-3 text-center">
                            <div class="company-logo">
                                <img src="{{ asset('images/advanta.jpg') }}" alt="ADVANTA Logo" class="img-fluid">
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <h1 class="company-name">ADVANTA UGANDA LIMITED</h1>
                            <p class="company-tagline">Project Management System</p>
                            <h2 class="document-title">LOCAL PURCHASE ORDER</h2>
                        </div>
                        <div class="col-3 text-center">
                            <div class="lpo-number-box">
                                <h3>{{ $lpo->lpo_number }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="company-info text-center mt-3">
                        <p class="mb-1">
                            <strong>Location:</strong> Katula Road Kisaasi, Kampala | 
                            <strong>Tel:</strong> 0393 249740 or 0200 91644 | 
                            <strong>Email:</strong> info@advanta.ug
                        </p>
                    </div>
                </div>

                <!-- LPO Information Box -->
                <div class="lpo-info-box printable-section mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="info-table">
                                <tr>
                                    <td><strong>LPO Number:</strong></td>
                                    <td>{{ $lpo->lpo_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Requisition:</strong></td>
                                    <td>{{ $lpo->requisition->ref }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Project:</strong></td>
                                    <td>{{ $lpo->requisition->project->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Project Location:</strong></td>
                                    <td>{{ $lpo->requisition->project->location ?? 'Katula Road Kisaasi' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Prepared By:</strong></td>
                                    <td>{{ $lpo->preparer->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="info-table">
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>{{ $lpo->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="status-badge status-{{ $lpo->status }}">
                                            {{ ucfirst($lpo->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Delivery Date:</strong></td>
                                    <td>{{ $lpo->delivery_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Delivery Location:</strong></td>
                                    <td>{{ $lpo->requisition->project->location ?? 'Katula Road Kisaasi' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Supplier & Company Information -->
                <div class="row printable-section mb-4">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h4 class="card-title">Supplier Information</h4>
                            <table class="info-table">
                                <tr>
                                    <td><strong>Supplier:</strong></td>
                                    <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Person:</strong></td>
                                    <td>{{ $lpo->supplier->contact_person ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $lpo->supplier->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $lpo->supplier->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td>{{ $lpo->supplier->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <h4 class="card-title">Our Information</h4>
                            <table class="info-table">
                                <tr>
                                    <td><strong>Company:</strong></td>
                                    <td>Advanta Uganda Limited</td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>Katula Road Kisaasi</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact:</strong></td>
                                    <td>0393 249740 or 0200 91644</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>procurement@advanta.ug</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="financial-summary printable-section mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="section-title">Financial Summary</h4>
                            <table class="financial-table">
                                <tr>
                                    <td>Subtotal (Excluding VAT):</td>
                                    <td>UGX {{ number_format($lpo->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>VAT Amount (18%):</td>
                                    <td>UGX {{ number_format($lpo->vat_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Other Charges:</td>
                                    <td>UGX {{ number_format($lpo->other_charges, 2) }}</td>
                                </tr>
                                <tr class="grand-total">
                                    <td><strong>Grand Total:</strong></td>
                                    <td><strong>UGX {{ number_format($lpo->total, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <div class="amount-words-card">
                                <h5>Amount in Words</h5>
                                <p class="amount-words">{{ $lpo->getAmountInWords() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items Table -->
                <div class="order-items printable-section mb-4">
                    <h4 class="section-title">Order Items</h4>
                    <div class="table-responsive">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">Item Description</th>
                                    <th width="10%">Qty</th>
                                    <th width="10%">Unit</th>
                                    <th width="15%">Unit Price</th>
                                    <th width="10%">VAT</th>
                                    <th width="15%">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lpo->items as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->description ?? 'No description' }}</td>
                                        <td class="text-center">{{ number_format($item->quantity, 3) }}</td>
                                        <td class="text-center">{{ $item->unit }}</td>
                                        <td class="text-right">UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-center">
                                            @if($item->has_vat)
                                                <span class="vat-indicator yes">YES</span>
                                            @else
                                                <span class="vat-indicator no">NO</span>
                                            @endif
                                        </td>
                                        <td class="text-right">UGX {{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No items found in this LPO
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                                    <td colspan="2" class="text-right"><strong>UGX {{ number_format($lpo->subtotal, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-right"><strong>VAT Amount:</strong></td>
                                    <td colspan="2" class="text-right"><strong>UGX {{ number_format($lpo->vat_amount, 2) }}</strong></td>
                                </tr>
                                <tr class="grand-total">
                                    <td colspan="5" class="text-right"><strong>GRAND TOTAL:</strong></td>
                                    <td colspan="2" class="text-right"><strong>UGX {{ number_format($lpo->total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Delivery & Terms -->
                <div class="row printable-section mb-4">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h4 class="card-title">Delivery Information</h4>
                            <table class="info-table">
                                <tr>
                                    <td><strong>Delivery Location:</strong></td>
                                    <td>{{ $lpo->requisition->project->location ?? 'Katula Road Kisaasi' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Person:</strong></td>
                                    <td>Site Manager</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Phone:</strong></td>
                                    <td>0393 249740 or 0200 91644</td>
                                </tr>
                                <tr>
                                    <td><strong>Delivery Date:</strong></td>
                                    <td>{{ $lpo->delivery_date->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <h4 class="card-title">Terms & Conditions</h4>
                            <div class="terms-content">
                                @if($lpo->terms)
                                    {{ $lpo->terms }}
                                @else
                                    <p>Standard payment terms apply. Delivery must be made on or before the specified date. All goods must meet quality standards.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Signatures Section -->
                <div class="signatures-section printable-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="signature-box supplier">
                                <h5>FOR SUPPLIER</h5>
                                <div class="signature-area">
                                    <p class="signature-line">Name: _________________________</p>
                                    <p class="signature-line">Signature: _____________________</p>
                                    <p class="signature-line">Date: _________________________</p>
                                    <div class="stamp-area">
                                        <p class="stamp-label">Company Stamp:</p>
                                        <div class="stamp-box"></div>
                                    </div>
                                </div>
                                <p class="signature-note">I hereby confirm delivery of all items as specified above</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="signature-box company">
                                <h5>FOR ADVANTA UGANDA LIMITED</h5>
                                <div class="signature-area">
                                    <p class="signature-line">Name: _________________________</p>
                                    <p class="signature-line">Signature: _____________________</p>
                                    <p class="signature-line">Date: _________________________</p>
                                    <div class="stamp-area">
                                        <p class="stamp-label">Company Stamp:</p>
                                        <div class="stamp-box"></div>
                                    </div>
                                </div>
                                <p class="signature-note">I hereby acknowledge receipt of all items in good condition</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="lpo-footer">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>ADVANTA UGANDA LIMITED</strong><br>
                                PLOT 28A, Katula Road Kisaasi<br>
                                Kampala, Uganda
                            </div>
                            <div class="col-md-4 text-center">
                                <strong>Contact Information</strong><br>
                                Tel: 0393 249740 or 0200 91644<br>
                                Email: info@advanta.ug
                            </div>
                            <div class="col-md-4 text-end">
                                <strong>Supplier</strong><br>
                                {{ $lpo->supplier->name ?? 'N/A' }}<br>
                                Tel: {{ $lpo->supplier->phone ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="footer-note">
                            <p class="mb-0">
                                <strong>Note:</strong> This LPO is valid only when signed by both parties. 
                                Goods must be delivered on or before {{ $lpo->delivery_date->format('M d, Y') }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div> <!-- Close print-container -->
        </div> <!-- Close col-lg-8 -->

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
                                <strong>Waiting for CEO Approval</strong>
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
                    @endif

                    <div class="text-center mt-3">
                        <a href="{{ route('procurement.requisitions.show', $lpo->requisition) }}" 
                           class="btn btn-outline-primary w-100">
                            <i class="bi bi-file-earmark-text"></i> View Requisition
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Summary</h5>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">{{ $lpo->items->count() }}</div>
                            <div class="stat-label">Items</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ $lpo->items->where('has_vat', true)->count() }}</div>
                            <div class="stat-label">VAT Items</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">UGX</div>
                            <div class="stat-label">{{ number_format($lpo->total, 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- Close col-lg-4 -->
    </div> <!-- Close row -->
</div> <!-- Close container-fluid -->


<style>

    .print-container {
    max-width: 210mm;
    margin: 0 auto;
    background: white;
    padding: 20px;
}
/* Professional LPO Styling */
.lpo-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.company-logo img {
    max-height: 80px;
}

.company-name {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
    letter-spacing: 1px;
}

.company-tagline {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 10px;
}

.document-title {
    font-size: 28px;
    font-weight: 300;
    letter-spacing: 2px;
    margin-bottom: 0;
}

.lpo-number-box {
    background: rgba(255,255,255,0.2);
    padding: 15px;
    border-radius: 8px;
    border: 2px solid rgba(255,255,255,0.3);
}

.lpo-number-box h3 {
    margin: 0;
    font-weight: 700;
}

.company-info {
    border-top: 1px solid rgba(255,255,255,0.3);
    padding-top: 15px;
}

.lpo-info-box {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
}

.info-table {
    width: 100%;
}

.info-table tr td {
    padding: 4px 8px;
    border-bottom: 1px solid #f0f0f0;
}

.info-table tr:last-child td {
    border-bottom: none;
}

.info-table td:first-child {
    width: 40%;
    font-weight: 600;
}

.info-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    height: 100%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card-title {
    color: #2c3e50;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.financial-summary {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
}

.section-title {
    color: #2c3e50;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
}

.financial-table {
    width: 100%;
}

.financial-table tr td {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.financial-table tr:last-child td {
    border-bottom: none;
}

.financial-table tr.grand-total td {
    border-top: 2px solid #2c3e50;
    font-size: 16px;
    font-weight: 700;
    color: #2c3e50;
}

.amount-words-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    height: 100%;
}

.amount-words {
    font-style: italic;
    color: #495057;
    line-height: 1.4;
    margin: 0;
}

.order-items {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th {
    background: #2c3e50;
    color: white;
    padding: 12px 8px;
    font-weight: 600;
    text-align: center;
}

.items-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #e0e0e0;
}

.items-table tbody tr:hover {
    background: #f8f9fa;
}

.items-table tfoot tr {
    background: #f8f9fa;
}

.items-table tfoot tr.grand-total {
    background: #2c3e50;
    color: white;
    font-weight: 700;
}

.vat-indicator {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.vat-indicator.yes {
    background: #d4edda;
    color: #155724;
}

.vat-indicator.no {
    background: #f8d7da;
    color: #721c24;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-issued {
    background: #d1ecf1;
    color: #0c5460;
}

.status-draft {
    background: #fff3cd;
    color: #856404;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.signatures-section {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 30px;
    margin-top: 20px;
}

.signature-box {
    text-align: center;
    padding: 20px;
}

.signature-box h5 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-weight: 600;
}

.signature-area {
    margin-bottom: 15px;
}

.signature-line {
    margin: 8px 0;
    font-size: 14px;
}

.stamp-area {
    margin-top: 20px;
}

.stamp-label {
    font-size: 12px;
    margin-bottom: 5px;
    color: #666;
}

.stamp-box {
    width: 150px;
    height: 80px;
    border: 2px dashed #ccc;
    margin: 0 auto;
    background: #fafafa;
}

.signature-note {
    font-size: 12px;
    color: #666;
    font-style: italic;
    margin-top: 10px;
}

.lpo-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e0e0e0;
}

.footer-note {
    margin-top: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 12px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    text-align: center;
}

.stat-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
}

/* Print Styles - Optimized for PDF */
@media print {
    /* Reset everything for print */
    * {
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }

    /* Hide non-printable elements */
    .non-printable,
    .breadcrumb,
    .btn-group,
    .card-header,
    .alert,
    form {
        display: none !important;
    }

    /* Show only print area */
    body {
        background: white !important;
        color: black !important;
        font-size: 12pt;
        line-height: 1.3;
        margin: 0 !important;
        padding: 0 !important;
    }

    #print-area {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    /* Remove all backgrounds and borders that don't print well */
    .lpo-header {
        background: #1e3c72 !important;
        color: white !important;
        padding: 15px 10px !important;
        border-radius: 0 !important;
        margin-bottom: 10px !important;
    }

    .company-logo img {
        max-height: 60px !important;
        filter: brightness(0) invert(1) !important;
    }

    .company-name {
        font-size: 18pt !important;
        margin-bottom: 3px !important;
    }

    .company-tagline {
        font-size: 10pt !important;
        margin-bottom: 5px !important;
    }

    .document-title {
        font-size: 16pt !important;
        margin-bottom: 5px !important;
    }

    .lpo-number-box {
        background: rgba(255,255,255,0.9) !important;
        color: #1e3c72 !important;
        padding: 8px !important;
        border: 1px solid white !important;
    }

    .lpo-number-box h3 {
        font-size: 14pt !important;
    }

    /* Simplify info boxes */
    .lpo-info-box,
    .info-card,
    .financial-summary,
    .order-items,
    .signatures-section {
        border: 1px solid #ccc !important;
        padding: 8px !important;
        margin-bottom: 8px !important;
        background: white !important;
        page-break-inside: avoid !important;
    }

    /* Optimize tables for print */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 9pt !important;
        page-break-inside: avoid !important;
    }

    th, td {
        padding: 4px 3px !important;
        border: 1px solid #ddd !important;
        text-align: left !important;
    }

    th {
        background: #f8f9fa !important;
        color: black !important;
        font-weight: bold !important;
    }

    /* Simplify financial tables */
    .financial-table tr td {
        padding: 3px 0 !important;
        border-bottom: 1px solid #eee !important;
    }

    /* Optimize items table */
    .items-table th {
        background: #2c3e50 !important;
        color: white !important;
        padding: 5px 3px !important;
    }

    .items-table td {
        padding: 4px 3px !important;
    }

    /* Simplify VAT indicators */
    .vat-indicator {
        padding: 2px 4px !important;
        font-size: 8pt !important;
        border: 1px solid #ccc !important;
    }

    .vat-indicator.yes {
        background: #f8f9fa !important;
        color: black !important;
    }

    .vat-indicator.no {
        background: #f8f9fa !important;
        color: black !important;
    }

    /* Simplify signatures */
    .signature-box {
        padding: 10px !important;
        border: 1px solid #ccc !important;
        margin-bottom: 10px !important;
    }

    .signature-line {
        margin: 5px 0 !important;
        font-size: 10pt !important;
    }

    .stamp-box {
        width: 120px !important;
        height: 60px !important;
        border: 1px dashed #999 !important;
    }

    /* Ensure proper page breaks */
    .printable-section {
        page-break-inside: avoid !important;
    }

    .order-items,
    .signatures-section {
        page-break-before: auto !important;
    }

    /* Remove shadows and gradients */
    .lpo-header {
        background: #1e3c72 !important;
        background-image: none !important;
    }

    /* Optimize text sizes */
    h1 { font-size: 18pt !important; }
    h2 { font-size: 16pt !important; }
    h3 { font-size: 14pt !important; }
    h4 { font-size: 12pt !important; }
    h5 { font-size: 11pt !important; }

    .card-title {
        font-size: 11pt !important;
        border-bottom: 1px solid #3498db !important;
    }

    .section-title {
        font-size: 12pt !important;
    }

    /* Ensure colors print properly */
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Page margins */
    @page {
        margin: 0.5cm !important;
        size: A4 portrait;
    }

    /* Footer optimization */
    .lpo-footer {
        margin-top: 15px !important;
        padding-top: 10px !important;
        border-top: 1px solid #ccc !important;
        font-size: 9pt !important;
    }

    .footer-note {
        font-size: 8pt !important;
        padding: 5px !important;
    }
}




/* Responsive Design */
@media (max-width: 768px) {
    .lpo-header {
        padding: 20px;
    }
    
    .company-name {
        font-size: 20px;
    }
    
    .document-title {
        font-size: 22px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const printButton = document.querySelector('button[onclick="window.print()"]');
    
    if (printButton) {
        printButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add optimization class
            document.body.classList.add('printing-optimized');
            
            // Small delay to ensure styles are applied
            setTimeout(function() {
                window.print();
                
                // Remove class after print
                setTimeout(function() {
                    document.body.classList.remove('printing-optimized');
                }, 500);
            }, 100);
        });
    }

    // Handle browser print dialog
    window.addEventListener('beforeprint', function() {
        document.body.classList.add('printing-optimized');
    });

    window.addEventListener('afterprint', function() {
        document.body.classList.remove('printing-optimized');
    });
});
</script>
@endsection

