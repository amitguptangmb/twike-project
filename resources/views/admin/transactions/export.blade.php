<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <table border="1">
        <thead>
            <tr>
                <th>S No</th><th>Txn.Date</th><th>Transaction ID</th><th>Reference ID</th>
                <th>Name</th><th>Bank</th><th>AccountNo</th><th>IFSC</th><th>Amount</th>
                <th>Tax</th><th>Tax Amount</th><th>Total Amount</th><th>UTR</th><th>Status</th>
                <th>Portal</th><th>Mode Of Payment</th><th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->CREATED_ON }}</td>
                    <td>{{ $row->TRANSACTIONID }}</td>
                    <td>{{ $row->ReferenceId }}</td>
                    <td>{{ $row->HolderName }}</td>
                    <td>{{ $row->BankName }}</td>
                    <td>{{ $row->AccountNo }}</td>
                    <td>{{ $row->IFSC }}</td>
                    <td>{{ $row->AMOUNT }}</td>
                    <td>{{ $row->TAX }}</td>
                    <td>{{ $row->TAX_AMOUNT }}</td>
                    <td>{{ $row->TOTAL_AMOUNT }}</td>
                    <td>{{ $row->UTR }}</td>
                    <td>{{ $row->STATUS }}</td>
                    <td>{{ $row->servicename }}</td>
                    <td>{{ $row->ModOfPayment }}</td>
                    <td>{{ $row->CREATED_BY }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
