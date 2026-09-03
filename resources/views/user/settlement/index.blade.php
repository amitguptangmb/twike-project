@extends('layouts.user')

@section('title', 'Settlement')

@section('content')
    <div class="card np-card">
        <div class="card-header">
            <h6 class="mb-0">Report(Daywise)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="settlementTable" class="table table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>S No</th>
                            <th>Request Date</th>
                            <th>TRANSACTION ID</th>
                            <th>AMOUNT</th>
                            <th>Reference Id</th>
                            <th>STATUS</th>
                            <th>ModOfPayment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row->Requestdate }}</td>
                                <td>{{ $row->TRANSACTIONID }}</td>
                                <td>{{ $row->AMOUNT }}</td>
                                <td>{{ $row->ReferenceId }}</td>
                                <td class="{{ $row->STATUS === 'SUCCESS' ? 'text-success' : ($row->STATUS === 'FAILED' ? 'text-danger' : '') }}">{{ $row->STATUS }}</td>
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
