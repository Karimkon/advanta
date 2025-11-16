@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
  @include('admin.partials._alerts')

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-0">Welcome, {{ auth()->user()->name }}</h2>
      <small class="text-muted">Overview of projects, requisitions, LPOs and finance</small>
    </div>
    <div class="d-flex gap-2">
      <!-- Date Filter -->
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-calendar me-1"></i> {{ $title }}
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="?filter=today">Today</a></li>
          <li><a class="dropdown-item" href="?filter=week">This Week</a></li>
          <li><a class="dropdown-item" href="?filter=month">This Month</a></li>
        </ul>
      </div>
      <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Project
      </a>
    </div>
  </div>

  <!-- Statistics Cards - Fixed UI -->
  <div class="row g-4">
    <!-- Projects Card -->
    <div class="col-xl-3 col-md-6">
      <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h6 class="card-subtitle text-muted mb-2">Projects</h6>
              <h2 class="fw-bold text-primary mb-1">{{ $stats['projects_count'] }}</h2>
              <small class="text-success">
                <i class="bi bi-circle-fill me-1"></i>Active: {{ $stats['active_projects'] }}
              </small>
            </div>
            <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-building fs-4 text-primary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Suppliers Card -->
    <div class="col-xl-3 col-md-6">
      <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h6 class="card-subtitle text-muted mb-2">Suppliers</h6>
              <h2 class="fw-bold text-success mb-1">{{ $stats['suppliers_total'] }}</h2>
              <small class="text-muted">Vendor partners</small>
            </div>
            <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-truck fs-4 text-success"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Requisitions Card -->
    <div class="col-xl-3 col-md-6">
      <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h6 class="card-subtitle text-muted mb-2">Requisitions</h6>
              <h2 class="fw-bold text-warning mb-1">{{ $stats['requisitions_pending'] + $stats['requisitions_approved'] + $stats['requisitions_rejected'] }}</h2>
              <small class="text-warning">
                <i class="bi bi-clock me-1"></i>Pending: {{ $stats['requisitions_pending'] }}
              </small>
            </div>
            <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-clipboard-check fs-4 text-warning"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payments Card -->
    <div class="col-xl-3 col-md-6">
      <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h6 class="card-subtitle text-muted mb-2">Payment ({{ $title }})</h6>
              <h2 class="fw-bold text-info mb-1">UGX {{ number_format($financials['total_payments']) }}</h2>
              <small class="text-success">
                <i class="bi bi-check-circle me-1"></i>Completed: UGX {{ number_format($financials['completed_payments']) }}
              </small>
            </div>
            <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-cash-coin fs-4 text-info"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- LPOs Card -->
    <div class="col-xl-3 col-md-6">
      <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h6 class="card-subtitle text-muted mb-2">LPOs Issued</h6>
              <h2 class="fw-bold text-purple mb-1">{{ $stats['lpos_issued'] }}</h2>
              <small class="text-muted">Purchase orders</small>
            </div>
            <div class="icon-wrapper bg-purple bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-receipt fs-4 text-purple"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory Card -->
    <div class="col-xl-3 col-md-6">
      <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h6 class="card-subtitle text-muted mb-2">Inventory Items</h6>
              <h2 class="fw-bold text-indigo mb-1">{{ $inventory['total_items'] }}</h2>
              <small class="text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>Low Stock: {{ $inventory['low_stock_items'] }}
              </small>
            </div>
            <div class="icon-wrapper bg-indigo bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-box-seam fs-4 text-indigo"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Card -->
    <div class="col-xl-3 col-md-6">
      <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h6 class="card-subtitle text-muted mb-2">Users</h6>
              <h2 class="fw-bold text-teal mb-1">{{ $stats['users_total'] }}</h2>
              <small class="text-muted">System accounts</small>
            </div>
            <div class="icon-wrapper bg-teal bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-people fs-4 text-teal"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Requisition Status Card -->
    <div class="col-xl-3 col-md-6">
      <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h6 class="card-subtitle text-muted mb-2">Req. Status</h6>
              <div class="d-flex gap-2 mb-2">
                <span class="badge bg-warning">P: {{ $stats['requisitions_pending'] }}</span>
                <span class="badge bg-success">A: {{ $stats['requisitions_approved'] }}</span>
                <span class="badge bg-danger">R: {{ $stats['requisitions_rejected'] }}</span>
              </div>
              <small class="text-muted">Approval status</small>
            </div>
            <div class="icon-wrapper bg-secondary bg-opacity-10 rounded-circle p-3">
              <i class="bi bi-pie-chart fs-4 text-secondary"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity & Quick Actions -->
  <div class="row mt-4">
    <!-- Recent Requisitions & Chart -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">Recent Requisitions</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th>Ref</th>
                  <th>Project</th>
                  <th>Requested By</th>
                  <th>Estimated</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              @forelse($recentRequisitions as $requisition)
    <tr>
        <td>{{ $requisition->ref }}</td>
        <td>{{ $requisition->project->name }}</td>
        <td>{{ $requisition->requester->name ?? 'N/A' }}</td>
        <td>UGX {{ number_format($requisition->estimated_total, 2) }}</td>
        <td>
            <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
            </span>
        </td>
        <td>{{ $requisition->created_at->format('M d, Y') }}</td>
        <td>
            <a href="{{ route('admin.requisitions.show', $requisition) }}" class="btn btn-sm btn-outline-primary">
                View
            </a>
        </td>
    </tr>
@empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No requisitions found
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">Requisitions Trend (Last 7 Days)</h5>
        </div>
        <div class="card-body">
          <canvas id="requisitionsChart" height="100"></canvas>
        </div>
      </div>
    </div>

    <!-- Quick Actions & Recent Projects -->
    <div class="col-lg-4">
      <!-- Quick Actions -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            @foreach([
              ['route' => 'admin.requisitions.index', 'icon' => 'clipboard-check', 'text' => 'Manage Requisitions'],
              ['route' => 'admin.procurement.index', 'icon' => 'cart-check', 'text' => 'Procurement'],
              ['route' => 'admin.lpos.index', 'icon' => 'receipt', 'text' => 'LPOs Management'],
              ['route' => 'admin.finance.index', 'icon' => 'cash-coin', 'text' => 'Finance'],
              ['route' => 'admin.stores.index', 'icon' => 'box-seam', 'text' => 'Stores'],
              ['route' => 'admin.users.index', 'icon' => 'people', 'text' => 'User Management']
            ] as $action)
            <a href="{{ route($action['route']) }}" class="btn btn-outline-secondary text-start py-2">
              <i class="bi bi-{{ $action['icon'] }} me-2"></i> {{ $action['text'] }}
            </a>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Recent Projects -->
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0">
          <h5 class="mb-0">Recent Projects</h5>
        </div>
        <div class="card-body">
          @forelse($recentProjects as $project)
          <div class="project-item d-flex align-items-center mb-3 pb-3 border-bottom">
            <div class="project-icon bg-primary bg-opacity-10 rounded-circle p-2 me-3">
              <i class="bi bi-folder text-primary"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-1">{{ $project->name }}</h6>
              <small class="text-muted d-block">{{ Str::limit($project->description, 40) }}</small>
              <span class="badge bg-{{ $project->status === 'active' ? 'success' : 'secondary' }} mt-1">
                {{ ucfirst($project->status) }}
              </span>
            </div>
          </div>
          @empty
          <div class="text-center text-muted py-4">
            <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
            No projects found
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('requisitionsChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Requisitions',
                data: @json($requisitionSeries),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>

<style>
.stat-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}
.icon-wrapper {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.text-purple { color: #6f42c1 !important; }
.bg-purple { background-color: #6f42c1 !important; }
.text-indigo { color: #6610f2 !important; }
.bg-indigo { background-color: #6610f2 !important; }
.text-teal { color: #20c997 !important; }
.bg-teal { background-color: #20c997 !important; }
.project-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
</style>
@endsection