<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <table border="1">
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
                    <td>{{ $row->Status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
