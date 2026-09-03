<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');

        $fromDate = (string) $request->query('from_date', '');
        $toDate   = (string) $request->query('to_date', '');
        $status   = (string) $request->query('status', '0');

        if ($fromDate === '' && $toDate === '') {
            $fromDate = now()->format('Y-m-d');
            $toDate   = now()->format('Y-m-d');
        }

        $cardRows = StoredProcedure::call('USP_Payout_Collection', [
            '@CREATED_BY' => $userId,
            '@fromdate'   => $fromDate,
            '@todate'     => $toDate,
        ]);

        $card = [
            'totalAmount'      => 0,
            'transactionCount' => 0,
            'successCount'     => 0,
            'failedCount'      => 0,
            'feeAmount'        => 0,
        ];

        if (!empty($cardRows)) {
            $cardRow = $cardRows[0];

            $card = [
                'totalAmount'      => $cardRow->payout_total_amount ?? 0,
                'transactionCount' => $cardRow->payout_total_transactions ?? 0,
                'successCount'     => $cardRow->payout_success ?? 0,
                'failedCount'      => $cardRow->payout_failed ?? 0,
                'feeAmount'        => $cardRow->total_fees ?? 0,
            ];
        }

        $rows = StoredProcedure::call('USP_TRANS_LIST_OUT', [
            '@CREATED_BY' => $userId,
            '@fromdate'   => $fromDate,
            '@todate'     => $toDate,
            '@txtTrans'   => '0',
            '@status'     => $status,
        ]);

        return view('user.transactions.index', [
            'rows'             => $rows,
            'fromDate'         => $fromDate,
            'toDate'           => $toDate,
            'status'           => $status,
            'totalAmount'      => $card['totalAmount'],
            'transactionCount' => $card['transactionCount'],
            'successCount'     => $card['successCount'],
            'failedCount'      => $card['failedCount'],
            'feeAmount'        => $card['feeAmount'],
        ]);
    }

    public function export(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');

        $fromDate = (string) $request->query('from_date', '');
        $toDate   = (string) $request->query('to_date', '');
        $status   = (string) $request->query('status', '0');

        if ($fromDate === '' && $toDate === '') {
            $fromDate = now()->format('Y-m-d');
            $toDate   = now()->format('Y-m-d');
        }

        $rows = StoredProcedure::call('USP_TRANS_LIST_OUT', [
            '@CREATED_BY' => $userId,
            '@fromdate'   => $fromDate,
            '@todate'     => $toDate,
            '@txtTrans'   => '0',
            '@status'     => $status,
        ]);

        $filename = 'Payout_Reports_' . now()->format('Y-m-d_His') . '.xls';

        $html = view('user.transactions.export', [
            'rows' => $rows,
        ])->render();

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment;filename=' . $filename,
            'Cache-Control'       => 'no-cache',
        ]);
    }
}
