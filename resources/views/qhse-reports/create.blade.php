<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit QHSE Report - ADVANTA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .report-form-container {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .report-form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .form-header {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
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
                        <h1><i class="bi bi-shield-check"></i> QHSE Report Submission</h1>
                        <p class="mb-0">Advanta Uganda Limited - Quality, Health, Safety & Environment Reports</p>
                    </div>
                    
                    <div class="card-body p-4">
                        @include('partials.alerts')
                        
                        <form action="{{ route('qhse-reports.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Report Type *</label>
                                    <select name="report_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="safety" {{ old('report_type') == 'safety' ? 'selected' : '' }}>Safety Report</option>
                                    <option value="quality" {{ old('report_type') == 'quality' ? 'selected' : '' }}>Quality Report</option>
                                    <option value="companydocuments" {{ old('report_type') == 'companydocuments' ? 'selected' : '' }}>Company Documents</option>
                                    <option value="health" {{ old('report_type') == 'health' ? 'selected' : '' }}>Health Report</option>
                                    <option value="environment" {{ old('report_type') == 'environment' ? 'selected' : '' }}>Environment Report</option>
                                    <option value="incident" {{ old('report_type') == 'incident' ? 'selected' : '' }}>Incident Report</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Report Date *</label>
                                    <input type="date" name="report_date" class="form-control" 
                                           value="{{ old('report_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location/Site *</label>
                                    <input type="text" name="location" class="form-control" 
                                           value="{{ old('location') }}" placeholder="e.g., Main Site, Kampala Office" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department *</label>
                                    <input type="text" name="department" class="form-control" 
                                           value="{{ old('department') }}" placeholder="e.g., Construction, Operations" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Report Title *</label>
                                <input type="text" name="title" class="form-control" 
                                       value="{{ old('title') }}" placeholder="e.g., Safety Inspection Report - Main Site" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea name="description" class="form-control" rows="6" 
                                          placeholder="Describe the QHSE observation, incident, inspection findings, or compliance status..." required>{{ old('description') }}</textarea>
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
                                <label class="form-label">QHSE Access Code *</label>
                                <input type="password" name="access_code" class="form-control" 
                                       placeholder="Enter QHSE access code" required>
                                <div class="form-text">Contact QHSE department for the access code</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Attachments (Optional)</label>
                                <input type="file" name="attachments[]" class="form-control" multiple 
                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                                <div class="form-text">
                                    Upload photos, documents, or evidence (Max 10MB per file)
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-shield-check"></i> Submit QHSE Report
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