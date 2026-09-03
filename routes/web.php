<?php

use App\Http\Controllers\Admin\AdvanceSearchController as AdminAdvanceSearchController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CaptchaController as AdminCaptchaController;
use App\Http\Controllers\Admin\ComingSoonController as AdminComingSoonController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EditUserController as AdminEditUserController;
use App\Http\Controllers\Admin\PasswordController as AdminPasswordController;
use App\Http\Controllers\Admin\SettlementController as AdminSettlementController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\AdvanceSearchController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CaptchaController;
use App\Http\Controllers\User\CollectionController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\PasswordController;
use App\Http\Controllers\User\ReportController;
use App\Http\Controllers\User\SettingController;
use App\Http\Controllers\User\SettlementController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\UserListController;
use Illuminate\Support\Facades\Route;

// Pilot scope: User portal (default/root login) + admin portal. See
// migration notes.
//
// Three login roles exist in the real app: Admin, User, Partner. What used
// to be called "partner" in this codebase (route prefix, controllers,
// session keys, views) was actually the "User" panel per the live
// screenshots - renamed throughout to match. This IS the default/root
// login now, at /login with no prefix, matching the live app's own
// unprefixed merchant login.
//
// "Partner" (the third, genuinely distinct role) has NOT been built - no
// controllers, no routes, no views exist for it. Explicitly out of scope
// until asked for.
Route::redirect('/', '/login');

// --- Public (mirrors login.aspx / partnerlogin.aspx, ForgotPassword.aspx, ChangePasswordF.aspx) ---
Route::name('user.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.submit');

    Route::get('reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.submit');

    Route::get('captcha', [CaptchaController::class, 'show'])->name('captcha');

    // --- Authenticated (mirrors the AuthTokenUs session+cookie check in every page) ---
    Route::middleware('user.auth')->group(function () {
        // "Dashboard" (KPI page, no ASP.NET source - see USER_PANEL_ANALYSIS.md)
        // is now the post-login landing page, matching the live sidebar. The
        // real ASP.NET landing page (userlist.aspx, "User List") still works,
        // just isn't linked in the nav anymore - see UserListController.
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('userlist', [UserListController::class, 'index'])->name('users');

        Route::get('advance-search', [AdvanceSearchController::class, 'index'])->name('advance-search');
        Route::post('advance-search', [AdvanceSearchController::class, 'search'])->name('advance-search.search');

        Route::get('collection', [CollectionController::class, 'index'])->name('collection');

        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
        Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');

        Route::get('settlement', [SettlementController::class, 'index'])->name('settlement');

        // Route::get('reports', [ReportController::class, 'index'])->name('reports');

        Route::get('setting', [SettingController::class, 'index'])->name('setting');
        Route::post('setting/ip-whitelist', [SettingController::class, 'addIp'])->name('setting.ip-whitelist.add');
        Route::post('setting/ip-whitelist/{whitelistIpId}/delete', [SettingController::class, 'deleteIp'])->name('setting.ip-whitelist.delete');
        Route::post('setting/webhook', [SettingController::class, 'updateWebhook'])->name('setting.webhook.update');
        Route::post('setting/api-key/regenerate', [SettingController::class, 'regenerateApiKey'])->name('setting.api-key.regenerate');
        Route::post('setting/api-key/view', [SettingController::class, 'viewKeys'])->name('setting.api-key.view');

        Route::get('account', [AccountController::class, 'index'])->name('account');
        Route::post('account', [AccountController::class, 'update'])->name('account.update');

        Route::get('change-password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::post('change-password', [PasswordController::class, 'update'])->name('password.update');
    });
});

Route::prefix('admin')->name('admin.')->group(function () {
    // --- Public (mirrors adminlogin.aspx, admin/ForgotPassword.aspx, ChangePasswordF.aspx flag=1) ---
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::get('forgot-password', [AdminAuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('forgot-password.submit');

    Route::get('reset-password', [AdminAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('reset-password', [AdminAuthController::class, 'resetPassword'])->name('password.reset.submit');

    Route::get('captcha', [AdminCaptchaController::class, 'show'])->name('captcha');

    // --- Authenticated (mirrors the AuthTokenad session+cookie check) ---
    Route::middleware('admin.auth')->group(function () {
        Route::get('change-password', [AdminPasswordController::class, 'edit'])->name('password.edit');
        Route::post('change-password', [AdminPasswordController::class, 'update'])->name('password.update');

        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('users', [AdminUserController::class, 'index'])->name('users');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::post('users/{userId}/settlement', [AdminUserController::class, 'settlement'])->name('users.settlement');

        Route::get('users/{userId}/edit', [AdminEditUserController::class, 'edit'])->name('users.edit');
        Route::post('users/{userId}/edit', [AdminEditUserController::class, 'update'])->name('users.edit.update');
        Route::post('users/{userId}/edit/reveal-password', [AdminEditUserController::class, 'revealPassword'])->name('users.edit.reveal-password');

        // EditAmount.aspx - not ported yet in this pass. Route exists now
        // so the list page's Update Amount links don't 404.
        Route::get('users/{userId}/edit-amount', [AdminComingSoonController::class, 'show'])->name('users.edit-amount')->defaults('page', 'Edit Amount');

        Route::get('transactions', [AdminTransactionController::class, 'index'])->name('transactions');
        Route::get('transactions/export', [AdminTransactionController::class, 'export'])->name('transactions.export');

        Route::get('settlements', [AdminSettlementController::class, 'index'])->name('settlements');

        Route::get('advance-search', [AdminAdvanceSearchController::class, 'index'])->name('advance-search');
        Route::post('advance-search', [AdminAdvanceSearchController::class, 'store'])->name('advance-search.search');
    });
});
