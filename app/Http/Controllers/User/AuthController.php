<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Port of userlogin.aspx(.cs), user/logout.aspx(.cs),
 * user/ForgotPassword.aspx(.cs) and the shared ChangePasswordF.aspx(.cs)
 * (for flag=3 / user resets).
 *
 * login()/showLogin() are NOT a port - the real login table turned out to
 * be `UserMaster` (confirmed directly by the user against their actual
 * `TwikeDB.usermaster` schema/data), not `AgentMst` the way
 * USP_AGENT_LOGIN checked it. UserMaster has no login_id column at all, so
 * "log in with @UserId/@Password against AgentMst" was never going to work
 * against real data - switched to querying UserMaster directly. No proc
 * exists for this shape (nothing in the SQL export logs a user in against
 * UserMaster's own columns), so this is the same deliberate "no proc to
 * call" exception AccountController already makes for profile reads.
 *
 * Single login mode: Email + Password (UserMaster.EmailId / .Password).
 * A Client ID + Client Secret toggle was built and then explicitly removed
 * per instruction ("directly login with email and password") - dropped
 * from both this controller and the view, not just hidden. Password
 * compared in PLAIN TEXT, per explicit instruction - matches how this
 * table actually stores it (see StoredProcedure::encryptByPassPhrase()'s
 * docblock: nothing in this app encrypts passwords today). `Status = 1`
 * required to log in (confirmed with the user) - a Status=0 row won't
 * authenticate even with the right credentials.
 *
 * Side effect worth knowing: this also resolves an ID-space mismatch
 * flagged earlier in MIGRATION_NOTES.md - session('user.id') is now
 * UserMaster.UserId directly (not AgentMst.ID), so every other controller
 * that already queries UserMaster WHERE UserId = session('user.id')
 * (Dashboard, Setting, Account, Advance Search, ...) is now querying the
 * SAME id space the logged-in row actually belongs to, not a
 * different table's ID reused by convention.
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('user.auth.login');
    }

    public function login(Request $request)
    {
        $identifier = trim((string) $request->input('email'));
        $secret = (string) $request->input('password');
        $captcha = (string) $request->input('captcha');

        if ($identifier === '' || $secret === '') {
            AuditLogger::write($request, $identifier, 'failed');

            return back()->withInput()->with('error', 'Email and Password can not be blank');
        }

        if ($captcha === '' || $captcha !== $request->session()->get('user_captcha')) {
            AuditLogger::write($request, $identifier, 'failed');

            return back()->withInput()->with('error', 'Captcha code is wrong!!');
        }

        $row = DB::table('UserMaster')
            ->where('Status', 1)
            ->where('EmailId', $identifier)
            ->where('Password', $secret)
            ->first();

        if (! $row) {
            AuditLogger::write($request, $identifier, 'failed');

            return back()->withInput()->with('error', 'Username and Password not matched');
        }

        $token = (string) Str::uuid();

        $request->session()->regenerate();
        $request->session()->put('user.login_id', $identifier);
        $request->session()->put('user.id', $row->UserId);
        $request->session()->put('user.name', $row->Name);
        $request->session()->put('user.auth_token', $token);

        $cookie = Cookie::make('AuthTokenUs', $token, 0, '/', null, $request->isSecure(), true, false, 'lax');

        return redirect()->route('user.dashboard')->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();

        return redirect()->route('user.login')
            ->withCookie(Cookie::forget('AuthTokenUs'))
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, pre-check=0, post-check=0, max-age=0, s-maxage=0',
                'Pragma' => 'no-cache',
            ]);
    }

    public function showForgotPassword()
    {
        return view('user.auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $email = trim((string) $request->input('email'));

        if (! preg_match('/\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $email)) {
            return back()->withInput()->with('error', 'Email format should be: xyz@abc.com');
        }

        // PtFlag = 3 for the user portal, matching the old ForgotPassword.aspx.cs.
        // NOTE: "forgotPasswordCode" was not included in the SQL export this
        // migration was given, so it isn't in the MySQL database yet - see
        // MIGRATION_NOTES.md. This whole flow is non-functional until that
        // procedure (and forgotPasswordUpdate, used later in the flow) are
        // supplied and ported; fail with a clear message instead of a raw
        // SQL error page.
        try {
            $rows = StoredProcedure::callIndexed('forgotPasswordCode', [
                '@email' => $email,
                '@UserType' => 3,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Password reset is temporarily unavailable. Please contact support.');
        }

        if (empty($rows)) {
            return back()->with('error', 'Mail not Send Successfully! Please Try Again');
        }

        // Ordinal access on purpose - mirrors dt.Rows[0][0]/[1]/[2] in the
        // original, since we don't have the proc's column names, only positions.
        [$sendEmail, $code, $statusUser] = $rows[0];

        if ((int) $statusUser === 0) {
            return back()->with('error', 'User not exits!');
        }

        $resetLink = route('user.password.reset', ['Email' => $sendEmail, 'Code' => $code, 'flag' => 3]);

        try {
            Mail::send('user.emails.reset-password', ['resetLink' => $resetLink], function ($message) use ($sendEmail) {
                $message->to($sendEmail)->subject('[Vimopay] Please reset your password');
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Mail not Send Successfully! Please Try Again');
        }

        return redirect()->route('user.login')->with('status', 'Mail Send Successfully');
    }

    public function showResetPassword(Request $request)
    {
        return view('user.auth.reset-password', [
            'email' => $request->query('Email', ''),
            'code' => $request->query('Code', ''),
            'flag' => $request->query('flag', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $email = (string) $request->input('email');
        $code = (string) $request->input('code');
        $flag = (int) $request->input('flag');
        $newPassword = (string) $request->input('new_password');
        $confirmPassword = (string) $request->input('confirm_password');

        if ($newPassword === '' || $confirmPassword === '') {
            return back()->withInput()->with('error', 'Username and Password can not be blank');
        }

        $passphrase = (string) config('services.user_reset.passphrase');
        // encryptByPassPhrase() no longer encrypts anything (no MySQL
        // equivalent to SQL Server's EncryptByPassPhrase; passwords are
        // being left as plain text for now - see StoredProcedure.php).
        $encrypted = StoredProcedure::encryptByPassPhrase($passphrase, $newPassword);

        // NOTE: "forgotPasswordUpdate" was not included in the SQL export
        // this migration was given, so it isn't in the MySQL database yet -
        // see MIGRATION_NOTES.md. Fail with a clear message instead of a
        // raw SQL error page until that procedure is supplied and ported.
        try {
            $updateFlag = StoredProcedure::callWithOutput('forgotPasswordUpdate', [
                '@email' => $email,
                '@code' => $code,
                '@UserType' => $flag,
                '@Password' => $encrypted,
            ], '@flag');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Password reset is temporarily unavailable. Please contact support.');
        }

        $updateFlag = (int) $updateFlag;

        $messages = [
            1 => 'password updated Successfully',
            2 => 'user not found!',
            0 => 'Your forgot password token expired!',
        ];

        $request->session()->flush();

        // NOTE: the original ChangePasswordF.aspx.cs redirects to ~/login.aspx
        // (the merchant login) in every branch, regardless of which portal
        // (admin/user/user) the reset was for - that looks like a bug in
        // the source app, but we're not silently "fixing" it. This pilot only
        // implements the user portal, so it redirects to user login;
        // flag this to whoever wires up the other portals.
        return redirect()->route('user.login')->with(
            $updateFlag === 1 ? 'status' : 'error',
            $messages[$updateFlag] ?? 'Somthing went wrong!'
        );
    }
}
