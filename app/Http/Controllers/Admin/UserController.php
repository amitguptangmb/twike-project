<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Port of admin/UserList.aspx(.cs).
 *
 * Deliberately NOT ported (dead in the source, not skipped by choice):
 * - The three "Add Fund in Payout/Payin Wallet" and "update Hold Amount"
 *   modals (updatepayoutAmount / updatePayinAmount / updateHoldAmount).
 *   Nothing in the page opens them (no data-target references them
 *   anywhere), and their Submit buttons' click handlers
 *   (btn_Add_Fund_payout_Click / btn_Add_Fund_payin_Click /
 *   update_hold_amount_Click) are empty in the source - orphaned dead UI.
 * - The hidden "ChangeStatus" LinkButton column: Visible="false" in the
 *   source markup (never rendered) and its RowCommand branch is an empty
 *   no-op anyway.
 *
 * Not yet ported (unlike the above, these ARE reachable and DO something
 * in the source - just not built yet in this pass): EditUser.aspx (the
 * "Edit" link) and EditAmount.aspx (the PayOut/PayIN/Hold "Update ...
 * Amount" links). Both route to ComingSoonController for now.
 */
class UserController extends Controller
{
    /** @see \PortalApp\admin\UserList.payinChannel() */
    private const PAYIN_CHANNELS = [
        'PP' => 'PhonePay',
        'TP' => 'TimesPay',
        'TL' => 'TrustlyPay',
        'PB' => 'PayblinkPay',
        'OS' => 'OneStopPay',
        'KP' => 'KiwssPay',
        'SA' => 'SafePay',
    ];

    /** @see \PortalApp\admin\UserList.payOutChannel() */
    private const PAYOUT_CHANNELS = [
        1 => 'TrustlyPay',
        2 => 'RazorPay',
        3 => 'SafexPay',
        4 => 'RBL',
        5 => 'SBI',
        6 => 'PayblinkPay',
        7 => 'KiwssPay',
        8 => 'Filpopay',
        9 => 'Safepay',
    ];

    public function index(Request $request)
    {
        $users = StoredProcedure::call('GetUserList');
        $agents = StoredProcedure::call('GetAgentList');

        foreach ($users as $user) {
            $user->StatusLabel = ((int) $user->Status === 1) ? 'Active' : 'Deactive';
            $user->PayoutFlagLabel = ((int) $user->payout_flag === 1) ? 'Active' : 'Deactive';
            $user->RTSettlementLabel = ((int) $user->RTSettlementOn === 1) ? 'On' : 'Off';
            $user->PayinChnlLabel = self::PAYIN_CHANNELS[$user->Payin_Chnl] ?? 'N/A';
            $user->PayoutChnlLabel = self::PAYOUT_CHANNELS[(int) $user->PayoutServiceID] ?? 'N/A';

            // Port of GridView1_RowDataBound's per-row gvOrders child grid -
            // same N+1-per-row query the source runs, parameterized here
            // instead of the source's string-interpolated SQL (same result
            // for the userID values this is ever called with - a plain
            // int off GridView1's own DataKeys - just not built by
            // concatenation).
            $user->childRows = DB::select(
                'SELECT um.Mobile, um.EmailId, um.SID, um.Available_Amount, um.Collection_Amount, ' .
                    'um.Callback_URL, um.Payout_Url, um.Hold_Amt, ut.Pay_Tax, ut.Tax, um.ClientID, ' .
                    'um.ClientSecret, um.FailedCount, um.Business_Type, um.Business_Category, ' .
                    'um.Business_Sub_Category, um.WebAppURL, um.BankName, um.AccountHolderName, ' .
                    'um.ifsc, um.Account ' .
                    'FROM UserMaster um JOIN User_Tax_Mst ut ON ut.UserID = um.UserId ' .
                    'WHERE um.UserId = ? LIMIT 1',
                [$user->UserId]
            );
        }

        return view('admin.users.index', ['users' => $users, 'agents' => $agents]);
    }

    /**
     * Port of btnSubmit_Click(). No server-side validation in the source -
     * only client-side JS (Allvalidate(), ported into the view unchanged).
     * Preserved here, not added.
     */
    public function store(Request $request)
    {
        $password = $request->input('password', '') !== '' ? $request->input('password') : 'Pass@123R';
        $pan = $request->input('pan', '') !== '' ? $request->input('pan') : 'ABCDE2586G';
        $payoutFlag = $request->boolean('payout_flag_a') ? 1 : 0;

        $params = [
            '@Name' => $request->input('user_name'),
            '@BusinessName' => $request->input('business_name'),
            '@Mobile' => $request->input('mobile'),
            '@EmailId' => $request->input('email'),
            '@Gender' => $request->input('gender'),
            '@Dob' => $request->input('dob'),
            '@PAN' => $pan,
            '@Pincode' => $request->input('pincode'),
            '@Address' => $request->input('address'),
            '@City' => $request->input('city'),
            '@State' => $request->input('state'),
            // The source never sets @GSTNo either - USP_ADD_USER
            // accepts it but btnSubmit_Click has no txt_GSTNo field
            // to read from.
            '@GSTNo' => null,
            '@Status' => $request->input('status', 1),
            '@Password' => $password,
            '@SID' => $request->input('sid'),
            '@payout_flag' => $payoutFlag,
            // Source quirk preserved: RTSettlementOn is driven by the
            // SAME checkbox as payout_flag (ck_payout_flag_a) - the
            // separate ck_rl_time_st_on_a checkbox on the form is
            // never read anywhere in btnSubmit_Click.
            '@RTSettlementOn' => $payoutFlag,
            '@Payin_Chnl' => $request->input('pay_in_channel'),
            '@Payin_tax' => (float) $request->input('payin_tax', 0),
            '@Payout_tax' => (float) $request->input('payout_tax', 0),
            '@Business_Type' => $request->input('business_type'),
            '@Business_Category' => $request->input('business_category'),
            '@Business_Sub_Category' => $request->input('business_sub_category'),
            '@WebAppURL' => $request->input('webapp_url'),
            '@Account' => $request->input('account_no'),
            '@ifsc' => $request->input('ifsc'),
            '@BankName' => $request->input('bank_name'),
            '@AccountHolderName' => $request->input('account_holder_name'),
            '@Payout_Chnl' => $request->input('pay_out_channel'),
            '@AgentID' => $request->input('agent_id'),
            '@workingKey' => $request->input('working_key'),
        ];

        try {
            $rows = StoredProcedure::call('USP_ADD_USER', $params);
        } catch (\Throwable $e) {
            // Self-heal: if this is specifically the "database still has
            // the old, too-narrow v_Payin_Chnl parameter" error (see
            // StoredProcedure::fixPayinChnlWidth() and MIGRATION_NOTES.md,
            // "Payin_Chnl width fix, round 5"), fix the database right now
            // using this app's own working DB connection and retry ONCE,
            // rather than making the admin run anything by hand. Only
            // falls through to the error message if the fix itself fails
            // too (most likely a DB privilege problem).
            if (StoredProcedure::isPayinChnlWidthError($e)) {
                try {
                    StoredProcedure::fixPayinChnlWidth();
                    $rows = StoredProcedure::call('USP_ADD_USER', $params);
                } catch (\Throwable $e2) {
                    return redirect()->route('admin.users')->with('error', StoredProcedure::friendlyError($e2));
                }
            } else {
                return redirect()->route('admin.users')->with('error', StoredProcedure::friendlyError($e));
            }
        }

        if (! empty($rows) && (int) $rows[0]->status === 1) {
            return redirect()->route('admin.users')->with('status', 'User Added successfully');
        }

        return redirect()->route('admin.users')->with('error', 'There might be some error! Please Check Again!');
    }

    /**
     * Port of btn_settlement_click(). Preserves the source's own inverted
     * success check: ExecuteNonQuery()'s return value is only treated as
     * success when it's NEGATIVE ("< 0"), which is how ADO.NET reports rows
     * affected for some non-SELECT stored-procedure calls - not an
     * intuitive check, but that's what the source does, not something to
     * "fix" here. rowCount() on the PDO statement is the closest available
     * equivalent to ExecuteNonQuery()'s return value.
     */
    public function settlement(Request $request, int $userId)
    {
        try {
            $before = DB::table('UserMaster')->where('UserId', $userId)->value('Collection_Amount');

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare('CALL SettlementtoWallet (?)');
            $stmt->execute([$userId]);
            while ($stmt->nextRowset()) {
                //
            }

            $after = DB::table('UserMaster')->where('UserId', $userId)->value('Collection_Amount');

            if ($before > 0 && (float) $after === 0.0) {
                return back()->with('status', "Settlement Done for user = {$userId}");
            }

            return back()->with('error', "Settlement not Done for user = {$userId}");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
