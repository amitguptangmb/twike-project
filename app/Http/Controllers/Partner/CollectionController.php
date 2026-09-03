<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * Port of partner/collection.aspx(.cs) - sidebar item "Pay In".
 *
 * CHANGED per USER_PANEL_ANALYSIS.md: the source hardcodes fromdate/todate
 * to today with no filter UI at all, but the live screenshots show a
 * working From/To date filter plus 6 KPI tiles (TOTAL GTV, TOTAL NO OF
 * SUCCESS, AMOUNT REFUNDED, CHARGEBACK AMOUNT, SETTLEMENT AMOUNT, FEE & TAX)
 * that don't exist in that source either. `USP_TRANS_PAY_IN_AGENT` already
 * takes real `@fromdate`/`@todate` parameters (the source just never wired
 * a UI to them) - so the date filter itself is a genuine proc feature, just
 * one the original page never exposed. Defaults to today when both are
 * blank, same convention as every other filtered page in this app.
 *
 * The KPI tiles are computed from the same fetched rows, with two real
 * gaps worth knowing about rather than silently papering over:
 * - AMOUNT REFUNDED and CHARGEBACK AMOUNT are always ₹0 here. The proc's
 *   WHERE clause only returns rows with `STATUS IN ('paid','Success',
 *   'processing','SUCCESS')` and `ModOfPayment IN ('Settlement','collection')`
 *   - no refund or chargeback rows ever reach this page's data at all, so
 *   there's nothing to sum. A real value for either tile would need a
 *   different proc/query this page doesn't have.
 * - TOTAL NO OF SUCCESS's "success ratio" is shown as 100% whenever there
 *   are any rows, because every row this proc returns is already a
 *   success by definition (see above) - there's no failed-transaction
 *   count in this data set to compute a real ratio against.
 */
class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = $request->session()->get('partner.id');

        $fromDate = (string) $request->query('from_date', '');
        $toDate = (string) $request->query('to_date', '');
        if ($fromDate === '' && $toDate === '') {
            $fromDate = $toDate = now()->format('Y-m-d');
        }

        $rows = StoredProcedure::call('USP_TRANS_PAY_IN_AGENT', [
            '@CREATED_BY' => $partnerId,
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
            '@txtTrans' => '0',
        ]);

        $totalGtv = 0.0;
        $settlementAmount = 0.0;
        $feeAndTax = 0.0;
        foreach ($rows as $row) {
            $totalGtv += (float) ($row->CR ?? 0) + (float) ($row->Settlement ?? 0);
            $settlementAmount += (float) ($row->Settlement ?? 0);
            $feeAndTax += (float) ($row->Fee ?? 0);
        }
        $successCount = count($rows);
        $successRatio = $successCount > 0 ? 100 : 0;

        return view('partner.collection.index', [
            'rows' => $rows,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalGtv' => $totalGtv,
            'successCount' => $successCount,
            'successRatio' => $successRatio,
            'amountRefunded' => 0,
            'chargebackAmount' => 0,
            'settlementAmount' => $settlementAmount,
            'feeAndTax' => $feeAndTax,
        ]);
    }
}
