<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * Port of admin/SettlementList.aspx(.cs).
 *
 * Unlike TransactionList.aspx, this page has no partner-side equivalent to
 * copy from (confirmed: no SettlementList.aspx anywhere under the partner
 * folder, and USP_REQUEST_SETTLEMENT_LIST - the only other candidate proc -
 * is scoped to a single partner/@CREATED_BY and is never called from any
 * ASPX page in the source, so it's not a template either).
 *
 * bindSettlementDetails() logic ported as-is:
 * - fromdate/todate default to today ONLY when BOTH are blank
 *   (`if (fromdate != "" || todate != "") {} else { default both }` -
 *   equivalent to "default only if fromdate=='' && todate==''").
 * - Calls USP_REQUEST_SETTLEMENT_LIST_ForAdmin(@fromdate, @todate) - no
 *   other params (the commented-out @status param in the source is dead).
 * - GridView only binds `if (dt.Rows.Count > 0)`; with an empty result set
 *   this port simply renders zero rows, which is the same visible outcome.
 *
 * Not ported (dead in the source):
 * - The commented-out settlement-email/HTML-template block
 *   (ReadTemplates, StlmntAdmin_Details.html / StlmntAdmin_Header.html).
 *
 * Known pre-existing bugs in USP_REQUEST_SETTLEMENT_LIST_ForAdmin (already
 * converted in twike_mysql_procs.sql, NOT touched here per "convert as-is"):
 * - WHERE filters ModOfPayment='Chargeback' while the SELECT hardcodes the
 *   output column 'Settlement' as ModOfPayment - looks like a copy/paste
 *   artifact, unconfirmed against the original T-SQL.
 * - The STATUS CASE branch uses T-SQL `+` string concatenation instead of
 *   MySQL CONCAT() - currently harmless here since this page never selects
 *   a STATUS column (GridView has no STATUS BoundField).
 * Left as-is since "convert same, no changes" - flagging in migration
 * notes rather than fixing silently.
 *
 * Export: the source uses a purely client-side DataTables Buttons
 * extension (csv/excel/pdf, dom: 'Blfrtip') with NO server-side export
 * action at all - unlike TransactionList's server-rendered .xls download.
 * Ported the same way: no export() method/route here, just the Buttons
 * extension wired into the DataTable in the view.
 */
class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->query('from_date', '');
        $toDate = $request->query('to_date', '');
        if ($fromDate === '' && $toDate === '') {
            $fromDate = $toDate = now()->format('Y-m-d');
        }

        $rows = StoredProcedure::call('USP_REQUEST_SETTLEMENT_LIST_ForAdmin', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
        ]);

        return view('admin.settlements.index', [
            'rows' => $rows,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }
}
