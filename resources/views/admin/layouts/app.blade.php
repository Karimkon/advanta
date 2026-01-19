<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin Dashboard')</title>

<!-- Bootstrap + Icons + Fonts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

<style>
:root {
  --primary: #1E88E5;          /* Construction Blue */
  --primary-hover: #42A5F5;    /* Lighter Blue */
  --sidebar-bg: #0D1B2A;       /* Dark Navy */
  --sidebar-text: #E0E1DD;     /* Light Gray Text */
  --sidebar-hover: #fff;       
  --bg: #F8FAFC;               /* Light Gray Background */
  --card-bg: #F1F5F9;          /* Card Background */
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    margin: 0;
    transition: background 0.3s;
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
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
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

/* Accordion Styles */
.sidebar .accordion .accordion-item,
.sidebar .accordion .accordion-collapse,
.sidebar .accordion .accordion-body {
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
}

.sidebar .accordion-button {
    background: transparent !important;
    color: var(--sidebar-text) !important;
    padding: 0.8rem 1rem;
    font-size: 0.95rem;
    font-weight: 500;
    border: none !important;
    border-radius: 8px;
    margin: 0.2rem 0.8rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sidebar .accordion-button:hover, 
.sidebar .accordion-button:not(.collapsed) {
    background: var(--primary) !important;
    color: var(--sidebar-hover) !important;
    transform: translateX(3px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
}

.sidebar .accordion .accordion-body > a {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--sidebar-text) !important;
  padding: 0.8rem 1rem 0.8rem 2.5rem;
  border-radius: 8px;
  margin: 0.1rem 0.8rem;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.25s ease;
  background: transparent;
}

.sidebar .accordion .accordion-body > a:hover,
.sidebar .accordion .accordion-body > a.active {
  background: var(--primary) !important;
  color: var(--sidebar-hover) !important;
  transform: translateX(4px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
    transform: translateY(-2px);
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

/* Scrollbar */
.sidebar::-webkit-scrollbar { width: 6px; }
.sidebar::-webkit-scrollbar-thumb { 
    background: rgba(255,255,255,0.2); 
    border-radius: 3px; 
}
.sidebar::-webkit-scrollbar-track { background: transparent; }

/* Mobile Responsive */
@media (max-width: 768px) {
    .sidebar { 
        left: -260px; 
        border-radius: 0;
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

/* Icon colors */
.sidebar a .bi,
.sidebar .accordion-button .bi,
.sidebar .accordion .accordion-body > a .bi {
  color: inherit !important;
  font-size: 1rem;
  flex-shrink: 0;
  width: 20px;
  text-align: center;
}
</style>
</head>
<body>

<!-- Mobile toggle -->
<div class="mobile-toggle d-md-none"><i class="bi bi-list"></i></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">🏗️ Admin Panel</div>

    <div class="sidebar-content">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="accordion" id="adminAccordion">
            <!-- Projects Management -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#projectsCollapse">
                        <i class="bi bi-folder2"></i> Projects
                    </button>
                </h2>
                <div id="projectsCollapse" class="accordion-collapse collapse {{ request()->is('admin/projects*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.projects.index') }}" class="{{ request()->routeIs('admin.projects.index') ? 'active' : '' }}">
                            <i class="bi bi-list-ul"></i> All Projects
                        </a>
                        <a href="{{ route('admin.projects.create') }}" class="{{ request()->routeIs('admin.projects.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle"></i> New Project
                        </a>
                    </div>
                </div>
            </div>

            <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.milestones.*') ? 'active' : '' }}" 
       href="{{ route('admin.milestones.index') }}">
        <i class="bi bi-flag me-2"></i>
        Manage Milestones
    </a>
</li>

            <!-- Requisitions -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#requisitionsCollapse">
                        <i class="bi bi-file-earmark-text"></i> Requisitions
                    </button>
                </h2>
                <div id="requisitionsCollapse" class="accordion-collapse collapse {{ request()->is('admin/requisitions*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.requisitions.index') }}" class="{{ request()->routeIs('admin.requisitions.index') ? 'active' : '' }}">
                            <i class="bi bi-list-check"></i> All Requisitions
                        </a>
                    </div>
                </div>
            </div>
                        <!-- LPOs -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#lposCollapse">
                        <i class="bi bi-receipt"></i> LPOs
                    </button>
                </h2>
                <div id="lposCollapse" class="accordion-collapse collapse {{ request()->is('admin/lpos*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.lpos.index') }}" class="{{ request()->routeIs('admin.lpos.index') ? 'active' : '' }}">
                            <i class="bi bi-file-text"></i> All LPOs
                        </a>
                    </div>
                </div>
            </div>

          
            <!-- Inventory & Stores -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#inventoryCollapse">
                        <i class="bi bi-box-seam"></i> Inventory & Stores
                    </button>
                </h2>
                <div id="inventoryCollapse" class="accordion-collapse collapse {{ request()->is('admin/stores*') || request()->is('admin/inventory*') ? 'show' : '' }}">
                    <div class="accordion-body">
                        <a href="{{ route('admin.stores.index') }}" class="{{ request()->routeIs('admin.stores.index') ? 'active' : '' }}">
                            <i class="bi bi-shop"></i> Stores Management
                        </a>
                        <a href="{{ route('admin.inventory.index') }}" class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                            <i class="bi bi-boxes"></i> Inventory Items
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product Catalog -->
<div class="accordion-item">
    <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#productCatalogCollapse">
            <i class="bi bi-box-seam"></i> Product Catalog
        </button>
    </h2>
    <div id="productCatalogCollapse" class="accordion-collapse collapse {{ request()->is('admin/product-catalog*') ? 'show' : '' }}">
        <div class="accordion-body">
            <a href="{{ route('admin.product-catalog.index') }}" class="{{ request()->routeIs('admin.product-catalog.index') ? 'active' : '' }}">
                <i class="bi bi-list-ul"></i> All Products
            </a>
            <a href="{{ route('admin.product-catalog.create') }}" class="{{ request()->routeIs('admin.product-catalog.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
            <a href="{{ route('admin.product-categories.create') }}" class="{{ request()->routeIs('admin.product-categories.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> New Category
            </a>
        </div>
    </div>
</div>
        </div>

        <!-- User Management (Direct Link) -->
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Users & Roles
        </a>

        <!-- Office Staff -->
        <a href="{{ route('admin.office-staff.index') }}" class="{{ request()->routeIs('admin.office-staff.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Office Staff
        </a>

        <!-- Clients -->
        <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
            <i class="bi bi-person-check"></i> Clients
        </a>

        <!-- Company Equipments -->
        <a href="{{ route('admin.equipments.index') }}" class="{{ request()->routeIs('admin.equipments.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Company Equipments
        </a>

        <!-- Reports -->
        <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i> Reports & Analytics
        </a>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
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
    @yield('content')
</main>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Mobile sidebar toggle
const toggle = document.querySelector('.mobile-toggle');
const sidebar = document.getElementById('sidebar');
if (toggle) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('active'));
}

// Initialize Select2
document.addEventListener('DOMContentLoaded', function () {
    $('.select2').select2({ 
        width: '100%',
        theme: 'bootstrap-5'
    });
});

// Auto-collapse other accordions when one opens
document.addEventListener('DOMContentLoaded', function() {
    const accordions = document.querySelectorAll('.accordion-button');
    accordions.forEach(accordion => {
        accordion.addEventListener('click', function() {
            const target = this.getAttribute('data-bs-target');
            accordions.forEach(otherAccordion => {
                if (otherAccordion !== this && !otherAccordion.classList.contains('collapsed')) {
                    const otherTarget = otherAccordion.getAttribute('data-bs-target');
                    const otherCollapse = bootstrap.Collapse.getInstance(document.querySelector(otherTarget));
                    if (otherCollapse) {
                        otherCollapse.hide();
                    }
                }
            });
        });
    });
});
</script>

@stack('scripts')
</body>
</html>