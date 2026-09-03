<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\StoredProcedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The user-facing "Setting" page shown in the User-panel screenshots -
 * 5 tabs: IP Business Details, IP WhiteList, WebHook, API KEY, Help
 * Document.
 *
 * No ASP.NET source exists for this page (see USER_PANEL_ANALYSIS.md), but
 * every tab besides IP Business Details and Help Document is backed by an
 * already-converted, already user-scoped CRUD proc:
 * - IP WhiteList  -> USP_CRUD_IP_WHITELIST(flag 1=list/2=add/3=update/4=delete)
 *   against IpWhitelistMst (WhitelistIPId, WhitelistIP, UserID, Created_On,
 *   Upated_On [sic - typo in the real column name], Status).
 * - WebHook       -> USP_CRUD_WEB_HOOK(flag 1=read/3=update/4=clear)
 *   reading/writing UserMaster.Callback_URL / Payout_Url directly.
 * - API KEY       -> USP_CRUD_API_KEY(flag 1=read/2=regenerate) against
 *   UserMaster's ClientID/ClientSecret/Request*Key/Response*Key columns.
 *   The proc only WRITES whatever key values it's given - it doesn't
 *   generate them - so "Regenerate Key" generates new random values here
 *   before calling flag=2, matching the "twikepay_live_..." ClientID format
 *   seen in the screenshot.
 *
 * IP Business Details has no dedicated proc anywhere in the SQL export -
 * every field shown (signatory name, business name/status/type/category,
 * WebApp URL, PAN, GST, address, pincode, city, state, payin/payout tax
 * rate, payin/payout callback URL, real-time settlement flag, banking
 * info) is a plain column on UserMaster (plus Tax/Pay_Tax on
 * User_Tax_Mst) - read directly rather than invented as a proc call that
 * doesn't exist. Read-only, no edit form - the screenshot doesn't show one.
 *
 * Help Document is a static "Download Integration Document" button with no
 * dynamic data behind it - no actual file was provided, so it's a
 * placeholder until one is.
 */
