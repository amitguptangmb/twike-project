<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * Port of partner/changepassword.aspx(.cs). Note the original does NOT
 * encrypt the old/new password before sending them to
 * USP_AGENT_CHANGE_PASSWORD - unlike the forgot-password flow, which
 * encrypts via SQL Server's EncryptByPassPhrase first. That asymmetry is
 * preserved here rather than "fixed", since the stored procedure is the
 * source of truth for how it expects to receive the password.
 *
 * Also note: the original's `finally { conn.Close(); Session.Clear(); }`
 * clears the session on EVERY submit, success or failure, but only
 * redirects on success (failure just re-shows the page via a JS alert,
 * with a now-dead session). This port redirects to login with a flashed
 * error on failure too, since staying on a page with a cleared session is
 * not meaningfully different from bouncing to login - flagged here as a
 * deliberate small deviation, not an oversight.
 */
class PasswordController extends Controller
{
    public function edit(Request $request)
    {
        return view('partner.password.edit', [
            'userId' => $request->session()->get('partner.login_id'),
        ]);
    }

    public function update(Request $request)
    {
        $oldPassword = (string) $request->input('old_password');
        $newPassword = (string) $request->input('new_password');
        $confirmPassword = (string) $request->input('confirm_password');

        // Same regex as the original client-side validatePassword().
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        if ($oldPassword === '') {
            return back()->with('error', 'Please Old Enter Password');
        }
        if ($newPassword === '' || ! preg_match($pattern, $newPassword)) {
            return back()->with('error', 'Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters!');
        }
        if ($confirmPassword !== $newPassword) {
            return back()->with('error', 'confrim Password and Password do not matched');
        }

        $changeFlag = StoredProcedure::callWithOutput('USP_AGENT_CHANGE_PASSWORD', [
            '@UserId' => $request->session()->get('partner.id'),
            '@OldPassword' => $oldPassword,
            '@Password' => $newPassword,
        ], '@loginStatus');

        $changeFlag = (int) $changeFlag;
        $request->session()->flush();

        if ($changeFlag === 1) {
            return redirect()->route('partner.login')->with('status', 'Password Changed Successfully');
        }
        if ($changeFlag === 2) {
            return redirect()->route('partner.login')->with('error', 'Old Password not matched');
        }

        return redirect()->route('partner.login')->with('error', 'Password not Changed Successfully! please try again');
    }
}
