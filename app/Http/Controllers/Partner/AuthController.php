<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Port of partnerlogin.aspx(.cs), partner/logout.aspx(.cs),
 * partner/ForgotPassword.aspx(.cs) and the shared ChangePasswordF.aspx(.cs)
 * (for flag=3 / partner resets). Same stored procedures, same status-code
 * branches, same messages.
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('partner.auth.login');
    }

    public function login(Request $request)
    {
        $login = trim((string) $request->input('login'));
        $password = (string) $request->input('password');
        $captcha = (string) $request->input('captcha');

        if ($login === '' || $password === '') {
            AuditLogger::write($request, $login, 'failed');

            return back()->withInput()->with('error', 'Username and Password can not be blank');
        }

        if ($captcha === '' || $captcha !== $request->session()->get('partner_captcha')) {
            AuditLogger::write($request, $login, 'failed');

            return back()->withInput()->with('error', 'Captcha code is wrong!!');
        }

        try {
            $rows = StoredProcedure::call('USP_AGENT_LOGIN', [
                '@UserId' => $login,
                '@Password' => $password,
            ]);
        } catch (\Throwable $e) {
            AuditLogger::write($request, $login, $e->getMessage());

            return back()->withInput()->with('error', $e->getMessage());
        }

        if (empty($rows)) {
            AuditLogger::write($request, $login, 'failed');

            return back()->withInput()->with('error', 'Username and Password not matched');
        }

        $row = $rows[0];
        $loginFlag = (int) $row->loginStatus;

        if ($loginFlag === 1) {
            $token = (string) Str::uuid();

            $request->session()->regenerate();
            $request->session()->put('partner.login_id', $row->login_id);
            $request->session()->put('partner.id', $row->id);
            $request->session()->put('partner.name', $row->Name);
            $request->session()->put('partner.auth_token', $token);

            $cookie = Cookie::make('AuthTokenPr', $token, 0, '/', null, $request->isSecure(), true, false, 'lax');

            return redirect()->route('partner.dashboard')->withCookie($cookie);
        }

        $messages = [
            2 => 'Partner Deactivated by backend',
            3 => 'Username and Password not matched',
            4 => 'Account has been locked.so many failed attempts.kindly contact administrator!',
            0 => 'Username and Password not matched',
        ];

        AuditLogger::write($request, $login, 'failed');

        return back()->withInput()->with('error', $messages[$loginFlag] ?? 'Username and Password not matched');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();

        return redirect()->route('partner.login')
            ->withCookie(Cookie::forget('AuthTokenPr'))
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, pre-check=0, post-check=0, max-age=0, s-maxage=0',
                'Pragma' => 'no-cache',
            ]);
    }

    public function showForgotPassword()
    {
        return view('partner.auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $email = trim((string) $request->input('email'));

        if (! preg_match('/\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $email)) {
            return back()->withInput()->with('error', 'Email format should be: xyz@abc.com');
        }

        // PtFlag = 3 for the partner portal, matching the old ForgotPassword.aspx.cs.
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
            return back()->with('error', 'Partner not exits!');
        }

        $resetLink = route('partner.password.reset', ['Email' => $sendEmail, 'Code' => $code, 'flag' => 3]);

        try {
            Mail::send('partner.emails.reset-password', ['resetLink' => $resetLink], function ($message) use ($sendEmail) {
                $message->to($sendEmail)->subject('[Vimopay] Please reset your password');
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Mail not Send Successfully! Please Try Again');
        }

        return redirect()->route('partner.login')->with('status', 'Mail Send Successfully');
    }

    public function showResetPassword(Request $request)
    {
        return view('partner.auth.reset-password', [
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

        $passphrase = (string) config('services.partner_reset.passphrase');
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
        // (admin/partner/user) the reset was for - that looks like a bug in
        // the source app, but we're not silently "fixing" it. This pilot only
        // implements the partner portal, so it redirects to partner login;
        // flag this to whoever wires up the other portals.
        return redirect()->route('partner.login')->with(
            $updateFlag === 1 ? 'status' : 'error',
            $messages[$updateFlag] ?? 'Somthing went wrong!'
        );
    }
}
