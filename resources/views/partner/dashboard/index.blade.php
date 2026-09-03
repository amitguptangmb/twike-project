@extends('layouts.partner')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card np-card np-widget h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="widget-label">Pay In</div>
                            <p class="widget-value">&#x20B9; {{ $payIn }}</p>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary">
                                <i class="ri-arrow-down-circle-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card np-card np-widget h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="widget-label">Payout</div>
                            <p class="widget-value">&#x20B9; {{ $payOut }}</p>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success">
                                <i class="ri-arrow-up-circle-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card np-card np-widget h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="widget-label">Charge Back</div>
                            <p class="widget-value">&#x20B9; {{ $chargeBack }}</p>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-danger-subtle text-danger">
                                <i class="ri-arrow-go-back-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card np-card np-widget h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="widget-label">Holding Amt.</div>
                            <p class="widget-value">&#x20B9; {{ $holdingAmt }}</p>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle text-warning">
                                <i class="ri-lock-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card np-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('partner.dashboard') }}" class="row g-2 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label">From:</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control">
                </div>
                <div class="col-sm-3">
                    <label class="form-label">To:</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control">
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-success" type="submit"><i class="ri-search-line align-middle me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="card np-card h-100">
                <div class="card-header bg-success text-white">Payin Revenue</div>
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th>Total Volume</th><td>&#x20B9; <strong>{{ $totalVolume }}</strong></td></tr>
                            <tr><th>Total Fees</th><td>&#x20B9; <strong>{{ $totalFees }}</strong></td></tr>
                            <tr><th>Total Tax</th><td>&#x20B9; <strong>{{ $totalTax }}</strong></td></tr>
                            <tr><th>Success Ratio</th><td><strong class="text-success">{{ $successRatio }}</strong> %</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-4">
            <div class="card np-card h-100">
                <div class="card-header bg-success text-white">Payin Volumn</div>
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th>Total Transactions</th><td><span class="badge bg-primary">{{ $totalTransactions }}</span></td></tr>
                            <tr><th>Success</th><td><span class="badge bg-success">{{ $success }}</span></td></tr>
                            <tr><th>Failed</th><td><span class="badge bg-danger">{{ $failed }}</span></td></tr>
                            <tr><th>Pending</th><td><span class="badge bg-warning">{{ $pending }}</span></td></tr>
                            <tr><th>Cancelled</th><td><span class="badge bg-info">{{ $cancelled }}</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-4">
            <div class="card np-card h-100">
                <div class="card-header bg-success text-white">Payout Volumn</div>
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th>Total Transactions</th><td><span class="badge bg-primary">{{ $payoutTotalTransactions }}</span></td></tr>
                            <tr><th>Success</th><td><span class="badge bg-success">{{ $payoutSuccess }}</span></td></tr>
                            <tr><th>Failed</th><td><span class="badge bg-danger">{{ $payoutFailed }}</span></td></tr>
                            <tr><th>Pending</th><td><span class="badge bg-warning">{{ $payoutPending }}</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
