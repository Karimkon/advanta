<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - Advanta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #0f1f32 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 420px;
            width: 100%;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f1f32 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header img {
            height: 60px;
            margin-bottom: 15px;
        }
        .login-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e5e7eb;
        }
        .form-control:focus {
            border-color: #1e3a5f;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
        }
        .btn-login {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f1f32 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 58, 95, 0.4);
        }
        .back-link {
            color: #6b7280;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #1e3a5f;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('images/advanta.jpg') }}" alt="Advanta" class="rounded">
            <h4 class="mb-1">Client Portal</h4>
            <p class="mb-0 opacity-75">View your project progress</p>
        </div>
        <div class="login-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('client.login.submit') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-medium">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter your password" required>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login to Portal
                </button>

                <div class="text-center">
                    <a href="/" class="back-link">
                        <i class="bi bi-arrow-left"></i> Back to Home
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
