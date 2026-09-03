<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * The user-facing "Advance Search" shown in the User-panel screenshots -
 * visually identical to the admin Advance Search page (Transaction ID/UTR
 * fields, Payin/Payout radio, Search button, results table).
 *
 * No ASP.NET source exists for this page (see USER_PANEL_ANALYSIS.md). It's
 * built against `USP_USER_PAYOUT(@trFlag, @txtTrans, @txtUTR, @userID)` -
 * the user-scoped sibling of `USP_USER_PAYOUT_ADMIN` that powers the
 * admin Advance Search page (`Admin\AdvanceSearchController`): same
 * trFlag/txtTrans/txtUTR branching shape, plus a `@userID` filter that
 * scopes every branch to `m.CREATED_BY = v_userID`. High-confidence match -
 * same column set (CREATED_ON, TRANSACTIONID, UTR, CR, DR, Recharge, Fee,
 * Balance, Status, refno, UPDATED_ON, Reason), same proc-name pattern as
 * the admin variant.
 *
 * The admin page's "Sent Callback" button (which re-queries by REF ID and
 * POSTs a webhook payload) has no equivalent control anywhere in this
 * page's screenshot, so it isn't ported here - a user re-firing their
 * own payment callback isn't a feature I'll invent without it being asked
 * for or shown.
 *
 * Same unverified-blank-Transaction-ID quirk as the admin page is
 * reproduced here for consistency (`txtTrans`/`txtUTR` sent as literal "0"
 * when blank) - there's no source to say otherwise for this page either,
 * and matching the admin proc's own calling convention is the safest
 * default until proven wrong.
 */
class AdvanceSearchController extends Controller
{
    public function index()
    {
        return view('user.advance-search.index', [
            'rows' => [],
            'trFlag' => '1',
            'trans' => '',
            'utr' => '',
        ]);
    }

    public function search(Request $request)
    {
        $trFlag = $request->input('tr_flag', '1');
        $trans = (string) $request->input('trans', '');
        $utr = (string) $request->input('utr', '');

        $rows = StoredProcedure::call('USP_USER_PAYOUT', [
            '@trFlag' => $trFlag,
            '@txtTrans' => $trans === '' ? '' : $trans,
            '@txtUTR' => $utr === '' ? '' : $utr,
            '@userID' => (int) $request->session()->get('user.id'),
        ]);

        return view('user.advance-search.index', [
            'rows' => $rows,
            'trFlag' => $trFlag,
            'trans' => $trans,
            'utr' => $utr,
        ]);
    }
}
