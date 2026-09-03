@extends('layouts.partner')

@section('title', 'Pay Out')

@section('content')
    <form method="GET" action="{{ route('partner.transactions') }}" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label">From:</label>
            <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To:</label>
            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status:</label>
            <select name="status" class="form-select">
                @foreach (['0' => 'ALL', 'SUCCESS' => 'SUCCESS', 'FAILED' => 'FAILED', 'Refund' => 'Refund'] as $val => $label)
                    <option value="{{ $val }}" @selected($status == $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-success">Search</button>
            <a class="btn btn-primary" href="{{ route('partner.transactions.export', request()->query()) }}">Download</a>
        </div>
    </form>

    <div class="row">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-uppercase text-primary small fw-semibold">Total Amount</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($totalAmount, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="text-uppercase text-info small fw-semibold">Number of Payout Transaction</div>
                    <div class="fs-5 fw-bold">{{ $transactionCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="text-uppercase text-success small fw-semibold">Total Success Transaction</div>
                    <div class="fs-5 fw-bold">{{ $successCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="text-uppercase text-danger small fw-semibold">Total Failed Transaction</div>
                    <div class="fs-5 fw-bold">{{ $failedCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="card np-card h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-uppercase text-warning small fw-semibold">Charge Amount Or Fee Amount</div>
                    <div class="fs-5 fw-bold">&#x20B9; {{ number_format($feeAmount, 2) }}</div>
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
                <table id="txnTable" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>S No</th><th>Txn.Date</th><th>Txn Updation Date</th><th>REF ID</th>
                            <th>USER ORDER ID</th><th>UTR</th><th>DR</th><th>Recharge</th>
                            <th>FEE</th><th>Balance</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row->CREATED_ON }}</td>
                                <td>{{ $row->UPDATED_ON }}</td>
                                <td>{{ $row->refno }}</td>
                                <td>{{ $row->TRANSACTIONID }}</td>
                                <td>{{ $row->UTR }}</td>
                                <td>{{ $row->DR }}</td>
                                <td>{{ $row->Recharge }}</td>
                                <td>{{ $row->Fee }}</td>
                                <td>{{ $row->Balance }}</td>
                                <td class="{{ $row->Status === 'SUCCESS' ? 'text-success' : ($row->Status === 'FAILED' ? 'text-danger' : '') }}">{{ $row->Status }}</td>
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
        $('#txnTable').DataTable({
            lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'pdf'],
        });
    </script>
@endpush
