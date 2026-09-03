<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Port of adminlogin.aspx(.cs), admin/LogOut.aspx(.cs), admin/ForgotPassword.aspx(.cs)
 * (which calls the shared SendEmailClass.SendMail(email, 1) - PtFlag=1 for
 * admin) and the shared ChangePasswordF.aspx(.cs) for flag=1 (admin) resets.
 * Same stored procedures, same status-code branches, same messages as the
 * ASP.NET source - see MIGRATION_NOTES.md for what's verified vs not.
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $login = trim((string) $request->input('login'));
        $password = (string) $request->input('password');
        $captcha = (string) $request->input('captcha');

        // Original has no explicit branch for blank fields other than the
        // outer `if (txtlogin.Text != "" && txtpassword.Text != "")` - the
        // else simply shows "Username and Password can not be blank".
        if ($login === '' || $password === '') {
            AuditLogger::write($request, $login, 'failed');

            return back()->withInput()->with('error', 'Username and Password can not be blank');
        }

        if ($captcha === '' || $captcha !== $request->session()->get('admin_captcha')) {
            AuditLogger::write($request, $login, 'failed');

            return back()->withInput()->with('error', 'Captcha code is wrong!!');
        }

        // The procedure has two OUT parameters (@loginStatus, @usetID) and
        // no result set - StoredProcedure::call()/callWithOutput() don't fit
        // (call() can't omit OUT params from the positional CALL at all;
        // callWithOutput() only supports a single OUT param), so this one
        // call site builds the CALL directly. See loginWithTwoOutputs() below.
        try {
            [$loginFlag, $userId] = self::loginWithTwoOutputs($login, $password);
        } catch (\Throwable $e) {
            AuditLogger::write($request, $login, $e->getMessage());

            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($loginFlag === 1) {
            $token = (string) Str::uuid();

            $request->session()->regenerate();
            $request->session()->put('admin.id', $userId);
            $request->session()->put('admin.auth_token', $token);

            $cookie = Cookie::make('AuthTokenad', $token, 0, '/', null, $request->isSecure(), true, false, 'lax');

            AuditLogger::write($request, $login, 'success');

            return redirect()->route('admin.dashboard')->withCookie($cookie);
        }

        $messages = [
            2 => 'Admin user Deactivated by backend',
            3 => 'Username and Password not matched',
            4 => 'Account has been locked.so many failed attempts.kindly contact administrator!',
            0 => 'Username and Password not matched',
        ];

        AuditLogger::write($request, $login, 'failed');

        return back()->withInput()->with('error', $messages[$loginFlag] ?? 'Username and Password not matched');
    }

    /**
     * USP_GET_USER_ID_PASSWORD_1_Admin has two OUT parameters (@loginStatus
     * and @usetID - that's the original's own spelling, not a typo
     * introduced here). StoredProcedure::callWithOutput() only supports a
     * single OUT parameter, so this calls it directly rather than adding a
     * two-output variant to a shared service class for one call site.
     *
     * @return array{0: int, 1: int}
     */
    private static function loginWithTwoOutputs(string $login, string $password): array
    {
        $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();

        $sql = 'CALL USP_GET_USER_ID_PASSWORD_1_Admin (?, ?, @sp_out_loginstatus, @sp_out_usetid)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login, $password]);

        while ($stmt->nextRowset()) {
            //
        }

        $row = \Illuminate\Support\Facades\DB::connection()->selectOne(
            'SELECT @sp_out_loginstatus AS loginStatus, @sp_out_usetid AS userId'
        );

        return [(int) $row->loginStatus, (int) $row->userId];
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();

        return redirect()->route('admin.login')
            ->withCookie(Cookie::forget('AuthTokenad'))
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, pre-check=0, post-check=0, max-age=0, s-maxage=0',
                'Pragma' => 'no-cache',
            ]);
    }

    public function showForgotPassword()
    {
        return view('admin.auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $email = trim((string) $request->input('email'));

        if (! preg_match('/\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $email)) {
            return back()->withInput()->with('error', 'Email format should be: xyz@abc.com');
        }

        // PtFlag = 1 for admin, matching SendEmailClass.SendMail(email, 1) as
        // called from admin/ForgotPassword.aspx.cs.
        // NOTE: "forgotPasswordCode" was not included in the SQL export this
        // migration was given, so it isn't in the MySQL database yet - see
        // MIGRATION_NOTES.md. This flow is non-functional until that
        // procedure (and forgotPasswordUpdate, used later) are supplied and
        // ported; fail with a clear message instead of a raw SQL error page.
        try {
            $rows = StoredProcedure::callIndexed('forgotPasswordCode', [
                '@email' => $email,
                '@UserType' => 1,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Password reset is temporarily unavailable. Please contact support.');
        }

        if (empty($rows)) {
            return back()->with('error', 'Mail not Send Successfully! Please Try Again');
        }

        [$sendEmail, $code, $statusUser] = $rows[0];

        if ((int) $statusUser === 0) {
            return back()->with('error', 'Admin user not exits!');
        }

        $resetLink = route('admin.password.reset', ['Email' => $sendEmail, 'Code' => $code, 'flag' => 1]);

        try {
            Mail::send('admin.emails.reset-password', ['resetLink' => $resetLink], function ($message) use ($sendEmail) {
                $message->to($sendEmail)->subject('[Vimopay] Please reset your password');
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Mail not Send Successfully! Please Try Again');
        }

        return redirect()->route('admin.login')->with('status', 'Mail Send Successfully');
    }

    public function showResetPassword(Request $request)
    {
        return view('admin.auth.reset-password', [
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

        // Matches the original's own (loose) check:
        // !string.IsNullOrEmpty(txt_newPass.Text) && !string.IsNullOrEmpty(txt_confirm.Text)
        // - it never actually compares the two values against each other.
        if ($newPassword === '' || $confirmPassword === '') {
            return back()->withInput()->with('error', 'Username and Password can not be blank');
        }

        $passphrase = (string) config('services.user_reset.passphrase');
        // encryptByPassPhrase() no longer encrypts anything - see
        // StoredProcedure.php and MIGRATION_NOTES.md.
        $encrypted = StoredProcedure::encryptByPassPhrase($passphrase, $newPassword);

        // NOTE: "forgotPasswordUpdate" was not included in the SQL export
        // this migration was given - see MIGRATION_NOTES.md.
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

        // NOTE: the original ChangePasswordF.aspx.cs redirects to
        // ~/login.aspx (the merchant login) in every branch regardless of
        // which portal (admin/partner/user) the reset was for - looks like a
        // bug in the source app, not silently "fixed" here. Redirects to
        // admin login instead since that's the portal this flow belongs to,
        // same deliberate deviation already applied to the partner portal.
        return redirect()->route('admin.login')->with(
            $updateFlag === 1 ? 'status' : 'error',
            $messages[$updateFlag] ?? 'Somthing went wrong!'
        );
    }
}
