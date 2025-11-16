@extends('ceo.layouts.app')

@section('title', 'LPO Management - CEO')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">LPO Management</h2>
            <p class="text-muted mb-0">Overview of all Local Purchase Orders</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ \App\Models\Lpo::count() }}</h4>
                    <small>Total LPOs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ \App\Models\Lpo::where('status', 'draft')->count() }}</h4>
                    <small>Draft LPOs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ \App\Models\Lpo::where('status', 'issued')->count() }}</h4>
                    <small>Issued LPOs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ \App\Models\Lpo::where('status', 'delivered')->count() }}</h4>
                    <small>Delivered</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>CEO LPO Management:</strong> As CEO, you can review and approve LPOs before they are issued to suppliers. 
                Use the requisitions section to manage individual LPO approvals.
            </div>
        </div>
    </div>

    <!-- Recent LPOs -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Recent LPOs</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                LPO management is primarily handled through the requisitions approval process. 
                Visit <a href="{{ route('ceo.requisitions.pending') }}" class="alert-link">Pending Requisitions</a> to review and approve LPOs.
            </div>
            
            <div class="text-center text-muted py-4">
                <i class="bi bi-receipt display-4 d-block mb-2"></i>
                <p>LPO management integrated with requisition workflow.</p>
                <a href="{{ route('ceo.requisitions.pending') }}" class="btn btn-primary">
                    <i class="bi bi-clock"></i> Go to Pending Approvals
                </a>
            </div>
        </div>
    </div>
</div>
@endsection