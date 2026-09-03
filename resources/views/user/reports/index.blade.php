@extends('layouts.user')

@section('title', 'Reports')

@section('content')
    <form method="GET" action="{{ route('user.reports') }}" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label">From:</label>
            <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To:</label>
            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success">Search</button>
        </div>
    </form>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card np-card h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="text-uppercase text-success small fw-semibold">Total Payin</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($totals['payinAmount'], 2) }}</div>
                    <div class="text-muted small">{{ $totals['payinCount'] }} txns</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card np-card h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-uppercase text-primary small fw-semibold">Total Payout</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($totals['payoutAmount'], 2) }}</div>
                    <div class="text-muted small">{{ $totals['payoutCount'] }} txns</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card np-card h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-uppercase text-warning small fw-semibold">Total Fee (Payin + Payout)</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($totals['payinFee'] + $totals['payoutFee'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card np-card h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="text-uppercase text-info small fw-semibold">Payout Success / Failed</div>
                    <div class="fs-5 fw-bold">
                        <span class="text-success">{{ $totals['payoutSuccess'] }}</span> /
                        <span class="text-danger">{{ $totals['payoutFailed'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card np-card">
        <div class="card-header">
            <h6 class="mb-0">Daily Summary Report</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="reportTable" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Payin Amount</th>
                            <th>Payin Txns</th>
                            <th>Payin Fee</th>
                            <th>Payout Amount</th>
                            <th>Payout Txns</th>
                            <th>Payout Fee</th>
                            <th>Payout Success</th>
                            <th>Payout Failed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $date => $day)
                            <tr>
                                <td>{{ $date }}</td>
                                <td>&#x20B9; {{ number_format($day['payinAmount'], 2) }}</td>
                                <td>{{ $day['payinCount'] }}</td>
                                <td>&#x20B9; {{ number_format($day['payinFee'], 2) }}</td>
                                <td>&#x20B9; {{ number_format($day['payoutAmount'], 2) }}</td>
                                <td>{{ $day['payoutCount'] }}</td>
                                <td>&#x20B9; {{ number_format($day['payoutFee'], 2) }}</td>
                                <td class="text-success">{{ $day['payoutSuccess'] }}</td>
                                <td class="text-danger">{{ $day['payoutFailed'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#reportTable').DataTable({
            lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'All']],
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'pdf'],
            order: [],
            language: { emptyTable: 'No activity in this date range' },
        });
    </script>
@endpush
