<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

class EditUserController extends Controller
{
    public function edit(Request $request, int $userId)
    {
        $rows = StoredProcedure::call('GET_USER_BY_ID', ['@UserId' => $userId]);
        $user = $rows[0];

        $agents = StoredProcedure::call('GetAgentList');

        // pull(), not get(): the revealed password is only meant to survive
        // the single redirect back from revealPassword() below, same as
        // withInput()'s old() data - a later, unrelated visit to this page
        // (or a different user's edit page) must not keep showing it.
        $revealedPassword = $request->session()->pull('admin.edit_user.revealed_password');

        return view('admin.users.edit', [
            'userId' => $userId,
            'user' => $user,
            'agents' => $agents,
            'revealedPassword' => $revealedPassword,
            'showPasswordButton' => $revealedPassword === null,
        ]);
    }

    /** Port of btnSubmit_Click(). */
    public function update(Request $request, int $userId)
    {
        $params = [
            '@UserID' => $userId ?: null,
            '@Name' => $request->input('user_name') ?: null,
            '@BusinessName' => $request->input('business_name') ?: null,
            '@Mobile' => $request->input('mobile') ?: null,
            '@EmailId' => $request->input('email') ?: null,
            '@Gender' => $request->input('gender') ?: null,
            '@Dob' => $request->input('dob') ?: null,
            '@PAN' => (string) $request->input('pan' ?: null, '') ?: null,
            '@Pincode' => $request->input('pincode') ?: null,
            '@Address' => $request->input('address') ?: null,
            '@City' => $request->input('city') ?: null,
            '@State' => $request->input('state') ?: null,
            '@GSTNo' => null ?: null,
            '@Status' => $request->input('status' ?: null, 1) ?: null,
            '@Password' => (string) $request->input('password' ?: null, '') ?: null,
            '@SID' => $request->input('sid') ?: null,
            '@CallbackPayin' => $request->input('payin_callback') ?: null,
            '@CallbackPayOut' => $request->input('payout_callback') ?: null,
            '@payout_flag' => $request->boolean('payout_flag_a') ? 1 : 0,
            '@RTSettlementOn' => $request->boolean('rl_time_st_on_a') ? 1 : 0,
            '@Payin_Chnl' => $request->input('pay_in_channel') ?: null,
            '@Payin_tax' => (float) $request->input('payin_tax' ?: null, 0) ?: null,
            '@Payout_tax' => (float) $request->input('payout_tax' ?: null, 0) ?: null,
            '@FailedCount' => $request->input('failed_count' ?: null, 0) ?: null,
            '@Business_Type' => $request->input('business_type') ?: null,
            '@Business_Category' => $request->input('business_category') ?: null,
            '@Business_Sub_Category' => $request->input('business_sub_category') ?: null,
            '@WebAppURL' => $request->input('webapp_url') ?: null,
            '@Account' => $request->input('account_no') ?: null,
            '@ifsc' => $request->input('ifsc') ?: null,
            '@BankName' => $request->input('bank_name') ?: null,
            '@AccountHolderName' => $request->input('account_holder_name') ?: null,
            '@Payout_Chnl' => $request->input('pay_out_channel') ?: null,
            '@AgentID' => $request->input('agent_id') ?: null,
            '@workingKey' => $request->input('working_key') ?: null,
        ];
        
        try {
            $rows = StoredProcedure::call('USP_UPDATE_USER', $params);
        } catch (\Throwable $e) {
            // Self-heal: see the matching comment in UserController::store().
            if (StoredProcedure::isPayinChnlWidthError($e)) {
                try {
                    StoredProcedure::fixPayinChnlWidth();
                    $rows = StoredProcedure::call('USP_UPDATE_USER', $params);
                } catch (\Throwable $e2) {
                    $request->session()->forget('admin.edit_user.revealed_password');

                    return back()->withInput()->with('error', StoredProcedure::friendlyError($e2, 'There might be some error! Please Check Again!'));
                }
            } else {
                $request->session()->forget('admin.edit_user.revealed_password');

                return back()->withInput()->with('error', StoredProcedure::friendlyError($e, 'There might be some error! Please Check Again!'));
            }
        }

        if (! empty($rows) && (int) $rows[0]->status === 1) {
            return redirect()->route('admin.users')->with('status', 'User updated successfully');
        }

        return back()->withInput()->with('error', 'There might be some error! Please Check Again!');
    }

    /**
     * Port of btn_get_pasword_Click(). Source behaviour: this is a postback
     * of the SAME form (single <form runat="server"> wraps both the main
     * fields and the admin-password modal), so every other field's typed
     * value survives via ViewState and the page re-renders with the
     * password revealed (or the "Put Right Admin Password" message) and the
     * "View Password" button swapped for the revealed text.
     *
     * Reproduced with withInput() (flashes every posted field back for the
     * next request's old()) plus a one-shot session value for the revealed
     * password/button state, since Laravel has no server-side ViewState
     * equivalent - the redirect back to the GET edit route re-renders the
     * form from that flashed input instead of from the database.
     */
    public function revealPassword(Request $request, int $userId)
    {
        $rows = StoredProcedure::call('USP_GET_PASSWORD', [
            '@userID' => $userId,
            '@Adminpassword' => (string) $request->input('admin_password', ''),
        ]);

        $password = $rows[0]->Password ?? '';
        $revealed = $password !== '' ? $password : 'Put Right Admin Password';

        $request->session()->put('admin.edit_user.revealed_password', $revealed);

        return back()->withInput();
    }

    /** Port of btnCancel_Click(). */
    public function cancel(Request $request)
    {
        $request->session()->forget('admin.edit_user.revealed_password');

        return redirect()->route('admin.users');
    }
}
