<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;

/**
 * Port of partner/userlist.aspx(.cs) - the real ASP.NET landing page after
 * login, listed there as "User List". Columns match the GridView's
 * BoundFields exactly: UserId, Name, Available_Amount ("Payout Amount"),
 * Collection_Amount, Status.
 *
 * Moved here from `DashboardController` (route name `partner.dashboard`) -
 * the live app's current sidebar (per the User-panel screenshots) no longer
 * shows "User List" at all; "Dashboard" is now a completely different KPI
 * page with no ASP.NET source of its own (see MIGRATION_NOTES.md's "User
 * panel" section). `partner.dashboard` now points at that new KPI page;
 * this controller keeps the real, already-working "User List" page alive
 * at `partner.users` so nothing that already depends on it breaks, even
 * though it's no longer linked in the sidebar.
 */
class UserListController extends Controller
{
    public function index(Request $request)
    {
        $rows = StoredProcedure::call('Get_Agent_UserList', [
            '@AgentID' => (int) $request->session()->get('partner.id'),
        ]);

        return view('partner.users.index', ['rows' => $rows]);
    }
}
