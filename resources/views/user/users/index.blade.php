@extends('layouts.user')

@section('title', 'User List')

@section('content')
    <div class="card np-card">
        <div class="card-body">
            <table id="userListTable" class="table table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Sl. No.</th>
                        <th>UserId</th>
                        <th>Name</th>
                        <th>Payout Amount</th>
                        <th>Collection Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row->UserId }}</td>
                            <td>{{ $row->Name }}</td>
                            <td>{{ $row->Available_Amount }}</td>
                            <td>{{ $row->Collection_Amount }}</td>
                            <td>
                                @if (($row->Status ?? '') == 'Active' || ($row->Status ?? '') == '1')
                                    <button class="btn btn-sm btn-success">Active</button>
                                @else
                                    <button class="btn btn-sm btn-danger">Deactive</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>$('#userListTable').DataTable();</script>
@endpush
