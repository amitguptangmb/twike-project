<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Port of the check at the top of admin/AdminMaster.Master.cs's Page_Load:
 *
 *   if (Session["LogIn"] == null)
 *       Response.Redirect("~/adminlogin.aspx");
 *
 * plus the token-vs-cookie check repeated in admin/changepassword.aspx.cs's
 * own Page_Load (the master page alone never checked the AuthTokenad
 * cookie/session pair - only individual content pages did, changepassword
 * being the one example we have). Applied uniformly here to every
 * authenticated admin route rather than page-by-page, same as the partner
 * portal's PartnerAuth.
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $loginId = session('admin.id');
        $sessionToken = session('admin.auth_token');
        $cookieToken = $request->cookie('AuthTokenad');

        if (! $loginId || ! $sessionToken || ! $cookieToken || ! hash_equals((string) $sessionToken, (string) $cookieToken)) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
