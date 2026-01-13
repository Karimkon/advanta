<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Advanta Report' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            padding: 15px 0;
            border-bottom: 2px solid #2563eb;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 11px;
            color: #6b7280;
        }
        .header .date {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 5px;
        }
        .content {
            padding: 0 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
            font-size: 9px;
        }
        th {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tr:hover {
            background-color: #f3f4f6;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-secondary {
            background-color: #f3f4f6;
            color: #374151;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .summary-box h3 {
            font-size: 11px;
            color: #475569;
            margin-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .summary-label {
            color: #64748b;
        }
        .summary-value {
            font-weight: bold;
            color: #1e293b;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            padding: 10px;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
        .page-break {
            page-break-after: always;
        }
        .amount {
            font-family: 'DejaVu Sans Mono', monospace;
            text-align: right;
        }
        .total-row {
            background-color: #1e40af !important;
            color: white;
            font-weight: bold;
        }
        .total-row td {
            border-color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ADVANTA AFRICA LTD</h1>
        <div class="subtitle">{{ $title ?? 'Report' }}</div>
        <div class="date">Generated on: {{ now()->format('F d, Y \a\t H:i') }}</div>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        Advanta Africa Ltd - Construction Management System | Page <span class="pagenum"></span>
    </div>
</body>
</html>
