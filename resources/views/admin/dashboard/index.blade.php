@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

    <div class="card np-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-end">
                <div class="col-sm-2">
                    <label class="form-label">From:</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control">
                </div>
                <div class="col-sm-2">
                    <label class="form-label">To:</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="form-control">
                </div>
                <div class="col-sm-2">
                    <label class="form-label">User:</label>
                    <select name="user_id" class="form-select">
                        <option value="0" @selected($userId == 0)>ALL</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->UserId }}" @selected($userId == $u->UserId)>{{ $u->UserId }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-primary" type="submit"><i class="ri-search-line align-middle me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card np-card np-widget h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="widget-label">Total Pay In</div>
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
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card np-card np-widget h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="widget-label">Total Payout</div>
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
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card np-card np-widget h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="widget-label">Settlement Amount</div>
                            <p class="widget-value">&#x20B9; {{ $walletAmount }}</p>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle text-info">
                                <i class="ri-bank-line"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="card np-card h-100">
                <div class="card-header">Pay In vs Payout vs Settlement</div>
                <div class="card-body">
                    <div id="np-overview-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 mb-4">
            <div class="card np-card h-100">
                <div class="card-header">Payin Revenue</div>
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
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card np-card">
                <div class="card-header">Payin Volumn</div>
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

        <div class="col-xl-6 mb-4">
            <div class="card np-card">
                <div class="card-header">Payout Volumn</div>
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

    <div class="row mt-3">
        <div class="col-sm-12">
            <div class="card np-card">
                <div class="card-header">User Pay IN Report</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead><tr><th>#</th><th>Marchant</th><th>Total Amount</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse ($userDetails as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->BusinessName }}</td>
                                    <td>{{ $row->total }}</td>
                                    <td>{{ $row->date }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-sm-12">
            <div class="card np-card">
                <div class="card-header">User Pay OUT Report</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead><tr><th>#</th><th>Marchant</th><th>Total Amount</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse ($payoutReportRows as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->BusinessName }}</td>
                                    <td>{{ $row->total }}</td>
                                    <td>{{ $row->date }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-sm-12">
            <div class="card np-card">
                <div class="card-header">User Pay and Payout Report</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead><tr><th>#</th><th>Marchant</th><th>Payin Amount</th><th>Payout Amount</th><th>Hold Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($payAndPayoutRows as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row->BusinessName }}</td>
                                    <td>{{ $row->Collection_Amount }}</td>
                                    <td>{{ $row->Available_Amount }}</td>
                                    <td>{{ $row->Hold_Amt }}</td>
                                    <td class="{{ $row->Status === 'Active' ? 'text-success' : 'text-danger' }}">{{ $row->Status }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        (function () {
            var el = document.querySelector('#np-overview-chart');
            if (!el || typeof ApexCharts === 'undefined') return;

            var payIn = parseFloat(@json($payIn)) || 0;
            var payOut = parseFloat(@json($payOut)) || 0;
            var walletAmount = parseFloat(@json($walletAmount)) || 0;

            var chart = new ApexCharts(el, {
                chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                series: [{ name: 'Amount (₹)', data: [payIn, payOut, walletAmount] }],
                xaxis: { categories: ['Pay In', 'Payout', 'Settlement'] },
                plotOptions: { bar: { columnWidth: '45%', borderRadius: 4, distributed: true } },
                dataLabels: { enabled: true, formatter: function (v) { return '₹ ' + v; } },
                legend: { show: false },
                colors: ['#405189', '#0ab39c', '#299cdb']
            });
            chart.render();
        })();
    </script>
@endpush
