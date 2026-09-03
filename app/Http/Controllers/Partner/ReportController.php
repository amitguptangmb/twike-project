<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * Partner "Reports" page - NEW page, no ASP.NET source and no screenshot
 * exist for it anywhere in the upload (a full listing of the partner/
 * source folder was checked: ForgotPassword, TransactionList, agentMaster
 * .Master, changepassword, collection, logout, payOut, userlist only - no
 * Report(s).aspx anywhere). Built per explicit user spec: a daily summary
 * report merging Pay In and Pay Out activity, one row per day, for a chosen
 * date range.
 *
 * Data comes from the same two already-converted procs the Pay In / Pay Out
 * pages already use - no new proc, no invented business logic on top of
 * what they return:
 * - Pay In side: USP_TRANS_PAY_IN_AGENT(@CREATED_BY, @fromdate, @todate,
 *   @txtTrans) - has real date params, so the range is applied proc-side,
 *   same call CollectionController makes.
 * - Pay Out side: USP_USER_PAYOUT(@trFlag=2, @txtTrans='0', @txtUTR='0',
 *   @userID) - same proc TransactionController uses, and (per that
 *   controller's docblock) it has NO date parameters at all. So exactly
 *   like TransactionController, this pulls every payout row for the
 *   partner and filters by date range in PHP. Same reconstruction, same
 *   caveat: if a proc with real payout date filtering turns up later, this
 *   should switch to it instead of the PHP-side filter.
 *
 * Grouping by day and summing/counting per day is plain PHP over rows
 * those two procs already return - presentation of existing data, not new
 * business logic.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = (int) $request->session()->get('partner.id');

        $fromDate = (string) $request->query('from_date', '');
        $toDate = (string) $request->query('to_date', '');
        if ($fromDate === '' && $toDate === '') {
            $toDate = now()->format('Y-m-d');
            $fromDate = now()->subDays(6)->format('Y-m-d');
        }

        $payinRows = StoredProcedure::call('USP_TRANS_PAY_IN_AGENT', [
            '@CREATED_BY' => $partnerId,
            '@fromdate' => $fromDate,
            '@todate' => $toDate,
            '@txtTrans' => '0',
        ]);

        $payoutRows = StoredProcedure::call('USP_USER_PAYOUT', [
            '@trFlag' => 2,
            '@txtTrans' => '0',
            '@txtUTR' => '0',
            '@userID' => $partnerId,
        ]);
        $payoutRows = array_values(array_filter($payoutRows, function ($r) use ($fromDate, $toDate) {
            $d = substr((string) $r->CREATED_ON, 0, 10);

            return $d >= $fromDate && $d <= $toDate;
        }));

        $days = [];

        foreach ($payinRows as $row) {
            $date = substr((string) $row->CREATED_ON, 0, 10);
            $days[$date] ??= self::emptyDay();
            $days[$date]['payinAmount'] += (float) ($row->CR ?? 0) + (float) ($row->Settlement ?? 0);
            $days[$date]['payinFee'] += (float) ($row->Fee ?? 0);
            $days[$date]['payinCount']++;
        }

        foreach ($payoutRows as $row) {
            $date = substr((string) $row->CREATED_ON, 0, 10);
            $days[$date] ??= self::emptyDay();
            $days[$date]['payoutAmount'] += (float) ($row->DR ?? 0) + (float) ($row->Recharge ?? 0);
            $days[$date]['payoutFee'] += (float) ($row->Fee ?? 0);
            $days[$date]['payoutCount']++;
            if (strcasecmp((string) $row->Status, 'SUCCESS') === 0) {
                $days[$date]['payoutSuccess']++;
            } elseif (strcasecmp((string) $row->Status, 'FAILED') === 0) {
                $days[$date]['payoutFailed']++;
            }
        }

        krsort($days);

        // Totals row - summed in PHP from the per-day rows above, not a
        // separate query.
        $totals = self::emptyDay();
        foreach ($days as $day) {
            foreach ($totals as $key => $value) {
                $totals[$key] = $value + $day[$key];
            }
        }

        return view('partner.reports.index', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'days' => $days,
            'totals' => $totals,
        ]);
    }

    /** @return array<string, int|float> */
    private static function emptyDay(): array
    {
        return [
            'payinAmount' => 0.0, 'payinFee' => 0.0, 'payinCount' => 0,
            'payoutAmount' => 0.0, 'payoutFee' => 0.0, 'payoutCount' => 0,
            'payoutSuccess' => 0, 'payoutFailed' => 0,
        ];
    }
}
