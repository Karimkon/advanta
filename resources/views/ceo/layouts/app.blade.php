<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CEO Dashboard - Advanta Group')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 4px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .navbar-brand {
            font-weight: 700;
            color: #1e3c72 !important;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .badge-pending {
            background: #ffc107;
            color: #000;
        }
        .badge-approved {
            background: #198754;
        }
        .badge-rejected {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar d-md-block">
                <div class="position-sticky pt-3">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-1">👑 CEO</h4>
                        <small class="text-white-50">Advanta Group</small>
                    </div>

                    <!-- Navigation -->
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ceo.dashboard') ? 'active' : '' }}" 
                               href="{{ route('ceo.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                                @if(isset($pendingCount) && $pendingCount > 0)
                                    <span class="badge bg-danger float-end">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ceo.requisitions.pending') ? 'active' : '' }}" 
                               href="{{ route('ceo.requisitions.pending') }}">
                                <i class="bi bi-clock"></i> Pending Approval
                                @if(isset($pendingCount) && $pendingCount > 0)
                                    <span class="badge bg-danger float-end">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ceo.requisitions.index') ? 'active' : '' }}" 
                               href="{{ route('ceo.requisitions.index') }}">
                                <i class="bi bi-list-check"></i> All Requisitions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ceo.lpos.*') ? 'active' : '' }}" 
                               href="{{ route('ceo.lpos.index') }}">
                                <i class="bi bi-receipt"></i> LPO Management
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <hr class="bg-white">
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="{{ route('ceo.dashboard') }}">
                                <i class="bi bi-graph-up"></i> Financial Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-info" href="{{ route('ceo.dashboard') }}">
                                <i class="bi bi-bar-chart"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav me-auto">
                                <li class="nav-item">
                                    <span class="navbar-text text-muted">
                                        <i class="bi bi-person-circle me-1"></i>
                                        {{ auth()->user()->name }} (CEO)
                                    </span>
                                </li>
                            </ul>
                            
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-bell"></i>
                                        @if(isset($pendingCount) && $pendingCount > 0)
                                            <span class="badge bg-danger">{{ $pendingCount }}</span>
                                        @endif
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><span class="dropdown-item-text">Pending approvals: {{ $pendingCount ?? 0 }}</span></li>
                                    </ul>
                                </li>
                                <li class="nav-item">
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <!-- Main Content Area -->
                <main class="py-4">
                    @include('ceo.partials.alerts')
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>