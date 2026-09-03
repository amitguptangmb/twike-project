@extends('layouts.admin')

@section('title', 'Transaction List')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* 1. Force hide the built-in DataTables circular +/- icons completely */
        table.dataTable td.dtr-control::before,
        table.dataTable th.dtr-control::before {
            display: none !important;
        }

        /* 2. Style our direct text cell */
        table.dataTable td.dtr-control {
            cursor: pointer;
            color: #0d6efd;
            /* Blue color */
            font-size: 24px;
            font-weight: bold;
            text-align: center !important;
            vertical-align: middle !important;
            user-select: none;
        }

        /* 3. Make the + invisible on large screens where no columns are hidden */
        table.dataTable:not(.collapsed) td.dtr-control {
            color: transparent;
            cursor: default;
            pointer-events: none;
        }

        /* Inner expandable table styling */
        .child-table-wrapper {
            padding: 10px 0;
        }
    </style>
@endpush

@section('content')
    <div class="card np-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transactions') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Transaction Type</label>
                    <select name="type" class="form-select">
                        <option value="1" @selected($type == '1')>PayIN</option>
                        <option value="2" @selected($type == '2')>PayOut</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Users</label>
                    <select name="user_id" class="form-select">
                        <option value="0" @selected($userType == '0')>ALL</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->UserId }}" @selected($userType == $u->UserId)>{{ $u->UserId }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach (['0' => 'ALL', 'SUCCESS' => 'SUCCESS', 'PENDING' => 'PENDING', 'FAILED' => 'FAILED', 'REFUND' => 'REFUND'] as $val => $label)
                            <option value="{{ $val }}" @selected($status == $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i
                            class="ri-search-line align-middle me-1"></i>Search</button>
                    <a class="btn btn-primary" href="{{ route('admin.transactions.export', request()->query()) }}"><i
                            class="ri-download-2-line align-middle me-1"></i>Download</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card np-card">
        <div class="card-body">
            <table id="txnTable" class="table table-bordered dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        {{-- Header for the + / - control --}}
                        <th style="width: 25px; text-align: center;"></th>
                        <th>S No</th>
                        <th>Txn.Date</th>
                        <th>Transaction ID</th>
                        <th>Reference ID</th>
                        <th>Name</th>
                        <th>Bank</th>
                        {{-- <th>AccountNo</th> --}}
                        <th>IFSC</th>
                        <th>Amount</th>
                        <th>Tax</th>
                        <th>Tax Amount</th>
                        <th>Total Amount</th>
                        <th>UTR</th>
                        <th>Status</th>
                        {{-- <th>Portal</th> --}}
                        <th>Mode Of Payment</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $row)
                        <tr>
                            {{-- Inject the literal "+" character directly into the cell --}}
                            <td>+</td>

                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row->CREATED_ON }}</td>
                            <td>{{ $row->TRANSACTIONID }}</td>
                            <td>{{ $row->ReferenceId }}</td>
                            <td>{{ $row->HolderName }}</td>
                            <td>{{ $row->BankName }}</td>
                            {{-- <td>{{ $row->AccountNo }}</td> --}}
                            <td>{{ $row->IFSC }}</td>
                            <td>{{ $row->AMOUNT }}</td>
                            <td>{{ $row->TAX }}</td>
                            <td>{{ $row->TAX_AMOUNT }}</td>
                            <td>{{ $row->TOTAL_AMOUNT }}</td>
                            <td>{{ $row->UTR }}</td>
                            <td
                                class="{{ $row->STATUS === 'SUCCESS' ? 'text-success' : ($row->STATUS === 'FAILED' ? 'text-danger' : '') }}">
                                {{ $row->STATUS }}</td>
                            {{-- <td>{{ $row->servicename }}</td> --}}
                            <td>{{ $row->ModOfPayment }}</td>
                            <td>{{ $row->CREATED_BY }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#txnTable').DataTable({
                pageLength: 25,
                responsive: {
                    details: {
                        type: 'column',
                        target: 0,
                        renderer: function(api, rowIdx, columns) {
                            var thead = '<thead class="table-light"><tr>';
                            var tbody = '<tbody><tr>';
                            var hasHidden = false;

                            // Build the horizontal row layout for hidden data
                            $.map(columns, function(col, i) {
                                if (col.hidden) {
                                    thead +=
                                        '<th class="text-uppercase text-nowrap" style="font-size:12px; padding:10px;">' +
                                        col.title + '</th>';
                                    tbody += '<td style="padding:10px;" class="text-nowrap">' +
                                        col.data + '</td>';
                                    hasHidden = true;
                                }
                            });

                            thead += '</tr></thead>';
                            tbody += '</tr></tbody>';

                            return hasHidden ?
                                $('<div class="child-table-wrapper m-0"><div class="table-responsive"><table class="table table-bordered mb-0">' +
                                    thead + tbody + '</table></div></div>') :
                                false;
                        }
                    }
                },
                columnDefs: [{
                    className: 'dtr-control',
                    orderable: false,
                    targets: 0 // Targets the first column
                }],
                order: [
                    [1, 'asc']
                ]
            });

            // Dynamically flip + to - when row expands/collapses via JavaScript
            table.on('responsive-display', function(e, datatable, row, showHide, update) {
                $(row.node()).find('td.dtr-control').text(showHide ? '-' : '+');
            });
        });
    </script>
@endpush
