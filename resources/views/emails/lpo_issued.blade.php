<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LPO {{ $lpo->lpo_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .attachment-note { 
            background: #e7f3ff; 
            padding: 15px; 
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>ADVANTA UGANDA LIMITED</h2>
        <h3>LOCAL PURCHASE ORDER</h3>
        <p>LPO Number: <strong>{{ $lpo->lpo_number }}</strong></p>
    </div>

    <div class="content">
        <p>Dear {{ $lpo->supplier->name }},</p>
        
        <p>We are pleased to issue the following Local Purchase Order for your attention and action:</p>

        <div class="attachment-note">
            <strong>📎 PDF Attachment:</strong> Please find the official LPO document attached as a PDF file. 
            This is the formal document that should be printed, signed, and brought during delivery.
        </div>

        <div>
            <p><strong>LPO Summary:</strong></p>
            <ul>
                <li><strong>LPO Number:</strong> {{ $lpo->lpo_number }}</li>
                <li><strong>Delivery Date:</strong> {{ $lpo->delivery_date->format('M d, Y') }}</li>
                <li><strong>Delivery Location:</strong> {{ $lpo->requisition->project->location ?? 'Katula Road Kisaasi' }}</li>
                <li><strong>Total Amount:</strong> UGX {{ number_format($lpo->total, 2) }}</li>
            </ul>
        </div>

        <p><strong>Order Items:</strong></p>
        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lpo->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ number_format($item->quantity, 3) }} {{ $item->unit }}</td>
                    <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                    <td>UGX {{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Grand Total:</strong></td>
                    <td><strong>UGX {{ number_format($lpo->total, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        @if($lpo->terms)
        <p><strong>Terms & Conditions:</strong><br>
        {{ $lpo->terms }}</p>
        @endif

        <p><strong>Action Required:</strong></p>
        <ol>
            <li>Print the attached PDF LPO document</li>
            <li>Sign and stamp in the supplier section</li>
            <li>Bring the signed copy when delivering the items</li>
            <li>Deliver all items on or before {{ $lpo->delivery_date->format('M d, Y') }}</li>
        </ol>

        <p>For any queries, please contact our procurement department at procurement@advanta.ug or call 0393 249740 or 0200 91644.</p>

        <p>Best regards,<br>
        <strong>Procurement Department</strong><br>
        Advanta Uganda Limited</p>
    </div>

    <div class="footer">
        <p>PLOT 28A, Katula Road Kisaasi, Kampala, Uganda<br>
        Tel: 0393 249740 or 0200 91644 / +256 393 215281 | Email: procurement@advanta.ug</p>
        <p><em>This is an electronically generated document. No signature is required for email purposes.</em></p>
    </div>
</body>
</html>