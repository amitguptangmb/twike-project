<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $users = StoredProcedure::call('GetUserID');

        $fromDate = $request->query('from_date', '');
        $toDate = $request->query('to_date', '');
        if ($fromDate === '' && $toDate === '') {
            $fromDate = $toDate = now()->format('Y-m-d');
        }

        // dp_tr_type: 1 = PayIN, 2 = PayOut (selected by default in the original).
        $type = $request->query('type', '2');
        $userType = $request->query('user_id', '0');
        $status = $request->query('status', '0');

        $rows = StoredProcedure::call('USP_TRANSACTION_LIST_ForAdmin', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
            '@type' => $type,
            '@userType' => $userType,
            '@status' => $status,
            '@AgentID' => 0,
        ]);

        return view('admin.transactions.index', [
            'rows' => $rows,
            'users' => $users,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'type' => $type,
            'userType' => $userType,
            'status' => $status,
        ]);
    }

    /**
     * Port of ExportGridToExcel() - same crude "render an HTML table with an
     * .xls content-type" trick the original used, same filename pattern
     * ("CollectionReports_{DateTime.Now}.xls" in the source).
     */
    public function export(Request $request)
    {
        $fromDate = $request->query('from_date', '');
        $toDate = $request->query('to_date', '');
        if ($fromDate === '' && $toDate === '') {
            $fromDate = $toDate = now()->format('Y-m-d');
        }

        $type = $request->query('type', '2');
        $userType = $request->query('user_id', '0');
        $status = $request->query('status', '0');

        $rows = StoredProcedure::call('USP_TRANSACTION_LIST_ForAdmin', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
            '@type' => $type,
            '@userType' => $userType,
            '@status' => $status,
            '@AgentID' => 0,
        ]);

        $filename = 'CollectionReports_'.now()->format('Y-m-d_His').'.xls';

        $html = view('admin.transactions.export', ['rows' => $rows])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment;filename='.$filename,
            'Cache-Control' => 'no-cache',
        ]);
    }
}
