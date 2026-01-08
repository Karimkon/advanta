@extends($layout ?? 'finance.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Record Staff Salary Payment</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'office-staff.index') }}">Office Staff</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'office-staff.show', $officeStaff) }}">{{ $officeStaff->name }}</a></li>
                    <li class="breadcrumb-item active">Record Payment</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route($routePrefix . 'office-staff.show', $officeStaff) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route($routePrefix . 'salary-payments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="office_staff_id" value="{{ $officeStaff->id }}">

                        <!-- Staff Information -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="bi bi-person-badge"></i> Staff Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Name:</strong> {{ $officeStaff->name }}<br>
                                    <strong>Role:</strong> {{ $officeStaff->role }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Department:</strong> {{ $officeStaff->department }}<br>
                                    <strong>Base Salary:</strong> UGX {{ number_format($officeStaff->salary, 2) }}
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Month For *</label>
                                <input type="text" class="form-control" name="month_for" placeholder="e.g. January 2026" required value="{{ date('F Y') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount (UGX) *</label>
                                <input type="number" class="form-control" name="amount" value="{{ $officeStaff->salary }}" required min="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method *</label>
                                <select class="form-select" name="payment_method">
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Reference (Optional)</label>
                                <input type="text" class="form-control" name="reference" placeholder="e.g. Transaction ID">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes about this payment..."></textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cash-coin"></i> Record Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Staff Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong> {{ $officeStaff->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Department:</strong> {{ $officeStaff->department }}
                    </div>
                    <div class="mb-3">
                        <strong>Role:</strong> {{ $officeStaff->role }}
                    </div>
                    <div class="mb-3">
                        <strong>Joined Date:</strong> {{ optional($officeStaff->joined_date)->format('d M, Y') }}
                    </div>
                    <div class="mb-3">
                        <strong>Total Paid to Date:</strong>
                        <span class="text-primary">UGX {{ number_format($officeStaff->payments->sum('amount'), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
