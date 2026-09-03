<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Port of admin/AdvanceSearch.aspx(.cs).
 *
 * ASP.NET WebForms posts the ENTIRE page (search fields, radio, and every
 * row's "Sent Callback" button) back on every click - there's no separate
 * page for the callback action, it's just another control in the same
 * <form runat="server">. This port keeps that shape: one <form> in the
 * view posts everything to store() on every submit, and store() looks at
 * which button was actually pressed (search vs. one row's "Sent Callback")
 * the same way the code-behind's two separate event handlers did.
 *
 * Both actions call the same proc, USP_USER_PAYOUT_ADMIN(@trFlag, @txtTrans,
 * @txtUTR) - already converted in twike_mysql_procs.sql, not touched here.
 *
 * Ported as-is, including two source quirks worth knowing about (not fixed,
 * per "convert same, no changes" - see MIGRATION_NOTES.md for the full
 * writeup):
 * - txtTrans/txtUTR are sent as the literal string "0" (not "") when the
 *   textbox is blank. Since the proc's branch is `IF (v_txtTrans != '')`,
 *   a blank txtTrans is never actually empty by the time it reaches the
 *   proc - so the UTR-only search branch is effectively unreachable from
 *   this page. Filling in only UTR and leaving Transaction ID blank
 *   searches `TRANSACTIONID='0' OR user_order_id='0'` (no rows) instead
 *   of searching by UTR.
 * - The "Sent Callback" button's payload is double JSON-encoded before
 *   being base64'd (SerializeObject() called twice - once on the payload
 *   object, once again on the resulting JSON string). The base64 blob a
 *   receiving merchant endpoint gets therefore decodes to a JSON STRING
 *   LITERAL (quotes escaped) rather than a JSON object. Reproduced
 *   exactly via json_encode(json_encode($payload)).
 */
class AdvanceSearchController extends Controller
{
    public function index()
    {
        // Matches the source: Page_Load never runs a search on its own: the
        // grid is only populated after Search (or a row's Sent Callback,
        // which re-renders whatever was already on screen) is clicked.
        return view('admin.advance-search.index', [
            'rows' => [],
            'trFlag' => '1',
            'trans' => '',
            'utr' => '',
            'callbackResult' => null,
        ]);
    }

    public function store(Request $request)
    {
        $trFlag = $request->input('tr_flag', '1');
        $trans = (string) $request->input('trans', '');
        $utr = (string) $request->input('utr', '');

        $callbackResult = null;

        if ($request->filled('callback_refno')) {
            $callbackResult = $this->sendCallback($request->input('callback_refno'), $trFlag);
        }

        // Same query the source's GridView shows after ANY postback on this
        // page (Search or Sent Callback) - the code-behind's GridView keeps
        // its last-bound data via ViewState across a Sent Callback postback
        // rather than clearing it; re-running the same search with the same
        // criteria that are still sitting in the form reproduces that.
        $rows = $this->runSearch($trFlag, $trans, $utr);

        return view('admin.advance-search.index', [
            'rows' => $rows,
            'trFlag' => $trFlag,
            'trans' => $trans,
            'utr' => $utr,
            'callbackResult' => $callbackResult,
        ]);
    }

    /**
     * Port of PayoutReport2(). Blank fields become the literal string "0",
     * exactly as `txtTrans.Text.ToString() == "" ? "0" : txtTrans.Text.ToString()`
     * did - see the class docblock for what that does to the UTR branch.
     */
    private function runSearch(string $trFlag, string $trans, string $utr): array
    {
        return StoredProcedure::call('USP_USER_PAYOUT_ADMIN', [
            '@trFlag' => $trFlag,
            '@txtTrans' => $trans === '' ? '0' : $trans,
            '@txtUTR' => $utr === '' ? '0' : $utr,
        ]);
    }

    /**
     * Port of btn_sent_callback_click(). $refno is the row's REF ID column
     * (`row.Cells[3].Text` in the source - the 4th GridView column, which is
     * `refno`, itself aliased from the proc's `m.TRANSACTIONID`). $trFlag is
     * read from the form's CURRENT radio selection at submit time, matching
     * `rb_tr.SelectedValue` being read live from the postback rather than
     * from whatever radio value was in effect during the original search.
     *
     * @return array{ok: bool, message: string}
     */
    private function sendCallback(string $refno, string $trFlag): array
    {
        Log::info($refno);

        $rows = StoredProcedure::call('USP_USER_PAYOUT_ADMIN', [
            '@trFlag' => $trFlag,
            '@txtTrans' => $refno,
            '@txtUTR' => '',
        ]);

        if (empty($rows)) {
            return ['ok' => false, 'message' => "No transaction found for REF ID {$refno}."];
        }

        $row = $rows[0];
        $status = $row->Status ?? '';
        $callbackUrl = $row->Callback_URL ?? '0';

        if ($status === 'SUCCESS') {
            $success = true;
            $responseCode = '200';
            $state = 'SUCCESS';
        } elseif ($status === 'FAILED') {
            $success = false;
            $responseCode = '400';
            $state = 'FAILED';
        } else {
            $success = false;
            $responseCode = '400';
            $state = 'CANCELLED';
        }

        $payload = [
            'TRANSACTIONID' => $row->TRANSACTIONID ?? '',
            'code' => '',
            'message' => '',
            'success' => $success,
            'responseCode' => $responseCode,
            'state' => $state,
            'reference_id' => $refno,
            'amount' => $row->Amt ?? '',
            'type' => 'UPI',
            'utr' => $row->UTR ?? '',
        ];

        if ($callbackUrl === '0' || $callbackUrl === '' || $callbackUrl === null) {
            return ['ok' => false, 'message' => 'No callback URL on file for this user - nothing sent.'];
        }

        // Reproduces the source's double JSON-encode exactly (see class
        // docblock) - this is almost certainly an existing bug, not
        // something to silently "fix" during a faithful port.
        $jsondata = json_encode($payload);
        $encodedData = base64_encode(json_encode($jsondata));

        try {
            $response = Http::asJson()->post($callbackUrl, ['response' => $encodedData]);
            $response->throw();

            Log::info("url:{$callbackUrl} data:{$encodedData} Status:{$response->body()}");

            return ['ok' => true, 'message' => "Callback sent to {$callbackUrl}."];
        } catch (\Throwable $e) {
            Log::error("url:{$callbackUrl} data:{$encodedData} Error:{$e->getMessage()}");

            return ['ok' => false, 'message' => "Callback to {$callbackUrl} failed: {$e->getMessage()}"];
        }
    }
}
