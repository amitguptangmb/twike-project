<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

/**
 * Placeholder for user pages not yet built. Currently only "Reports":
 * no screenshot shows its content and no ASP.NET source exists anywhere in
 * the upload (see USER_PANEL_ANALYSIS.md) - there isn't enough information
 * to build it yet, so this keeps the sidebar link alive without breaking
 * anything until either a screenshot or a description of what it should
 * show is available. Mirrors Admin\ComingSoonController's pattern.
 */
class ComingSoonController extends Controller
{
    public function show(string $page)
    {
        return view('user.coming-soon', ['page' => $page]);
    }
}
