<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Staff Report - ADVANTA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .report-form-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .report-form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .form-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
        }
    </style>
</head>
<body class="report-form-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="report-form-card">
                    <div class="form-header text-center">
                        <h1><i class="bi bi-clipboard-check"></i> Staff Report Submission</h1>
                        <p class="mb-0">Advanta Uganda Limited - Daily/Weekly Activity Reports</p>
                    </div>
                    
                    <div class="card-body p-4">
                        @include('partials.alerts')
                        
                        <form action="{{ route('staff-reports.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Report Type *</label>
                                    <select name="report_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="daily" {{ old('report_type') == 'daily' ? 'selected' : '' }}>Daily Report</option>
                                        <option value="weekly" {{ old('report_type') == 'weekly' ? 'selected' : '' }}>Weekly Report</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Report Date *</label>
                                    <input type="date" name="report_date" class="form-control" 
                                           value="{{ old('report_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Report / Document Title *</label>
                                <input type="text" name="title" class="form-control" 
                                       value="{{ old('title') }}" placeholder="e.g., Daily Site Progress Report" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea name="description" class="form-control" rows="6" 
                                          placeholder="Describe your activities, progress, challenges, and achievements..." required>{{ old('description') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Name *</label>
                                    <input type="text" name="staff_name" class="form-control" 
                                           value="{{ old('staff_name') }}" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Email *</label>
                                    <input type="email" name="staff_email" class="form-control" 
                                           value="{{ old('staff_email') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Access Code *</label>
                                <input type="password" name="access_code" class="form-control" 
                                       placeholder="Enter the access code provided" required>
                                <div class="form-text">Contact your supervisor for the access code</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Attachments (Optional)</label>
                                <input type="file" name="attachments[]" class="form-control" multiple 
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                                <div class="form-text">
                                    You can upload multiple files (images, PDFs, documents). Max 10MB per file.
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send"></i> Submit Report
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="{{ route('welcome') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Main Portal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>