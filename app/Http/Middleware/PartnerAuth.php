<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Port of the check repeated at the top of every partner/*.aspx.cs Page_Load:
 *
 *   if (Session["LOGINID"] != null && Session["AuthTokenPr"] != null && Request.Cookies["AuthTokenPr"] != null)
 *   {
 *       if (!Session["AuthTokenPr"].ToString().Equals(Request.Cookies["AuthTokenPr"].Value))
 *           Response.Redirect("~/partnerlogin.aspx");
 *       ...
 *   }
 *   else
 *       Response.Redirect("~/partnerlogin.aspx");
 *
 * Same three-way check: session login id set, session token set, cookie
 * token set, and session token === cookie token.
 */
class PartnerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $loginId = session('partner.login_id');
        $sessionToken = session('partner.auth_token');
        $cookieToken = $request->cookie('AuthTokenPr');

        if (! $loginId || ! $sessionToken || ! $cookieToken || ! hash_equals((string) $sessionToken, (string) $cookieToken)) {
            return redirect()->route('partner.login');
        }

        return $next($request);
    }
}
