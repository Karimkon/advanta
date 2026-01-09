<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subcontractor Login - Advanta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #c2410c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #f97316, #ea580c);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .login-header h3 {
            margin: 0;
            font-weight: 700;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 0.2rem rgba(249, 115, 22, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #f97316, #ea580c);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #ea580c, #c2410c);
        }
        .back-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <a href="/" class="back-link">
                        <i class="bi bi-arrow-left me-2"></i> Back to Portal
                    </a>
                </div>

                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-building fs-1 mb-2 d-block"></i>
                        <h3>Subcontractor Portal</h3>
                        <small class="opacity-75">Advanta Uganda Limited</small>
                    </div>

                    <div class="login-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                @foreach($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('subcontractor.login.submit') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           placeholder="Enter your email"
                                           required
                                           autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           placeholder="Enter your password"
                                           required>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <button type="submit" class="btn btn-login btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Login
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Contact administration if you don't have login credentials
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
