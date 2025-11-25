<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QHSE Report Submitted - ADVANTA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .success-container {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="success-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="success-card">
                    <div class="text-success mb-4">
                        <i class="bi bi-shield-check" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="text-success mb-3">QHSE Report Submitted Successfully!</h2>
                    
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <p class="text-muted mb-4">
                        Your QHSE report has been received and will be reviewed by management. 
                        Thank you for helping maintain our quality, health, safety, and environment standards.
                    </p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('qhse-reports.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Submit Another Report
                        </a>
                        <a href="{{ route('welcome') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> Back to Main Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>