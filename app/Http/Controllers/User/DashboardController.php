<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The user-facing "Dashboard" shown in the User-panel screenshots (PAY
 * IN / PAYOUT / CHARGE BACK / HOLDING AMT. tiles, a From/To filter, and
 * Payin Revenue / Payin Volume / Payout Volume cards).
 *
 * IMPORTANT - unlike almost every other controller in this codebase, there
 * is NO ASP.NET source for this page anywhere in the uploaded project (see
 * MIGRATION_NOTES.md's "User panel" section / USER_PANEL_ANALYSIS.md for
 * the full audit). `user/userlist.aspx` - the page that WAS the real
 * landing page and IS in the source - is a plain user list, now moved to
 * `UserListController`/`user.users`. This controller is a reconstruction
 * built from the screenshots plus the already-converted, already-proven
 * procs the *admin* Dashboard uses (`Admin\DashboardController`):
 *
 * - `Admin_Dashbaord(@fromdate, @todate, @UserID)` and
 *   `Admin_Dashbaord_payout(@fromdate, @todate, @UserID)` are confirmed (by
 *   reading the proc body, not guessed) to scope correctly to a single user
 *   when `@UserID` is non-zero: `(v_userID=0 AND u.UserId=u.UserId) OR
 *   (v_userID<>0 AND u.UserId=v_userID)`. The admin dashboard already
 *   exercises this exact code path whenever a specific user is picked from
 *   its dropdown - passing this user's own `session('user.id')` is
 *   the same call shape, not a new one.
 * - `USP_GROUP_Payin_Revenue(@CREATED_BY, @fromdate, @todate)` is the exact
 *   proc already powering the admin dashboard's Payin Revenue / Payin
 *   Volume / Payout Volume cards - same field names as this page's
 *   screenshot (Total Volume, Total Fees, Total Tax, Success Ratio /
 *   Total Transactions, Success, Failed, Pending, Cancelled).
 *
 * Two things that are NOT verified against real data, because no source or
 * proc directly says so - both use the same "default to 0 / don't blow up"
 * pattern as the rest of this file if the assumption is wrong:
 * - The CHARGE BACK tile assumes `Admin_Dashbaord`'s result can include a
 *   `ModOfPayment='Chargeback'` row (that literal value is used elsewhere
 *   in this codebase - SettlementList's admin proc filters on it - but I
 *   have not confirmed MONEY_TRANSFER_PAYIN actually stores chargebacks
 *   with `STATUS IN ('processed','SUCCESS')`, which is what this proc
 *   requires to return anything for that ModOfPayment at all).
 * - HOLDING AMT reads `UserMaster.Hold_Amt` directly (a plain column, not
 *   business logic) for the logged-in user - no proc surfaces it and
 *   this project's convention is "business logic stays in procs," but a
 *   single-column display read isn't business logic to reimplement.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');

        $fromDate = (string) $request->query('from_date', '');
        $toDate = (string) $request->query('to_date', '');
        if ($fromDate === '' && $toDate === '') {
            $fromDate = $toDate = now()->format('Y-m-d');
        }

        // --- Top tiles: PAY IN / PAYOUT / CHARGE BACK / HOLDING AMT. ---
        $payIn = '0';
        $chargeBack = '0';
        $dashRows = StoredProcedure::call('Admin_Dashbaord', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
            '@UserID' => $userId,
        ]);
        foreach ($dashRows as $row) {
            if ($row->ModOfPayment === 'Collection') {
                $payIn = $row->total;
            } elseif ($row->ModOfPayment === 'Chargeback') {
                $chargeBack = $row->total;
            }
        }

        $payOut = '0';
        $payoutRows = StoredProcedure::call('Admin_Dashbaord_payout', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
            '@UserID' => $userId,
        ]);
        $payoutTotal = 0.0;
        foreach ($payoutRows as $row) {
            if (in_array($row->ModOfPayment, ['IMPS', 'NEFT', 'UPI'], true)) {
                $payoutTotal += (float) $row->total;
            }
        }
        if ($payoutTotal !== 0.0) {
            $payOut = (string) $payoutTotal;
        }

        $holdingAmt = DB::table('UserMaster')->where('UserId', $userId)->value('Hold_Amt') ?? '0';

        // --- Payin Revenue / Payin Volume / Payout Volume cards ---
        $totalVolume = '0';
        $totalFees = '0';
        $totalTax = '0';
        $successRatio = '0';
        $totalTransactions = '0';
        $success = '0';
        $failed = '0';
        $cancelled = '0';
        $pending = '0';
        $payoutTotalTransactions = '0';
        $payoutSuccess = '0';
        $payoutFailed = '0';
        $payoutPending = '0';

        $revenue = StoredProcedure::callMultiRowset('USP_GROUP_Payin_Revenue', [
            '@CREATED_BY' => $userId,
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
        ]);

        $rev0 = $revenue[0][0] ?? null;
        $rev1 = $revenue[1][0] ?? null;
        $rev2 = $revenue[2][0] ?? null;

        if ($rev0) {
            $totalVolume = $rev0->total_transactions ?? '0';
            $totalFees = $rev0->total_fees ?? '0';
            $totalTax = $rev0->totaltax ?? '0';
            $successRatio = $rev0->success_percentage ?? '0';
        }
        if ($rev1) {
            $totalTransactions = $rev1->total_transactions ?? '0';
            $success = $rev1->success ?? '0';
            $failed = $rev1->failed ?? '0';
            $cancelled = $rev1->Cancelled ?? '0';
            $pending = $rev1->pending ?? '0';
        }
        if ($rev2) {
            $payoutTotalTransactions = $rev2->payout_total_transactions ?? '0';
            $payoutSuccess = $rev2->payout_success ?? '0';
            $payoutFailed = $rev2->payout_failed ?? '0';
            $payoutPending = $rev2->payout_pending ?? '0';
        }

        return view('user.dashboard.index', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'payIn' => $payIn,
            'payOut' => $payOut,
            'chargeBack' => $chargeBack,
            'holdingAmt' => $holdingAmt,
            'totalVolume' => $totalVolume,
            'totalFees' => $totalFees,
            'totalTax' => $totalTax,
            'successRatio' => $successRatio,
            'totalTransactions' => $totalTransactions,
            'success' => $success,
            'failed' => $failed,
            'cancelled' => $cancelled,
            'pending' => $pending,
            'payoutTotalTransactions' => $payoutTotalTransactions,
            'payoutSuccess' => $payoutSuccess,
            'payoutFailed' => $payoutFailed,
            'payoutPending' => $payoutPending,
        ]);
    }
}
