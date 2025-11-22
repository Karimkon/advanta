{{-- resources/views/finance/labor/payments/receipt.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->payment_reference }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .container { max-width: 100% !important; }
            .receipt-container { box-shadow: none !important; border: none !important; }
            .btn { display: none !important; }
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
            background: white;
        }
        .company-header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
        }
        .amount-display {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
        .signature-area {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <a href="{{ route('finance.labor.show', $payment->laborWorker) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Worker
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Receipt
            </button>
        </div>

        <div class="receipt-container">
            <!-- Company Header -->
            <div class="company-header">
                <div class="row align-items-center">
                    <div class="col-3 text-start">
                        <img src="{{ asset('images/advanta.jpg') }}" alt="ADVANTA Logo" style="height: 60px;">
                    </div>
                    <div class="col-6">
                        <h1 class="receipt-title mb-1">ADVANTA UGANDA LTD</h1>
                        <p class="mb-1">Construction & Engineering Solutions</p>
                        <p class="mb-0">Kampala, Uganda</p>
                        <p class="mb-0">Tel: 0393 249740 or 0200 91644 | Email: info@advanta.ug</p>
                    </div>
                    <div class="col-3 text-end">
                        <div class="border p-2 d-inline-block">
                            <strong>RECEIPT</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Details -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Receipt No:</strong></td>
                            <td>{{ $payment->payment_reference }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>{{ $payment->payment_date->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Project:</strong></td>
                            <td>{{ $payment->laborWorker->project->name }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Worker ID:</strong></td>
                            <td>{{ $payment->laborWorker->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td class="text-uppercase">{{ $payment->payment_method }}</td>
                        </tr>
                        <tr>
                            <td><strong>Paid By:</strong></td>
                            <td>{{ $payment->paidBy->name }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Worker Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Worker Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Name:</strong> {{ $payment->laborWorker->name }}<br>
                            <strong>Role:</strong> {{ $payment->laborWorker->role }}<br>
                            <strong>ID Number:</strong> {{ $payment->laborWorker->id_number ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Phone:</strong> {{ $payment->laborWorker->phone ?? 'N/A' }}<br>
                            <strong>NSSF No:</strong> {{ $payment->laborWorker->nssf_number ?? 'N/A' }}<br>
                            <strong>Payment Frequency:</strong> {{ ucfirst($payment->laborWorker->payment_frequency) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Period:</strong> {{ $payment->period_start->format('M d, Y') }} to {{ $payment->period_end->format('M d, Y') }}<br>
                            <strong>Days Worked:</strong> {{ $payment->days_worked }} days<br>
                            <strong>Description:</strong> {{ $payment->description }}
                        </div>
                        <div class="col-md-6">
                            @if(isset($payment->gross_amount))
                            <strong>Gross Amount:</strong> UGX {{ number_format($payment->gross_amount, 2) }}<br>
                            <strong>NSSF Deduction:</strong> UGX {{ number_format($payment->nssf_amount, 2) }}<br>
                            @endif
                            <strong>Net Amount:</strong> UGX {{ number_format($payment->amount, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount in Words -->
            <div class="alert alert-info mb-4">
                <strong>Amount in Words:</strong> 
                <span id="amountInWords">{{ \App\Helpers\NumberHelper::convertToWords($payment->amount) }}</span>
            </div>

            <!-- Amount Display -->
            <div class="amount-display">
                UGX {{ number_format($payment->amount, 2) }}
            </div>

            <!-- Notes -->
            @if($payment->notes)
            <div class="alert alert-warning mb-4">
                <strong>Notes:</strong> {{ $payment->notes }}
            </div>
            @endif

            <!-- Signatures -->
            <div class="row signature-area">
                <div class="col-md-6 text-center">
                    <p>_________________________</p>
                    <p><strong>Receiver's Signature</strong></p>
                    <p>Name: {{ $payment->laborWorker->name }}</p>
                    <p>Date: ___________________</p>
                </div>
                <div class="col-md-6 text-center">
                    <p>_________________________</p>
                    <p><strong>Authorized Signature</strong></p>
                    <p>Name: {{ $payment->paidBy->name }}</p>
                    <p>For: ADVANTA UGANDA LTD</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-1"><strong>Thank you for your service!</strong></p>
                <p class="mb-0 text-muted small">
                    This is a computer generated receipt. No signature required for digital copies.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Print automatically when page loads (optional)
        window.onload = function() {
            // Uncomment the line below if you want auto-print
            // window.print();
        };
    </script>
</body>
</html>