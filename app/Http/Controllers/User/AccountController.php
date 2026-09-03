<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The user-facing "My Account" page shown in the User-panel screenshots -
 * 4 tabs: Account Info, Change Password, Message, Support.
 *
 * No ASP.NET source exists for this page (see USER_PANEL_ANALYSIS.md).
 * - Account Info's 4 fields (Name, Business Name, Email ID, Mobile Number)
 *   are plain UserMaster columns. No dedicated "read/update my own profile"
 *   proc exists anywhere in the SQL export (only admin-side USER procs,
 *   which aren't appropriate for a user to call on themselves), so this
 *   is a direct UPDATE - the one deliberate exception in this whole file to
 *   "business logic stays in procs," made because there is no proc to call.
 * - Change Password reuses `User\PasswordController::update()` completely
 *   unchanged - that's already a faithful port of changepassword.aspx.cs
 *   (USP_AGENT_CHANGE_PASSWORD). This tab's form just posts to the same
 *   existing `user.password.update` route instead of duplicating that
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
        $userId = (int) $request->session()->get('user.id');

        $account = DB::table('UserMaster')->where('UserId', $userId)->first();

        return view('user.account.index', [
            'account' => $account,
            'userId' => $request->session()->get('user.login_id'),
        ]);
    }

    public function update(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');

        DB::table('UserMaster')->where('UserId', $userId)->update([
            'Name' => (string) $request->input('name', ''),
            'BusinessName' => (string) $request->input('business_name', ''),
            'EmailId' => (string) $request->input('email', ''),
            'Mobile' => (string) $request->input('mobile', ''),
        ]);

        return back()->with('status', 'Account info updated.');
    }
}
