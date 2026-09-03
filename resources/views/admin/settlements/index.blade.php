@extends('layouts.admin')

@section('title', 'Settlement List')

@section('content')
    <div class="card np-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.settlements') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success"><i class="ri-search-line align-middle me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card np-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="settlementTable" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>S No</th>
                            <th>Request Date</th>
                            <th>TRANSACTION ID</th>
                            <th>AccountNo</th>
                            <th>IFSC</th>
                            <th>Holder Name</th>
                            <th>AMOUNT</th>
                            <th>User ID</th>
                            <th>Reference Id</th>
                            <th>ModOfPayment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row->Requestdate }}</td>
                                <td>{{ $row->TRANSACTIONID }}</td>
                                <td>{{ $row->AccountNo }}</td>
                                <td>{{ $row->IFSC }}</td>
                                <td>{{ $row->HolderName }}</td>
                                <td>{{ $row->AMOUNT }}</td>
                                <td>{{ $row->UserID }}</td>
                                <td>{{ $row->ReferenceId }}</td>
                                <td>{{ $row->ModOfPayment }}</td>
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
        $('#settlementTable').DataTable({
            lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],
            dom: 'Blfrtip',
            buttons: ['csv', 'excel', 'pdf'],
        });
    </script>
@endpush
