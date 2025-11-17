<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Store Management - Advanta Group')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2d5016 0%, #4a7c1f 100%);
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
            color: #2d5016 !important;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .store-badge {
            background: linear-gradient(135deg, #4a7c1f, #6ea839);
            color: white;
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
                        <h4 class="text-white mb-1">🏪 Store</h4>
                        <small class="text-white-50">Advanta Group</small>
                    </div>

                    <!-- Navigation -->
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('stores.dashboard') ? 'active' : '' }}" 
                               href="{{ route('stores.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                                @if(isset($pendingCount) && $pendingCount > 0)
                                    <span class="badge bg-danger float-end">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('stores.inventory.*') ? 'active' : '' }}" 
                               href="#">
                                <i class="bi bi-box-seam"></i> Inventory Management
                            </a>
                            <ul class="nav flex-column ms-3">
                                @php
                                    // Show ONLY the store assigned to this user
                                    $userStores = collect($stores ?? [])->filter(function($store) {
                                        $user = auth()->user();
                                        
                                        // Main store manager (ID 6) sees only main store
                                        if ($user->id === 6) {
                                            return $store->isMainStore();
                                        }
                                        
                                        // Other store users see only their project stores
                                        return $store->isProjectStore() && 
                                               $store->project && 
                                               $store->project->users()->where('user_id', $user->id)->exists();
                                    });
                                @endphp
                                
                                @foreach($userStores as $store)
                                    <li>
                                        <a class="nav-link small {{ request()->is('stores/inventory/' . $store->id) ? 'active' : '' }}" 
                                           href="{{ route('stores.inventory.index', $store) }}">
                                            <i class="bi bi-building"></i> {{ $store->display_name }}
                                        </a>
                                    </li>
                                @endforeach
                                
                                @if($userStores->isEmpty())
                                    <li>
                                        <span class="nav-link small text-warning">
                                            <i class="bi bi-exclamation-circle"></i> No stores assigned
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        
                        <!-- Store Releases - Only show if user has stores -->
                        @if($userStores->isNotEmpty())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('stores.releases.*') ? 'active' : '' }}" 
                               href="{{ route('stores.releases.index', $userStores->first()) }}">
                                <i class="bi bi-clipboard-check"></i> Store Releases
                                @if(isset($pendingCount) && $pendingCount > 0)
                                    <span class="badge bg-danger float-end">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('stores.movements.*') ? 'active' : '' }}" 
                            href="{{ route('stores.movements.index', $userStores->first() ?? 1) }}">
                                <i class="bi bi-arrow-left-right"></i> Stock Movements
                            </a>
                        </li>
                        
                        <li class="nav-item mt-4">
                            <hr class="bg-white">
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="#">
                                <i class="bi bi-graph-up"></i> Stock Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-info" href="#">
                                <i class="bi bi-exclamation-triangle"></i> Low Stock Alerts
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
                                        {{ auth()->user()->name }} (Store Manager)
                                    </span>
                                </li>
                                @if(isset($currentStore))
                                <li class="nav-item">
                                    <span class="navbar-text text-muted ms-3">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $currentStore->display_name }}
                                    </span>
                                </li>
                                @endif
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
                                        <li><span class="dropdown-item-text">Pending releases: {{ $pendingCount ?? 0 }}</span></li>
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
                    <!-- Alerts Section -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

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