class SettingController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');

        $business = DB::table('UserMaster')->where('UserId', $userId)->first();
        $tax = DB::table('User_Tax_Mst')->where('UserID', (string) $userId)->first();

        $whitelist = StoredProcedure::callWithOutputRows(
            'USP_CRUD_IP_WHITELIST',
            ['@flag' => 1, '@WhitelistIPId' => 0, '@UserID' => $userId, '@WhitelistIP' => ''],
            '@loginStatus'
        )['rows'];

        $webhook = StoredProcedure::callWithOutputRows(
            'USP_CRUD_WEB_HOOK',
            ['@flag' => 1, '@UserID' => $userId, '@Callback_URL' => '', '@Payout_Url' => ''],
            '@loginStatus'
        )['rows'][0] ?? null;

        $apiKey = StoredProcedure::callWithOutputRows(
            'USP_CRUD_API_KEY',
            [
                '@flag' => 1, '@UserID' => $userId, '@ClientID' => '', '@ClientSecret' => '',
                '@RequestHashKey' => '', '@RequestSaltKey' => '', '@RequestAESKey' => '',
                '@ResponseHashKey' => '', '@ResponseSaltKey' => '', '@ResponseAESKey' => '',
            ],
            '@loginStatus'
        )['rows'][0] ?? null;

        $revealedKeys = $request->session()->pull('user.setting.revealed_keys');

        return view('user.setting.index', [
            'business' => $business,
            'tax' => $tax,
            'whitelist' => $whitelist,
            'webhook' => $webhook,
            'apiKey' => $apiKey,
            'revealedKeys' => $revealedKeys,
        ]);
    }

    public function addIp(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');
        $ip = trim((string) $request->input('whitelist_ip', ''));

        if ($ip === '') {
            return back()->with('error', 'Enter an IP address to add.');
        }

        $result = StoredProcedure::callWithOutputRows(
            'USP_CRUD_IP_WHITELIST',
            ['@flag' => 2, '@WhitelistIPId' => 0, '@UserID' => $userId, '@WhitelistIP' => $ip],
            '@loginStatus'
        );

        return match ((int) $result['output']) {
            1 => back()->with('status', 'IP address added.'),
            4 => back()->with('error', 'That IP address is already whitelisted.'),
            default => back()->with('error', 'Could not add the IP address. Please try again.'),
        };
    }

    public function deleteIp(Request $request, int $whitelistIpId)
    {
        $userId = (int) $request->session()->get('user.id');

        $result = StoredProcedure::callWithOutputRows(
            'USP_CRUD_IP_WHITELIST',
            ['@flag' => 4, '@WhitelistIPId' => $whitelistIpId, '@UserID' => $userId, '@WhitelistIP' => ''],
            '@loginStatus'
        );

        if ((int) $result['output'] === 1) {
            return back()->with('status', 'IP address removed.');
        }

        return back()->with('error', 'Could not remove the IP address. Please try again.');
    }

    public function updateWebhook(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');

        $result = StoredProcedure::callWithOutputRows(
            'USP_CRUD_WEB_HOOK',
            [
                '@flag' => 3,
                '@UserID' => $userId,
                '@Callback_URL' => (string) $request->input('payin_callback', ''),
                '@Payout_Url' => (string) $request->input('payout_callback', ''),
            ],
            '@loginStatus'
        );

        if ((int) $result['output'] === 1) {
            return back()->with('status', 'Webhook updated.');
        }

        return back()->with('error', 'Could not update the webhook. Please try again.');
    }

    /**
     * Port note: the proc only stores whatever key values it's handed - the
     * "generate a new secret" step is necessarily new code (nothing to port
     * from, since no source exists), not a translation of existing logic.
     */
    public function regenerateApiKey(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');

        $keys = [
            'ClientID' => 'twikepay_live_'.Str::random(16),
            'ClientSecret' => Str::random(40),
            'RequestHashKey' => Str::random(32),
            'RequestSaltKey' => Str::random(16),
            'RequestAESKey' => Str::random(32),
            'ResponseHashKey' => Str::random(32),
            'ResponseSaltKey' => Str::random(16),
            'ResponseAESKey' => Str::random(32),
        ];

        $result = StoredProcedure::callWithOutputRows(
            'USP_CRUD_API_KEY',
            [
                '@flag' => 2, '@UserID' => $userId,
                '@ClientID' => $keys['ClientID'], '@ClientSecret' => $keys['ClientSecret'],
                '@RequestHashKey' => $keys['RequestHashKey'], '@RequestSaltKey' => $keys['RequestSaltKey'],
                '@RequestAESKey' => $keys['RequestAESKey'], '@ResponseHashKey' => $keys['ResponseHashKey'],
                '@ResponseSaltKey' => $keys['ResponseSaltKey'], '@ResponseAESKey' => $keys['ResponseAESKey'],
            ],
            '@loginStatus'
        );

        if ((int) $result['output'] !== 1) {
            return back()->with('error', 'Could not regenerate the API key. Please try again.');
        }

        return redirect()->route('user.setting')
            ->with('status', 'API key regenerated.')
            ->with('user.setting.revealed_keys', $keys);
    }

    /** "View Keys" - reveal-once, same pattern as admin's password reveal. */
    public function viewKeys(Request $request)
    {
        $userId = (int) $request->session()->get('user.id');

        $result = StoredProcedure::callWithOutputRows(
            'USP_CRUD_API_KEY',
            [
                '@flag' => 1, '@UserID' => $userId, '@ClientID' => '', '@ClientSecret' => '',
                '@RequestHashKey' => '', '@RequestSaltKey' => '', '@RequestAESKey' => '',
                '@ResponseHashKey' => '', '@ResponseSaltKey' => '', '@ResponseAESKey' => '',
            ],
            '@loginStatus'
        );

        $row = $result['rows'][0] ?? null;

        if (! $row) {
            return back()->with('error', 'Could not load your API keys.');
        }

        return redirect()->route('user.setting')
            ->with('user.setting.revealed_keys', (array) $row);
    }
}
