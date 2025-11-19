<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Surveyor Dashboard')</title>

<!-- Bootstrap + Icons + Fonts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

<style>
:root {
  --primary: #10B981;          /* Surveyor Green */
  --primary-hover: #34D399;    /* Lighter Green */
  --sidebar-bg: #0F172A;       /* Dark Navy */
  --sidebar-text: #E2E8F0;     /* Light Gray Text */
  --sidebar-hover: #fff;       
  --bg: #F8FAFC;               /* Light Gray Background */
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    margin: 0;
}

/* Sidebar */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, var(--sidebar-bg) 0%, #0A1520 100%);
    color: var(--sidebar-text);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s ease;
    z-index: 1000;
    overflow-y: auto;
    box-shadow: 2px 0 15px rgba(0,0,0,0.2);
}

.sidebar-logo {
    text-align: center;
    font-weight: 700;
    font-size: 1.4rem;
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    color: var(--primary);
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--sidebar-text);
    padding: 0.8rem 1rem;
    border-radius: 8px;
    margin: 0.2rem 0.8rem;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.sidebar a:hover, .sidebar a.active {
    background: var(--primary);
    color: var(--sidebar-hover);
    transform: translateX(4px);
}

/* Sidebar Footer */
.sidebar-footer {
    margin-top: auto;
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.2);
}

.logout-button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem;
    border-radius: 8px;
    background: #EF4444;
    color: #fff;
    border: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.logout-button:hover { 
    background: #DC2626; 
}

/* Main Content */
.content {
    margin-left: 260px;
    padding: 2rem;
    min-height: 100vh;
    background: var(--bg);
}

/* Mobile Toggle */
.mobile-toggle {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    font-size: 1.5rem;
    color: var(--primary);
    z-index: 1100;
    cursor: pointer;
    background: white;
    border-radius: 8px;
    padding: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .sidebar { 
        left: -260px; 
    }
    .sidebar.active { 
        left: 0; 
    }
    .content { 
        margin-left: 0; 
        padding: 1rem;
    }
    .mobile-toggle {
        display: block;
    }
}

/* Top Navigation */
.navbar-custom {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1rem 2rem;
    margin-bottom: 0;
    border-bottom: 1px solid #E2E8F0;
}
</style>
</head>
<body>

<!-- Mobile toggle -->
<div class="mobile-toggle d-md-none"><i class="bi bi-list"></i></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">📐 Surveyor Panel</div>

    <div class="sidebar-content">
        <a href="{{ route('surveyor.dashboard') }}" class="{{ request()->routeIs('surveyor.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

       <!-- Milestones -->
<a href="{{ route('surveyor.milestones.index') }}" class="{{ request()->routeIs('surveyor.milestones.index') ? 'active' : '' }}">
    <i class="bi bi-flag"></i> Milestones
</a>

<!-- Projects -->
<a href="{{ route('surveyor.projects.index') }}" class="{{ request()->routeIs('surveyor.projects.index') ? 'active' : '' }}">
    <i class="bi bi-building"></i> Projects
</a>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="text-center mb-3">
            <div class="mb-2">
                <i class="bi bi-person-fill text-primary fs-4"></i>
            </div>
            <small class="text-muted d-block">{{ auth()->user()->name }}</small>
            <small class="text-muted">Surveyor</small>
        </div>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-button">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</div>

<!-- Main Content -->
<main class="content">
    <!-- Simple Top Navigation -->
    <nav class="navbar navbar-custom">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <h4 class="mb-0 me-3">@yield('title', 'Surveyor Dashboard')</h4>
                <span class="badge bg-primary">Surveyor</span>
            </div>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item">
                    <span class="nav-link">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container-fluid mt-4">
        @include('surveyor.partials._alerts')
        @yield('content')
    </div>
</main>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Mobile sidebar toggle
const toggle = document.querySelector('.mobile-toggle');
const sidebar = document.getElementById('sidebar');
if (toggle) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('active'));
}

// Close sidebar when clicking on mobile
document.addEventListener('click', function(event) {
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-toggle');
        if (sidebar.classList.contains('active') && 
            !sidebar.contains(event.target) && 
            !toggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>

@stack('scripts')
</body>
</html>