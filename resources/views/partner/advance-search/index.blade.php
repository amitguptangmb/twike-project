@extends('layouts.partner')

@section('title', 'Advance Search')

@section('content')
    <form method="POST" action="{{ route('partner.advance-search.search') }}" autocomplete="off">
        @csrf

        <div class="card np-card mb-4">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Transaction Id / UTR No :-</label>
                        <input type="text" name="trans" class="form-control" placeholder="Transaction ID or Marchant Order ID" value="{{ $trans }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">OR :</label>
                        <input type="text" name="utr" class="form-control" placeholder="UTR No" value="{{ $utr }}">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tr_flag" id="trFlagPayin" value="1" {{ $trFlag == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="trFlagPayin">Payin</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tr_flag" id="trFlagPayout" value="2" {{ $trFlag == '2' ? 'checked' : '' }}>
                            <label class="form-check-label" for="trFlagPayout">Payout</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success"><i class="ri-search-line align-middle me-1"></i>Search</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card np-card">
            <div class="card-header">
                <h6 class="mb-0">Advance Search</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S No</th>
                                <th>Txn.Date</th>
                                <th>Txn Updation Date</th>
                                <th>REF ID</th>
                                <th>USER ORDER ID</th>
                                <th>UTR</th>
                                <th>DR</th>
                                <th>Recharge</th>
                                <th>FEE</th>
                                <th>Balance</th>
                                <th>Status</th>
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
                                    <td>{{ $row->Status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
@endsection
