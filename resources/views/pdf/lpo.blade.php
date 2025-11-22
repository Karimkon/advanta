<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LPO {{ $lpo->lpo_number }} - Advanta Uganda Limited</title>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #fff;
        }

        h1, h2, h3, h4, h5 {
            margin: 0;
        }

        .container-fluid {
            width: 100%;
            padding: 20px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .col-md-6, .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .col-md-4, .col-3 {
            flex: 0 0 33.3333%;
            max-width: 33.3333%;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .mb-0 { margin-bottom: 0; }
        .mb-4 { margin-bottom: 20px; }
        .mt-3 { margin-top: 15px; }
        .py-4 { padding-top: 20px; padding-bottom: 20px; }

        /* Header Styling */
        .lpo-header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .company-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
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
        }

        .lpo-number-box {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 8px;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .lpo-number-box h3 {
            color: #fff;
        }

        .company-info p {
            margin: 0;
            font-size: 14px;
        }

        /* Info Cards */
        .info-card, .financial-summary, .order-items, .amount-words-card, .signatures-section {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-title, .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }

        .info-table, .financial-table, .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td, .financial-table td, .items-table td, .items-table th {
            padding: 8px;
            border: 1px solid #dee2e6;
        }

        .items-table th {
            background: #2c3e50;
            color: #fff;
            font-weight: 600;
            text-align: center;
        }

        .items-table td {
            text-align: center;
        }

        .items-table tfoot tr.grand-total {
            background: #2c3e50;
            color: #fff;
            font-weight: 700;
        }

        .vat-indicator {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .vat-indicator.yes { background: #d4edda; color: #155724; }
        .vat-indicator.no { background: #f8d7da; color: #721c24; }

        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-issued { background: #d1ecf1; color: #0c5460; }
        .status-draft { background: #fff3cd; color: #856404; }
        .status-delivered { background: #d4edda; color: #155724; }

        .signature-box h5 {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .signature-line {
            margin: 5px 0;
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
            font-style: italic;
            color: #666;
            margin-top: 10px;
        }

        .footer-note {
            font-size: 12px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-top: 15px;
        }

        /* Ensure print colors */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">

        <!-- Header -->
        <div class="lpo-header">
            <div class="row align-items-center">
                <div class="col-3 text-center">
                    <div class="company-logo">ADVANTA LOGO</div>
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
                <p>
                    <strong>Location:</strong> Katula Road Kisaasi, Kampala | 
                    <strong>Tel:</strong> 0393 249740 or 0200 91644 | 
                    <strong>Email:</strong> procurement@advanta.ug
                </p>
            </div>
        </div>

        <!-- LPO Details -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-card">
                    <h4 class="card-title">LPO Information</h4>
                    <table class="info-table">
                        <tr><td>LPO Number:</td><td>{{ $lpo->lpo_number }}</td></tr>
                        <tr><td>Requisition:</td><td>{{ $lpo->requisition->ref }}</td></tr>
                        <tr><td>Project:</td><td>{{ $lpo->requisition->project->name }}</td></tr>
                        <tr><td>Project Location:</td><td>{{ $lpo->requisition->project->location ?? 'Katula Road Kisaasi' }}</td></tr>
                        <tr><td>Prepared By:</td><td>{{ $lpo->preparer->name ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card">
                    <h4 class="card-title">Additional Information</h4>
                    <table class="info-table">
                        <tr><td>Date:</td><td>{{ $lpo->created_at->format('M d, Y') }}</td></tr>
                        <tr><td>Status:</td><td><span class="status-badge status-{{ $lpo->status }}">{{ ucfirst($lpo->status) }}</span></td></tr>
                        <tr><td>Delivery Date:</td><td>{{ $lpo->delivery_date->format('M d, Y') }}</td></tr>
                        <tr><td>Delivery Location:</td><td>{{ $lpo->requisition->project->location ?? 'Katula Road Kisaasi' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Supplier & Company Info -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-card">
                    <h4 class="card-title">Supplier Information</h4>
                    <table class="info-table">
                        <tr><td>Supplier:</td><td>{{ $lpo->supplier->name ?? 'N/A' }}</td></tr>
                        <tr><td>Contact Person:</td><td>{{ $lpo->supplier->contact_person ?? 'N/A' }}</td></tr>
                        <tr><td>Phone:</td><td>{{ $lpo->supplier->phone ?? 'N/A' }}</td></tr>
                        <tr><td>Email:</td><td>{{ $lpo->supplier->email ?? 'N/A' }}</td></tr>
                        <tr><td>Address:</td><td>{{ $lpo->supplier->address ?? 'N/A' }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-card">
                    <h4 class="card-title">Our Information</h4>
                    <table class="info-table">
                        <tr><td>Company:</td><td>Advanta Uganda Limited</td></tr>
                        <tr><td>Location:</td><td>Katula Road Kisaasi</td></tr>
                        <tr><td>Contact:</td><td>0393 249740 or 0200 91644</td></tr>
                        <tr><td>Email:</td><td>procurement@advanta.ug</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="financial-summary">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="section-title">Financial Summary</h4>
                    <table class="financial-table">
                        <tr><td>Subtotal (Excl. VAT):</td><td>UGX {{ number_format($lpo->subtotal, 2) }}</td></tr>
                        <tr><td>VAT (18%):</td><td>UGX {{ number_format($lpo->vat_amount, 2) }}</td></tr>
                        <tr><td>Other Charges:</td><td>UGX {{ number_format($lpo->other_charges, 2) }}</td></tr>
                        <tr class="grand-total"><td>Grand Total:</td><td>UGX {{ number_format($lpo->total, 2) }}</td></tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <div class="amount-words-card">
                        <h5>Amount in Words</h5>
                        <p>{{ $lpo->getAmountInWords() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="order-items">
            <h4 class="section-title">Order Items</h4>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>VAT</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lpo->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->description ?? 'N/A' }}</td>
                            <td>{{ number_format($item->quantity, 3) }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                            <td>
                                <span class="vat-indicator {{ $item->has_vat ? 'yes' : 'no' }}">{{ $item->has_vat ? 'YES' : 'NO' }}</span>
                            </td>
                            <td>UGX {{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No items found</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr><td colspan="6" class="text-right">Subtotal:</td><td>UGX {{ number_format($lpo->subtotal, 2) }}</td></tr>
                    <tr><td colspan="6" class="text-right">VAT Amount:</td><td>UGX {{ number_format($lpo->vat_amount, 2) }}</td></tr>
                    <tr class="grand-total"><td colspan="6" class="text-right">Grand Total:</td><td>UGX {{ number_format($lpo->total, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>

        <!-- Signatures -->
        <div class="signatures-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="signature-box">
                        <h5>FOR SUPPLIER</h5>
                        <p class="signature-line">Name: _________________________</p>
                        <p class="signature-line">Signature: _____________________</p>
                        <p class="signature-line">Date: _________________________</p>
                        <div class="stamp-box"></div>
                        <p class="signature-note">I hereby confirm delivery of all items as specified above</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="signature-box">
                        <h5>FOR ADVANTA UGANDA LIMITED</h5>
                        <p class="signature-line">Name: _________________________</p>
                        <p class="signature-line">Signature: _____________________</p>
                        <p class="signature-line">Date: _________________________</p>
                        <div class="stamp-box"></div>
                        <p class="signature-note">I hereby acknowledge receipt of all items in good condition</p>
                    </div>
                </div>
            </div>
            <div class="footer-note">
                <p><strong>Note:</strong> This LPO is valid only when signed by both parties. Goods must be delivered on or before {{ $lpo->delivery_date->format('M d, Y') }}.</p>
            </div>
        </div>

    </div>
</body>
</html>
