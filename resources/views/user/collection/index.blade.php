@extends('layouts.user')

@section('title', 'Collection')

@section('content')
    <form method="GET" action="{{ route('user.collection') }}" class="row g-2 align-items-end mb-3">
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
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-uppercase text-primary small fw-semibold">Total GTV</div>
                    <div class="fs-5 fw-bold">{{ number_format($totalGtv, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="text-uppercase text-success small fw-semibold">Total No Of Success</div>
                    <div class="fs-5 fw-bold">{{ $successCount }} | {{ $successRatio }}%</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="text-uppercase text-info small fw-semibold">Amount Refunded</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($amountRefunded, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-uppercase text-warning small fw-semibold">Chargeback Amount</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($chargebackAmount, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="text-uppercase text-success small fw-semibold">Settlement Amount</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($settlementAmount, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-uppercase text-warning small fw-semibold">Fee &amp; Tax</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($feeAndTax, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card np-card">
        <div class="card-header">
            <h6 class="mb-0">Report(Daywise)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="collectionTable" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>S No</th>
                            <th>Txn.Date</th>
                            <th>Transaction ID</th>
                            <th>Ref ID</th>
                            <th>UTR</th>
                            <th>CR</th>
                            <th>Settlement</th>
                            <th>Fee</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row->CREATED_ON }}</td>
                                <td>{{ $row->TRANSACTIONID }}</td>
                                <td>{{ $row->refno }}</td>
                                <td>{{ $row->UTR }}</td>
                                <td>{{ $row->CR }}</td>
                                <td>{{ $row->Settlement }}</td>
                                <td>{{ $row->Fee }}</td>
                                <td>{{ $row->Balance }}</td>
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
        $('#collectionTable').DataTable({
            lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'All']],
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'pdf'],
        });
    </script>
@endpush
