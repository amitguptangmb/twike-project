<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * Port of admin/Dashboard.aspx(.cs).
 *
 * Deliberately NOT ported (dead in the source, not just "skipped"):
 * - Button1-4 (PAYIN/PAYOUT on/off toggles): rendered Enabled="false" in the
 *   source markup with no code path that ever re-enables them (the block
 *   that would is commented out in Page_Load), and their target procedure
 *   OptStatus isn't in the SQL export anyway. Buttons are kept in the view,
 *   permanently disabled, for visual fidelity only.
 * - "Pipe Story" panel (user_pipe grid, BindSIDDetails(), OnRowDataBound()):
 *   the whole card is commented out in the .aspx markup. Its procedure
 *   (SIDDetails) also isn't in the SQL export.
 * - BindUserPipe() / userReportDeateWise (note the typo - distinct from the
 *   correctly-spelled userReportDateWise, which IS used): never called from
 *   anywhere. Its procedure also isn't in the export.
 * - BindReportBusiness() / busniessReportDeateWise: never called from
 *   anywhere either, despite the procedure existing.
 *
 * Everything else mirrors Page_Load's Bind*() call order exactly, including
 * the original's control flow quirk: if CollectionDashboardReport()'s
 * procedure call fails, the original's catch redirects to login and (via
 * Response.Redirect's thread-abort) never runs the remaining Bind*() calls
 * that follow it in Page_Load. That short-circuit is preserved below.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = (string) $request->query('from_date', '');
        $toDate = (string) $request->query('to_date', '');

        // Matches the original's exact (slightly odd) logic: only default
        // to today if BOTH are blank. If just one is filled in, the other
        // is left as an empty string, same as the source.
        if ($fromDate === '' && $toDate === '') {
            $fromDate = $toDate = now()->format('Y-m-d');
        }

        $userId = (int) $request->query('user_id', 0);

        $users = StoredProcedure::call('GetUserID');

        // --- BindDashbaordData(): Pay In card + Settlement Amount card ---
        $payIn = '0';
        $walletAmount = '0';
        $dashRows = StoredProcedure::call('Admin_Dashbaord', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
            '@UserID' => $userId,
        ]);
        foreach ($dashRows as $row) {
            // Case-sensitive on purpose - matches the original's C# ==
            // comparison, which only ever matched literal "Collection" /
            // "Settlement", not the lowercase "collection" the underlying
            // data actually uses. Preserved as-is, not "fixed".
            if ($row->ModOfPayment === 'Collection') {
                $payIn = $row->total;
            } elseif ($row->ModOfPayment === 'Settlement') {
                $walletAmount = $row->total;
            }
        }
        if ((float) $payIn === 0.0) {
            $payIn = '0';
        }
        if ((float) $walletAmount === 0.0) {
            $walletAmount = '0';
        }

        // --- CollectionDashboardReport(): Revenue / Volume / Payout cards ---
        // On failure the original redirects to login and runs nothing after
        // it in Page_Load - same here.
        try {
            $revenue = StoredProcedure::callMultiRowset('USP_GROUP_Payin_Revenue', [
                '@CREATED_BY' => $userId,
                '@fromdate' => $fromDate,
                '@todate' => $toDate,
            ]);

            $rev0 = $revenue[0][0] ?? null;
            $rev1 = $revenue[1][0] ?? null;
            $rev2 = $revenue[2][0] ?? null;

            $totalVolume = $rev0->total_transactions ?? '0';
            $totalFees = $rev0->total_fees ?? '0';
            $totalTax = $rev0->totaltax ?? '0';
            $successRatio = $rev0->success_percentage ?? '0';

            $totalTransactions = $rev1->total_transactions ?? '0';
            $success = $rev1->success ?? '0';
            $failed = $rev1->failed ?? '0';
            $cancelled = $rev1->Cancelled ?? '0';
            $pending = $rev1->pending ?? '0';

            $payoutTotalTransactions = $rev2->payout_total_transactions ?? '0';
            $payoutSuccess = $rev2->payout_success ?? '0';
            $payoutFailed = $rev2->payout_failed ?? '0';
            $payoutPending = $rev2->payout_pending ?? '0';
        } catch (\Throwable $e) {
            return redirect()->route('admin.login');
        }

        // --- BindDashbaordDatapayout(): Total Payout card ---
        // Note: this method's own $WalletAmount local never gets set to
        // anything but "0" in the source, so its "update wallet label"
        // branch never actually fires - lbl_walletAmount is only ever set
        // by BindDashbaordData() above. Preserved as-is.
        $payOut = '0';
        $payoutRows = StoredProcedure::call('Admin_Dashbaord_payout', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
            '@UserID' => $userId,
        ]);
        $impsTotal = 0.0;
        foreach ($payoutRows as $row) {
            if (in_array($row->ModOfPayment, ['IMPS', 'NEFT', 'UPI'], true)) {
                $impsTotal += (float) $row->total;
            }
        }
        if ($impsTotal !== 0.0) {
            $payOut = (string) $impsTotal;
        }

        // --- BindUserDateWise() ---
        $userDetails = StoredProcedure::call('userReportDateWise', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
        ]);

        // --- BindUserPayinPayoutReport() ---
        $payAndPayoutRows = StoredProcedure::call('userPayinPayoutReport');

        // --- BindUserPayoutReportDateWise() ---
        $payoutReportRows = StoredProcedure::call('userPayoutReportDateWise', [
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
        ]);

        return view('admin.dashboard.index', [
            'users' => $users,
            'userId' => $userId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'payIn' => $payIn,
            'payOut' => $payOut,
            'walletAmount' => $walletAmount,
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
            'userDetails' => $userDetails,
            'payAndPayoutRows' => $payAndPayoutRows,
            'payoutReportRows' => $payoutReportRows,
        ]);
    }
}
