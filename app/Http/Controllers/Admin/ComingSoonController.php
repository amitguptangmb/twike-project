<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Temporary placeholder for admin pages not yet ported in this pass
 * (Dashboard, UserList/EditUser, TransactionList, SettlementList,
 * AdvanceSearch, and the rest - see MIGRATION_NOTES.md's Admin portal
 * section for the exact list and sequencing). Routes/nav for these already
 * exist so the rest of the admin layout doesn't break, but each renders
 * this instead of the real page until its own conversion pass lands.
 */
class ComingSoonController extends Controller
{
    public function show(string $page)
    {
        return view('admin.coming-soon', ['page' => $page]);
    }
}
