<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * The user-facing "Settlement" page shown in the User-panel screenshots -
 * a single Report(Daywise) DataTable (S No, Request Date, TRANSACTION ID,
 * AMOUNT, Reference Id, STATUS, ModOfPayment) with client-side CSV/Excel/PDF
 * export.
 *
 * No ASP.NET source exists for this page (see USER_PANEL_ANALYSIS.md), but
 * the backing proc is an exact column-for-column match:
 * `USP_REQUEST_SETTLEMENT_LIST(@CREATED_BY)` - already converted, already
 * user-scoped, and its SELECT list (TRANSACTIONID, AMOUNT, STATUS,
 * CREATED_ON->Requestdate, PortalName->servicename, ModOfPayment,
 * user_order_id->ReferenceId) lines up with every column this screenshot
 * shows. Capped at 50 rows inside the proc itself - no pagination params to
 * pass through.
 */
class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $rows = StoredProcedure::call('USP_REQUEST_SETTLEMENT_LIST', [
            '@CREATED_BY' => $request->session()->get('user.id'),
        ]);

        return view('user.settlement.index', ['rows' => $rows]);
    }
}
