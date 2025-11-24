<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LPO {{ $lpo->lpo_number }} - Advanta Uganda Limited</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            background: #ffffff;
            padding: 15mm;
        }

        /* Use tables for layout - more reliable in PDFs */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header Section */
        .header-section {
            width: 100%;
            background: #1e3c72;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }

        .header-section td {
            vertical-align: middle;
            padding: 10px;
        }

        .company-logo-cell {
            width: 25%;
            text-align: center;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: inline-block;
            line-height: 80px;
            font-size: 10px;
            color: white;
        }

        .company-info-cell {
            width: 50%;
            text-align: center;
        }

        .company-name {
            font-size: 22pt;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .company-tagline {
            font-size: 10pt;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .document-title {
            font-size: 20pt;
            font-weight: 300;
            letter-spacing: 2px;
            margin-top: 5px;
        }

        .lpo-number-cell {
            width: 25%;
            text-align: center;
        }

        .lpo-number-box {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            padding: 15px;
            display: inline-block;
        }

        .lpo-number-box h3 {
            font-size: 18pt;
            font-weight: bold;
        }

        .company-contact {
            text-align: center;
            padding: 10px;
            font-size: 9pt;
            border-top: 1px solid rgba(255,255,255,0.3);
            margin-top: 10px;
        }

        /* Info Sections */
        .info-section {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-section td {
            width: 50%;
            vertical-align: top;
            padding: 5px;
        }

        .info-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            height: 100%;
        }

        .card-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
        }

        .info-table td {
            padding: 4px 6px;
            font-size: 10pt;
            border-bottom: 1px solid #e9ecef;
        }

        .info-table td:first-child {
            font-weight: 600;
            width: 45%;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: 600;
        }

        .status-issued { background: #d1ecf1; color: #0c5460; }
        .status-draft { background: #fff3cd; color: #856404; }
        .status-delivered { background: #d4edda; color: #155724; }

        /* Financial Summary */
        .financial-section {
            width: 100%;
            margin: 15px 0;
        }

        .financial-section td {
            vertical-align: top;
            padding: 5px;
        }

        .financial-table {
            width: 100%;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
        }

        .financial-table td {
            padding: 6px 8px;
            font-size: 10pt;
            border-bottom: 1px solid #e9ecef;
        }

        .financial-table td:first-child {
            width: 70%;
        }

        .financial-table td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .financial-total {
            border-top: 2px solid #2c3e50 !important;
            font-size: 11pt !important;
            font-weight: bold !important;
            color: #2c3e50;
        }

        .amount-words-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            height: 100%;
        }

        .amount-words-title {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .amount-words-text {
            font-size: 9pt;
            font-style: italic;
            color: #495057;
            line-height: 1.5;
        }

        /* Items Table */
        .items-section {
            margin: 15px 0;
        }

        .section-title {
            font-size: 13pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            padding: 8px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
        }

        .items-table {
            width: 100%;
            border: 1px solid #dee2e6;
        }

        .items-table th {
            background: #2c3e50;
            color: white;
            padding: 10px 6px;
            font-size: 10pt;
            font-weight: 600;
            text-align: center;
            border: 1px solid #2c3e50;
        }

        .items-table td {
            padding: 8px 6px;
            font-size: 9pt;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .items-table .text-left { text-align: left; }
        .items-table .text-right { text-align: right; }

        .vat-indicator {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 600;
        }

        .vat-yes { background: #d4edda; color: #155724; }
        .vat-no { background: #f8d7da; color: #721c24; }

        .items-table tfoot td {
            background: #f8f9fa;
            font-weight: 600;
            padding: 8px 6px;
            border-top: 2px solid #2c3e50;
        }

        .items-table .grand-total-row td {
            background: #2c3e50;
            color: white;
            font-weight: bold;
            font-size: 11pt;
        }

        /* Signatures Section */
        .signatures-section {
            margin: 20px 0;
            page-break-inside: avoid;
        }

        .signature-cell {
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }

        .signature-box {
            border: 2px solid #dee2e6;
            padding: 15px;
            min-height: 220px;
        }

        .signature-title {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
        }

        .signature-line {
            margin: 12px 0;
            font-size: 9pt;
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
        }

        .stamp-box {
            width: 140px;
            height: 70px;
            border: 2px dashed #999;
            margin: 10px auto;
            background: #fafafa;
            text-align: center;
            line-height: 70px;
            font-size: 8pt;
            color: #999;
        }

        .signature-note {
            font-size: 8pt;
            font-style: italic;
            color: #666;
            text-align: center;
            margin-top: 10px;
        }

        /* Footer */
        .footer-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            page-break-inside: avoid;
        }

        .footer-info {
            width: 100%;
        }

        .footer-info td {
            padding: 8px;
            font-size: 9pt;
            vertical-align: top;
        }

        .footer-info td:nth-child(1) { text-align: left; width: 33%; }
        .footer-info td:nth-child(2) { text-align: center; width: 34%; }
        .footer-info td:nth-child(3) { text-align: right; width: 33%; }

        .footer-note {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-top: 10px;
            font-size: 8pt;
            text-align: center;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .mb-10 { margin-bottom: 10px; }
        .mt-10 { margin-top: 10px; }

        /* Ensure colors print */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Page breaks */
        .page-break-avoid {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-section">
        <tr>
            <td class="company-logo-cell">
                <div class="company-logo">
                    <img src="{{ public_path('images/advanta.jpg') }}" alt="ADVANTA" style="max-width: 80px; max-height: 80px;">
                </div>
            </td>
            <td class="company-info-cell">
                <div class="company-name">ADVANTA UGANDA LIMITED</div>
                <div class="company-tagline">Project Management System</div>
                <div class="document-title">LOCAL PURCHASE ORDER</div>
            </td>
            <td class="lpo-number-cell">
                <div class="lpo-number-box">
                    <h3>{{ $lpo->lpo_number }}</h3>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="company-contact">
                <strong>Location:</strong> Katula Road Kisaasi, Kampala | 
                <strong>Tel:</strong> 0393 249740 or 0200 91644 | 
                <strong>Email:</strong> procurement@advanta.ug
            </td>
        </tr>
    </table>

    <!-- LPO Basic Information -->
    <table class="info-section page-break-avoid">
        <tr>
            <td>
                <div class="info-card">
                    <div class="card-title">LPO Information</div>
                    <table class="info-table">
                        <tr>
                            <td>LPO Number:</td>
                            <td>{{ $lpo->lpo_number }}</td>
                        </tr>
                        <tr>
                            <td>Requisition:</td>
                            <td>{{ $lpo->requisition->ref }}</td>
                        </tr>
                        <tr>
                            <td>Project:</td>
                            <td>{{ $lpo->requisition->project->name }}</td>
                        </tr>
                        <tr>
                            <td>Location:</td>
                            <td>{{ $lpo->requisition->project->location ?? 'Katula Road Kisaasi' }}</td>
                        </tr>
                        <tr>
                            <td>Prepared By:</td>
                            <td>{{ $lpo->preparer->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="info-card">
                    <div class="card-title">Additional Details</div>
                    <table class="info-table">
                        <tr>
                            <td>Date:</td>
                            <td>{{ $lpo->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td>
                                <span class="status-badge status-{{ $lpo->status }}">
                                    {{ ucfirst($lpo->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Delivery Date:</td>
                            <td>{{ $lpo->delivery_date->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td>Delivery Location:</td>
                            <td>{{ $lpo->requisition->project->location ?? 'Katula Road Kisaasi' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Supplier & Company Information -->
    <table class="info-section page-break-avoid">
        <tr>
            <td>
                <div class="info-card">
                    <div class="card-title">Supplier Information</div>
                    <table class="info-table">
                        <tr>
                            <td>Supplier:</td>
                            <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Contact Person:</td>
                            <td>{{ $lpo->supplier->contact_person ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Phone:</td>
                            <td>{{ $lpo->supplier->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td>{{ $lpo->supplier->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Address:</td>
                            <td>{{ $lpo->supplier->address ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="info-card">
                    <div class="card-title">Our Information</div>
                    <table class="info-table">
                        <tr>
                            <td>Company:</td>
                            <td>Advanta Uganda Limited</td>
                        </tr>
                        <tr>
                            <td>Location:</td>
                            <td>Katula Road Kisaasi</td>
                        </tr>
                        <tr>
                            <td>Contact:</td>
                            <td>0393 249740 or 0200 91644</td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td>procurement@advanta.ug</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Financial Summary -->
    <table class="financial-section page-break-avoid">
        <tr>
            <td style="width: 65%;">
                <div class="card-title">Financial Summary</div>
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
                    <tr class="financial-total">
                        <td>GRAND TOTAL:</td>
                        <td>UGX {{ number_format($lpo->total, 2) }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 35%;">
                <div class="amount-words-box">
                    <div class="amount-words-title">Amount in Words</div>
                    <div class="amount-words-text">{{ $lpo->getAmountInWords() }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Order Items -->
    <div class="items-section page-break-avoid">
        <div class="section-title">Order Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Item Description</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 10%;">Unit</th>
                    <th style="width: 15%;">Unit Price</th>
                    <th style="width: 10%;">VAT</th>
                    <th style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lpo->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left">{{ $item->description ?? 'No description' }}</td>
                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-center">{{ $item->unit }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-center">
                            @if($item->has_vat)
                                <span class="vat-indicator vat-yes">YES</span>
                            @else
                                <span class="vat-indicator vat-no">NO</span>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px;">
                            No items found in this LPO
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">Subtotal:</td>
                    <td class="text-right">UGX {{ number_format($lpo->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right">VAT Amount:</td>
                    <td class="text-right">UGX {{ number_format($lpo->vat_amount, 2) }}</td>
                </tr>
                <tr class="grand-total-row">
                    <td colspan="6" class="text-right">GRAND TOTAL:</td>
                    <td class="text-right">UGX {{ number_format($lpo->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signatures Section -->
    <div class="signatures-section page-break-avoid">
        <div class="section-title">Signatures & Confirmation</div>
        <table style="width: 100%;">
            <tr>
                <td class="signature-cell">
                    <div class="signature-box">
                        <div class="signature-title">FOR SUPPLIER</div>
                        <div class="signature-line">Name: _________________________</div>
                        <div class="signature-line">Signature: _____________________</div>
                        <div class="signature-line">Date: __________________________</div>
                        <div class="stamp-box">Company Stamp</div>
                        <div class="signature-note">
                            I hereby confirm delivery of all items as specified above
                        </div>
                    </div>
                </td>
                <td class="signature-cell">
                    <div class="signature-box">
                        <div class="signature-title">FOR ADVANTA UGANDA LIMITED</div>
                        <div class="signature-line">Name: _________________________</div>
                        <div class="signature-line">Signature: _____________________</div>
                        <div class="signature-line">Date: __________________________</div>
                        <div class="stamp-box">Company Stamp</div>
                        <div class="signature-note">
                            I hereby acknowledge receipt of all items in good condition
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer-section">
        <table class="footer-info">
            <tr>
                <td>
                    <strong>ADVANTA UGANDA LIMITED</strong><br>
                    PLOT 28A, Katula Road Kisaasi<br>
                    Kampala, Uganda
                </td>
                <td>
                    <strong>Contact Information</strong><br>
                    Tel: 0393 249740 or 0200 91644<br>
                    Email: procurement@advanta.ug
                </td>
                <td>
                    <strong>Supplier</strong><br>
                    {{ $lpo->supplier->name ?? 'N/A' }}<br>
                    Tel: {{ $lpo->supplier->phone ?? 'N/A' }}
                </td>
            </tr>
        </table>
        <div class="footer-note">
            <strong>Important Note:</strong> This LPO is valid only when signed by both parties. 
            Goods must be delivered on or before {{ $lpo->delivery_date->format('M d, Y') }}. 
            All items must meet quality standards as specified.
        </div>
    </div>
</body>
</html>