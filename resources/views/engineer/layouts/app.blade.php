<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Engineer Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    <style>
        :root {
            --primary: #f59e0b;
            --primary-hover: #d97706;
            --sidebar-bg: #1e293b;
            --sidebar-text: #e2e8f0;
            --sidebar-hover: #fff;
            --bg: #f8fafc;
            --card-bg: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            margin: 0;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #0f172a 100%);
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
            background: rgba(255,255,255,0.05);
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

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
            background: #ef4444;
            color: #fff;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-button:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            background: var(--bg);
        }

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

        @media (max-width: 768px) {
            .sidebar {
                left: -260px;
            }
            .sidebar.active {
                left: 0;
                box-shadow: 4px 0 20px rgba(0,0,0,0.3);
            }
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            .mobile-toggle {
                display: block;
            }
        }

        .stat-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .store-item-table {
            max-height: 400px;
            overflow-y: auto;
        }
        .store-item-row:hover {
            background-color: #f8f9fa;
        }
        .selected-item-badge {
            font-size: 0.75em;
        }
    </style>
</head>
<body>

<div class="mobile-toggle d-md-none"><i class="bi bi-list"></i></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">🏗️ Engineer</div>

    <div class="sidebar-content">
        <a href="{{ route('engineer.dashboard') }}" class="{{ request()->routeIs('engineer.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('engineer.requisitions.index') }}" class="{{ request()->routeIs('engineer.requisitions.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> My Requisitions
        </a>

        <a href="{{ route('engineer.requisitions.create') }}" class="{{ request()->routeIs('engineer.requisitions.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> Create Requisition
        </a>

        <a href="{{ route('engineer.requisitions.pending') }}" class="{{ request()->routeIs('engineer.requisitions.pending') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Pending Approvals
        </a>

        <a href="{{ route('engineer.projects.index') }}" class="{{ request()->routeIs('engineer.projects.*') ? 'active' : '' }}">
            <i class="bi bi-folder2"></i> My Projects
        </a>
    </div>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-button">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</div>

<main class="content">
    @yield('content')
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const toggle = document.querySelector('.mobile-toggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('active'));
    }

    document.addEventListener('DOMContentLoaded', function () {
        $('.select2').select2({
            width: '100%',
            theme: 'bootstrap-5'
        });
    });
</script>

@stack('scripts')
</body>
</html>