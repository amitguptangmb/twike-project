<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * Port of admin/changepassword.aspx(.cs). Unlike the partner portal's
 * change-password page, the ORIGINAL admin one has no server-side
 * validation at all (no empty-field check, no strength regex, no
 * old/new/confirm comparison) - Allvalidate() in the .aspx only runs
 * client-side, and btn_submit_change_Click() goes straight to the stored
 * procedure call. That asymmetry with the partner portal is preserved here
 * deliberately, per "convert the same, no changes" - not silently
 * hardened to match the partner portal's stricter version.
 */
class PasswordController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.password.edit', [
            'userId' => $request->session()->get('admin.id'),
        ]);
    }

    public function update(Request $request)
    {
        $oldPassword = (string) $request->input('old_password');
        $newPassword = (string) $request->input('new_password');

        $changeFlag = StoredProcedure::callWithOutput('USP_Admin_CHANGE_PASSWORD_1', [
            '@UserId' => $request->session()->get('admin.id'),
            '@OldPassword' => $oldPassword,
            '@Password' => $newPassword,
        ], '@loginStatus');

        $changeFlag = (int) $changeFlag;

        // The original's `finally { conn.Close(); Session.Clear(); }` clears
        // the session on EVERY submit, success or failure - same as the
        // partner portal's version. Redirects to login with a flashed
        // message on every branch here too, for the same reason already
        // documented on Partner\PasswordController.
        $request->session()->flush();

        if ($changeFlag === 1) {
            return redirect()->route('admin.login')->with('status', 'Password Changed Successfully');
        }
        if ($changeFlag === 2) {
            return redirect()->route('admin.login')->with('error', 'Old Password not matched');
        }

        return redirect()->route('admin.login')->with('error', 'Password not Changed Successfully! please try again');
    }
}
