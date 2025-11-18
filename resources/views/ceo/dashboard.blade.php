@extends('ceo.layouts.app')

@section('title', 'CEO Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">CEO Dashboard</h2>
            <p class="text-muted mb-0">Executive overview and strategic insights</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.requisitions.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Approvals
                @if($pendingRequisitions->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingRequisitions->count() }}</span>
                @endif
            </a>
            <a href="{{ route('ceo.reports.index') }}" class="btn btn-info">
                <i class="bi bi-bar-chart"></i> Financial Reports
            </a>
        </div>
    </div>

    <!-- Executive Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Financial Overview -->
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Payments</h6>
                            <h2 class="fw-bold text-primary mb-1">UGX {{ number_format($financialStats['total_payments'] / 1000000, 2) }}M</h2>
                            <small class="text-primary">All company payments</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-credit-card fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Pending Approval</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $pendingRequisitions->count() }}</h2>
                            <small class="text-warning">Requisitions awaiting decision</small>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-clock fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">This Month</h6>
                            <h2 class="fw-bold text-info mb-1">UGX {{ number_format($financialStats['this_month_spending']) }}</h2>
                            <small class="text-info">Current month spending</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-graph-up fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">CEO Approved</h6>
                            <h2 class="fw-bold text-success mb-1">{{ $stats->ceo_approved ?? 0 }}</h2>
                            <small class="text-success">Your approved requisitions</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Pending Requisitions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock text-warning me-2"></i>
                        Pending Approval
                    </h5>
                    <a href="{{ route('ceo.requisitions.pending') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @forelse($pendingRequisitions as $requisition)
                        <div class="requisition-item border-start border-3 border-warning ps-3 mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-2">{{ $requisition->ref }}</h6>
                                        <span class="badge bg-warning">Awaiting Approval</span>
                                    </div>
                                    <p class="mb-1 text-muted small">
                                        <i class="bi bi-building me-1"></i>{{ $requisition->project->name }}
                                    </p>
                                    <p class="mb-1 text-muted small">
                                        <i class="bi bi-person me-1"></i>Requested by {{ $requisition->requester->name }}
                                    </p>
                                    <p class="mb-0 fw-bold text-primary">
                                        UGX {{ number_format($requisition->estimated_total, 2) }}
                                    </p>
                                </div>
                                <a href="{{ route('ceo.requisitions.show', $requisition) }}" 
                                   class="btn btn-sm btn-outline-primary align-self-start">Review</a>
                            </div>
                            @if($requisition->lpo)
                                <small class="text-info">
                                    <i class="bi bi-receipt me-1"></i>LPO: {{ $requisition->lpo->lpo_number }}
                                </small>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                            <h5>All Clear!</h5>
                            <p class="mb-0">No pending requisitions for approval.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Project Performance -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy text-primary me-2"></i>
                        Project Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Project</th>
                                    <th>Total Spent</th>
                                    <th>Requisitions</th>
                                    <th>Payment Ratio</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectPerformance as $project)
                                    <tr>
                                        <td>
                                            <strong>{{ $project['name'] }}</strong>
                                        </td>
                                        <td class="fw-bold">UGX {{ number_format($project['total_spent'], 2) }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $project['requisition_count'] }}</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $project['payment_ratio'] }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ number_format($project['payment_ratio'], 1) }}%</small>
                                        </td>
                                        <td>
                                            @if($project['total_spent'] > 10000000)
                                                <span class="badge bg-danger">High Spend</span>
                                            @elseif($project['total_spent'] > 5000000)
                                                <span class="badge bg-warning">Medium Spend</span>
                                            @else
                                                <span class="badge bg-success">Low Spend</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-folder-x display-4 d-block mb-2"></i>
                                            No project performance data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Project Milestones Overview -->
