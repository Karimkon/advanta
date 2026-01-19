<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Client Portal') - Advanta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        .navbar-brand img {
            height: 40px;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(180deg, #1e3a5f 0%, #0f1f32 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 12px;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .content-wrapper {
            padding: 24px;
        }
        .stat-card {
            border-radius: 12px;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .progress-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#10b981 var(--progress), #e5e7eb var(--progress));
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .progress-circle-inner {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('client.dashboard') }}">
                <img src="{{ asset('images/advanta.jpg') }}" alt="Advanta" class="rounded me-2">
                <span>Client Portal</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::guard('client')->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small">{{ Auth::guard('client')->user()->email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('client.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar d-none d-md-block">
                <nav class="py-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('client.dashboard') }}" class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        @foreach(Auth::guard('client')->user()->projects as $project)
                            <li class="nav-item">
                                <a href="{{ route('client.projects.milestones', $project->id) }}" class="nav-link {{ request()->is('client/projects/'.$project->id.'*') ? 'active' : '' }}">
                                    <i class="bi bi-building"></i> {{ Str::limit($project->name, 20) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto content-wrapper">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
