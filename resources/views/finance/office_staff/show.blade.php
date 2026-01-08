@extends($layout ?? 'finance.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Staff Profile: {{ $officeStaff->name }}</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'office-staff.index') }}">Office Staff</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4">
        <div class="card overflow-hidden">
            <div class="bg-primary bg-soft">
                <div class="row">
                    <div class="col-7">
                        <div class="text-primary p-3">
                            <h5 class="text-primary">{{ $officeStaff->role }}</h5>
                            <p>{{ $officeStaff->department }}</p>
                        </div>
                    </div>
                    <div class="col-5 align-self-end">
                        <img src="{{ asset('assets/images/profile-img.png') }}" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="avatar-md profile-user-wid mb-4">
                            <span class="avatar-title rounded-circle bg-light text-primary font-size-24">
                                {{ strtoupper(substr($officeStaff->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="pt-4">
                            <h5 class="font-size-15 text-truncate">{{ $officeStaff->name }}</h5>
                            <p class="text-muted mb-0 text-truncate">{{ $officeStaff->role }}</p>
                        </div>
                    </div>
                </div>

                <div class="card-body border-top">
                    <!-- Staff Details -->
                    <div class="text-muted">
                        <div class="table-responsive">
                            <table class="table table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row">Email :</th>
                                        <td>{{ $officeStaff->email }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Phone :</th>
                                        <td>{{ $officeStaff->phone }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Joined :</th>
                                        <td>{{ optional($officeStaff->joined_date)->format('d M, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Salary :</th>
                                        <td class="text-success font-weight-bold">{{ number_format($officeStaff->salary) }} UGX</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Status :</th>
                                        <td>
                                            <span class="badge bg-{{ $officeStaff->status == 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($officeStaff->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route($routePrefix . 'office-staff.edit', $officeStaff->id) }}" class="btn btn-primary waves-effect waves-light btn-sm">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Salary Payment History</h4>
                    <button type="button" class="btn btn-success waves-effect waves-light btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="bx bx-plus me-1"></i> Record Payment
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Month For</th>
                                <th>Amount (UGX)</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Paid By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($officeStaff->payments->sortByDesc('payment_date') as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                <td>{{ $payment->month_for }}</td>
                                <td class="font-weight-bold">{{ number_format($payment->amount) }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td>{{ $payment->paidBy ? $payment->paidBy->name : 'N/A' }}</td>
                                <td>
                                    <form action="{{ route($routePrefix . 'salary-payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this payment record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm p-1" title="Delete"><i class="bx bx-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No payment records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalLabel">Record Salary Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route($routePrefix . 'salary-payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="office_staff_id" value="{{ $officeStaff->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Month For</label>
                        <input type="text" class="form-control" name="month_for" placeholder="e.g. January 2026" required value="{{ date('F Y') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (UGX)</label>
                        <input type="number" class="form-control" name="amount" value="{{ $officeStaff->salary }}" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference (Optional)</label>
                        <input type="text" class="form-control" name="reference" placeholder="e.g. Transaction ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
