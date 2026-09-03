<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Port of the check repeated at the top of every user/*.aspx.cs Page_Load:
 *
 *   if (Session["LOGINID"] != null && Session["AuthTokenUs"] != null && Request.Cookies["AuthTokenUs"] != null)
 *   {
 *       if (!Session["AuthTokenUs"].ToString().Equals(Request.Cookies["AuthTokenUs"].Value))
 *           Response.Redirect("~/userlogin.aspx");
 *       ...
 *   }
 *   else
 *       Response.Redirect("~/userlogin.aspx");
 *
 * Same three-way check: session login id set, session token set, cookie
 * token set, and session token === cookie token.
 */
class UserAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $loginId = session('user.login_id');
        $sessionToken = session('user.auth_token');
        $cookieToken = $request->cookie('AuthTokenUs');

        if (! $loginId || ! $sessionToken || ! $cookieToken || ! hash_equals((string) $sessionToken, (string) $cookieToken)) {
            return redirect()->route('user.login');
        }

        return $next($request);
    }
}
