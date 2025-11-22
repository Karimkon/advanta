<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Finance Dashboard - Advanta Uganda Ltd')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --sidebar-bg: #1f2937;
            --sidebar-text: #e5e7eb;
            --sidebar-hover: #fff;
            --bg: #f8fafc;
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
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #111827 100%);
            color: var(--sidebar-text);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
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
        }

        .content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            background: var(--bg);
        }

        .stat-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

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
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">💰 Finance</div>

    <div class="sidebar-content">
        <a href="{{ route('finance.dashboard') }}" class="{{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('finance.payments.index') }}" class="{{ request()->routeIs('finance.payments.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Payments
        </a>

        <a href="{{ route('finance.payments.pending') }}" class="{{ request()->routeIs('finance.payments.pending') ? 'active' : '' }}">
            <i class="bi bi-clock"></i> Pending Payments
        </a>

        <a href="{{ route('finance.subcontractors.index') }}" class="{{ request()->routeIs('finance.subcontractors.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Subcontractors Costs
        </a>

        <a href="{{ route('finance.labor.index') }}" class="{{ request()->routeIs('finance.labor.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Labor Costs
        </a>

        <a href="{{ route('finance.expenses.index') }}" class="{{ request()->routeIs('finance.expenses.*') ? 'active' : '' }}">
            <i class="bi bi-cash-coin"></i> Expenses
        </a>

        <a href="{{ route('finance.reports.index') }}" class="{{ request()->routeIs('finance.reports.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i> Financial Reports
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
<script>
    // Mobile sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.querySelector('.mobile-toggle');
        const sidebar = document.getElementById('sidebar');
        if (toggle) {
            toggle.addEventListener('click', () => sidebar.classList.toggle('active'));
        }
    });
</script>

@stack('scripts')
</body>
</html>