<div class="card shadow-sm mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-flag text-info me-2"></i>
            Project Milestones Overview
        </h5>
        <a href="{{ route('ceo.milestones.index') }}" class="btn btn-sm btn-outline-primary">View All Milestones</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Progress</th>
                        <th>Milestones</th>
                        <th>Completion</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectMilestones as $project)
                        <tr>
                            <td>
                                <strong>{{ $project['name'] }}</strong>
                                @if($project['overdue_milestones'] > 0)
                                    <span class="badge bg-danger ms-1">{{ $project['overdue_milestones'] }} overdue</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: {{ $project['completion_rate'] }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $project['completion_rate'] }}%</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $project['completed_milestones'] }}/{{ $project['total_milestones'] }}
                                </span>
                            </td>
                            <td>
                                @if($project['latest_milestone'])
                                    <small class="text-muted">
                                        {{ $project['latest_milestone']->title }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $project['status'] === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($project['status']) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('ceo.milestones.project', $project['id']) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-flag display-4 d-block mb-2"></i>
                                No milestone data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Attention Needed Milestones -->
@if($attentionMilestones->count() > 0)
<div class="card shadow-sm mt-4 border-warning">
    <div class="card-header bg-warning bg-opacity-10">
        <h5 class="mb-0 text-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Milestones Needing Attention
        </h5>
    </div>
    <div class="card-body">
        @foreach($attentionMilestones as $milestone)
            <div class="border-start border-3 border-warning ps-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">{{ $milestone->title }}</h6>
                        <p class="text-muted mb-1 small">{{ $milestone->project->name }}</p>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-warning">
                                <i class="bi bi-calendar me-1"></i>
                                Due: {{ $milestone->due_date->format('M d, Y') }}
                            </small>
                            <span class="badge bg-{{ $milestone->getStatusBadgeClass() }}">
                                {{ ucfirst($milestone->status) }}
                            </span>
                            @if($milestone->completion_percentage > 0)
                                <small class="text-info">
                                    {{ $milestone->completion_percentage }}% Complete
                                </small>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('ceo.milestones.show', ['project' => $milestone->project, 'milestone' => $milestone]) }}" 
                       class="btn btn-sm btn-warning">Review</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-speedometer2 text-info me-2"></i>
                        Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">{{ $stats->total ?? 0 }}</h4>
                                <small class="text-muted">Total Requisitions</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ $stats->completed ?? 0 }}</h4>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">UGX {{ number_format($financialStats['pending_payments']) }}</h4>
                                <small class="text-muted">Pending Payments</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-danger mb-1">UGX {{ number_format($financialStats['total_expenses']) }}</h4>
                                <small class="text-muted">Total Expenses</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Financial Activities -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-activity text-success me-2"></i>
                        Recent Activities
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($recentFinancialActivities as $activity)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-badge bg-{{ $activity['color'] }} bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi {{ $activity['icon'] }} text-{{ $activity['color'] }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ Str::limit($activity['description'], 30) }}</h6>
                                        <p class="mb-0 fw-bold text-{{ $activity['color'] }}">
                                            UGX {{ number_format($activity['amount'], 2) }}
                                        </p>
                                        <small class="text-muted">{{ $activity['date']->format('M d, H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-activity display-4 d-block mb-2"></i>
                                <p>No recent activities</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Monthly Trends -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up text-warning me-2"></i>
                        Spending Trends
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($monthlyTrends as $trend)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted">{{ $trend['period'] }}</span>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-2">UGX {{ number_format($trend['amount']) }}</span>
                                <i class="bi bi-arrow-{{ $trend['trend'] }}-circle text-{{ $trend['trend'] == 'up' ? 'success' : 'secondary' }}"></i>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-graph-up display-4 d-block mb-2"></i>
                            <p>No trend data available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    

    <!-- Recent Approvals -->
    @if($recentApprovals->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Your Recent Approvals
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($recentApprovals as $requisition)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-success border-1 h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $requisition->ref }}</h6>
                                        <p class="card-text text-muted small mb-2">{{ $requisition->project->name }}</p>
                                        <p class="card-text fw-bold text-success mb-2">
                                            UGX {{ number_format($requisition->estimated_total, 2) }}
                                        </p>
                                        <small class="text-muted">
                                            Approved {{ $requisition->updated_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Inventory Quick Overview -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-box-seam text-primary me-2"></i>
                    Inventory Quick Overview
                </h5>
                <a href="{{ route('ceo.inventory.index') }}" class="btn btn-sm btn-outline-primary">View Full Report</a>
            </div>
            <div class="card-body">
                @php
                    // Quick inventory stats - you can pass these from controller
                    $quickInventoryStats = [
                        'total_stores' => \App\Models\Store::count(),
                        'total_items' => \App\Models\InventoryItem::count(),
                        'total_value' => \App\Models\InventoryItem::sum(DB::raw('quantity * unit_price')),
                        'low_stock_items' => \App\Models\InventoryItem::whereColumn('quantity', '<', 'reorder_level')->where('quantity', '>', 0)->count(),
                    ];
                @endphp
                <div class="row text-center">
                    <div class="col-3">
                        <h4 class="text-primary">{{ $quickInventoryStats['total_stores'] }}</h4>
                        <small class="text-muted">Stores</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-success">{{ $quickInventoryStats['total_items'] }}</h4>
                        <small class="text-muted">Items</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-info">UGX {{ number_format($quickInventoryStats['total_value'], 2) }}</h4>
                        <small class="text-muted">Total Value</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-warning">{{ $quickInventoryStats['low_stock_items'] }}</h4>
                        <small class="text-muted">Low Stock</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<style>
.stat-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: none;
    border-radius: 12px;
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

.requisition-item {
    transition: background-color 0.2s ease;
}

.requisition-item:hover {
    background-color: rgba(255, 193, 7, 0.05);
}

.timeline-badge {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,0.05);
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
}

.border-success {
    border-color: #198754 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth animations to cards
    const cards = document.querySelectorAll('.stat-card, .card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
    });

    // Animate cards on load
    setTimeout(() => {
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }, 100);
});
</script>
@endsection