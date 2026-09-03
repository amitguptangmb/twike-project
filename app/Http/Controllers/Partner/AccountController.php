<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The partner-facing "My Account" page shown in the User-panel screenshots -
 * 4 tabs: Account Info, Change Password, Message, Support.
 *
 * No ASP.NET source exists for this page (see USER_PANEL_ANALYSIS.md).
 * - Account Info's 4 fields (Name, Business Name, Email ID, Mobile Number)
 *   are plain UserMaster columns. No dedicated "read/update my own profile"
 *   proc exists anywhere in the SQL export (only admin-side USER procs,
 *   which aren't appropriate for a partner to call on themselves), so this
 *   is a direct UPDATE - the one deliberate exception in this whole file to
 *   "business logic stays in procs," made because there is no proc to call.
 * - Change Password reuses `Partner\PasswordController::update()` completely
 *   unchanged - that's already a faithful port of changepassword.aspx.cs
 *   (USP_AGENT_CHANGE_PASSWORD). This tab's form just posts to the same
 *   existing `partner.password.update` route instead of duplicating that
 *   logic here.
 * - Message: no screenshot shows this tab's content and no source exists,
 *   so it's a placeholder rather than an invented feature.
 * - Support: static text ("Email:COINNECTED@gmail.com"), matches the
 *   screenshot exactly - no dynamic data.
 */
class AccountController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = (int) $request->session()->get('partner.id');

        $account = DB::table('UserMaster')->where('UserId', $partnerId)->first();

        return view('partner.account.index', [
            'account' => $account,
            'userId' => $request->session()->get('partner.login_id'),
        ]);
    }

    public function update(Request $request)
    {
        $partnerId = (int) $request->session()->get('partner.id');

        DB::table('UserMaster')->where('UserId', $partnerId)->update([
            'Name' => (string) $request->input('name', ''),
            'BusinessName' => (string) $request->input('business_name', ''),
            'EmailId' => (string) $request->input('email', ''),
            'Mobile' => (string) $request->input('mobile', ''),
        ]);

        return back()->with('status', 'Account info updated.');
    }
}
