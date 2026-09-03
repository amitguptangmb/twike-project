# User (Partner) Panel — Screenshot Analysis + Conversion Plan

Written from: 16 screenshots of the live app at `localhost:51063/User/*.aspx`, plus a
full source audit of `/mnt/user-data/uploads/ReportProject/PortalApp_02_09_2023/PortalApp_02_09_2023/partner/`
(the actual ASP.NET folder behind the `/User/` URLs), the already-ported
`app/Http/Controllers/Partner/*` + `resources/views/partner/*`, and every
plausibly-related stored procedure in `twike_mysql_procs.sql` +
`UserMaster`'s schema.

## Read this first: "convert without any change" is only possible for part of this

Of the 8 sidebar sections in your screenshots (Dashboard, Advance Search, Pay In,
Pay Out, Settlement, Reports, Setting, My Account), **the uploaded ASP.NET source
only has real `.aspx`/`.aspx.cs` code for 4 of them** — and 2 of those 4 no longer
match what the live screenshots show. Specifically:

- **No ASP.NET source exists anywhere in the upload** for: Advance Search (partner
  side), Settlement, Setting (all 5 tabs), Reports, Dashboard, or 3 of My Account's
  4 tabs (Account Info, Message, Support — only Change Password has source). I
  searched the whole uploads tree case-insensitively for every plausible filename;
  nothing turned up outside the *admin* folder, which is a different role's pages.
- **Pay Out and Collection have source, but the live screenshots show materially
  more UI than that source contains.** `TransactionList.aspx`/`payOut.aspx` and
  `collection.aspx` have no KPI tiles and no date-range filter on Collection at
  all (it's hardcoded to today only) — but your screenshots show 5 KPI tiles on
  Pay Out and 6 on Collection, plus a working date filter on Collection. The app
  has clearly grown past this source export.
- **The nav itself has changed.** `agentMaster.Master` (the real current layout
  file) has exactly 4 links: User List, Pay In, Pay Out, Change Password. Your
  screenshots show 8: Dashboard, Advance Search, Pay In, Pay Out, Settlement,
  Reports, Setting, My Account — no "User List" at all, and Change Password has
  moved from a standalone link into a tab under My Account.

None of this is a reason to stop — it just means two genuinely different kinds of
work are being asked for here, and they carry different risk:

1. **Faithful port** (User List, Pay In/Collection, Pay Out/Transactions, Change
   Password): line-for-line from real `.aspx.cs`, same as every other page done
   so far. Already done for 3 of these 4 in Laravel; low risk.
2. **Screenshot + proc reconstruction** (Dashboard, Advance Search, Settlement,
   Setting, most of My Account, the KPI tiles on Pay Out/Collection): there is no
   source to "not change" — I'd be building new controller/view code from what
   the screenshot shows, wired to whichever already-converted stored procedure
   looks like the right shape. That's a reasonable way to get you a working page,
   but it is **not** the same guarantee as the rest of this migration, and for a
   couple of pages I found more than one plausible proc with no way to tell which
   one the live page actually calls without you confirming.

I'm flagging this now, before writing any code, rather than quietly treating a
guess as a "no change" port.

## Per-screen findings

### Dashboard

**Screenshot**: 4 top tiles (PAY IN, PAYOUT, CHARGE BACK, HOLDING AMT.), a
From/To date filter + Search, then 3 cards — Payin Revenue (Total Volume, Total
Fees, Total Tax, Success Ratio), Payin Volume (Total Transactions, Success,
Failed, Pending, Cancelled), Payout Volume (same 5 sub-metrics).

**Source**: none. `userlist.aspx` — the page that's actually first in the real
nav and that the current Laravel `partner.dashboard` route points at — is a
*plain user list* (UserId, Name, Payout Amount, Collection Amount, Status), not
a KPI dashboard. There's no overlap between what's live at `/User/Dashboard.aspx`
in your screenshots and what `userlist.aspx` renders.

**Best-fit proc**: `Admin_Dashbaord(v_fromdate, v_todate, v_UserID)` and
`Admin_Dashbaord_payout(v_fromdate, v_todate, v_UserID)` — group
`MONEY_TRANSFER_PAYIN`/`MONEY_TRANSFER_PAYOUT` totals by `ModOfPayment`,
status filtered to `processed`/`SUCCESS`, and accept an optional `v_UserID`.
These are the only KPI-shaped procs in the whole file, but they read as
*admin* procs (built to run with `v_UserID=0` for "all users") that happen to
also accept a single-user filter — I have not confirmed the WHERE clause
actually scopes correctly to one partner's own transactions when `v_UserID`
is set to that partner's own ID rather than 0. **This needs verifying against
real data before it ships**, not assumed from the proc name alone.

**Naming collision to resolve**: the current route name `partner.dashboard`
already means "User List" in the shipped code (`DashboardController`,
`resources/views/partner/dashboard/index.blade.php`). If this new KPI page
becomes the new `partner.dashboard`, the existing controller/view need
renaming (e.g. to `partner.users`) — and since "User List" isn't in your
screenshots' sidebar at all anymore, I'd propose dropping it from the nav
entirely (route stays reachable, just unlinked) rather than guessing where
else it belongs. **Your call before I touch routing.**

### Advance Search (partner side)

**Screenshot**: identical layout to the admin Advance Search I already built —
Transaction ID/UTR fields, Payin/Payout radio, Search button, results panel.

**Source**: none (no `partner/AdvanceSearch.aspx` anywhere).

**Proc**: `USP_USER_PAYOUT(v_trFlag, v_txtTrans, v_txtUTR, v_userID)` — this is
the clean partner-scoped sibling of the admin's `USP_USER_PAYOUT_ADMIN`, same
branching logic (trFlag picks Payin/Payout source table, txtTrans-vs-UTR
priority, same "blank becomes unreachable UTR branch" shape worth checking
against — need to confirm whether this partner UI has the same "blank sent as
literal '0'" quirk or was actually fixed here, since there's no code-behind to
tell me either way). High confidence on the proc match; no callback/"Sent
Callback" button visible in this screenshot the way the admin page has one, so
that piece likely doesn't apply here — worth confirming since I can't verify
a missing feature from a screenshot alone.

### Pay In (Collection)

**Screenshot** (2 variants): a KPI-tile version (TOTAL GTV, TOTAL NO OF
SUCCESS, AMOUNT REFUNDED, CHARGEBACK AMOUNT, SETTLEMENT AMOUNT, FEE & TAX)
with a working From/To filter, and a plain Report(Daywise) DataTable (S No,
Txn.Date, Txn Updation Date, TRANSACTION ID, Ref ID, UTR, CR, SETTLEMENT,
Status, Balance) with CSV/Excel/PDF export and 139 total rows across 14 pages.

**Source**: `collection.aspx`/`.cs` — **already ported** as
`Partner\CollectionController` + `partner/collection/index.blade.php`, calling
`USP_TRANS_PAY_IN_AGENT(@CREATED_BY, @fromdate, @todate, @txtTrans)`. But the
shipped port hardcodes `fromdate=todate=today` with no filter UI and no KPI
tiles at all — matching the *source*, not what's live today. The date filter
and 6 KPI tiles are new UI with no backing source; the KPI numbers would need
to be computed (client-side from the fetched rows, or via a new aggregate
query) rather than copied from any existing proc I found.

### Pay Out

**Screenshot**: From/To/Status filter + Search/Download, 5 KPI tiles (TOTAL
AMOUNT, NUMBER OF PAYOUT TRANSACTION, TOTAL SUCCESS TRANSACTION, TOTAL FAILED
TRANSACTION, CHARGE AMOUNT OR FEE AMOUNT), then a Report(Daywise) DataTable:
S No, Txn.Date, Txn Updation Date, REF ID, USER ORDER ID, UTR, DR, Recharge,
FEE, Balance, Status.

**Source**: `TransactionList.aspx`/`.cs` — **already ported** as
`Partner\TransactionController`, calling `USP_TRANSACTION_LIST_ForAdmin`. But
that proc's column set (CREATED_ON, TRANSACTIONID, ReferenceId, HolderName,
BankName, AccountNo, IFSC, AMOUNT, TAX, TAX_AMOUNT, TOTAL_AMOUNT, UTR, STATUS,
servicename, ModOfPayment, CREATED_BY) **doesn't match** the screenshot's
columns (REF ID, USER ORDER ID, UTR, DR, Recharge, FEE, Balance, Status) —
those match `USP_USER_PAYOUT`'s output shape instead (the same proc Advance
Search uses, with `v_trFlag=2`). **This is the most important finding in this
whole analysis**: the currently-shipped Partner Transactions/Pay Out page may
be built against the wrong proc for what's actually live today. Worth
confirming directly rather than me silently swapping it, since
`USP_TRANSACTION_LIST_ForAdmin` is also used by the admin Transactions page I
already built and shares logic with it — a change here doesn't touch that.

### Settlement

**Screenshot**: Report(Daywise) DataTable, columns S No, Request Date,
TRANSACTION ID, AMOUNT, Reference Id, STATUS, ModOfPayment — 2 rows in the
example, both SUCCESS/Settlement.

**Source**: none (no `partner/Settlement.aspx`).

**Proc**: `USP_REQUEST_SETTLEMENT_LIST(v_CREATED_BY)` — **exact column match**
(TRANSACTIONID, AMOUNT, STATUS, CREATED_ON→Requestdate, PortalName→servicename,
ModOfPayment, user_order_id→ReferenceId), already partner-scoped, capped at 50
rows in the proc itself. High confidence this is the right proc. No "Settle
Now" action button is visible in the screenshot — `SettlementtoWallet(v_userID)`
exists as an already-converted action proc (moves Collection_Amount into
Available_Amount) but nothing in the screenshots shows a trigger for it, so I
won't wire it in speculatively.

### Setting (5 tabs)

**Screenshot**: IP Business Details (long read-only dump of Business Info /
Transaction Info / Banking Info — signatory name, business name/status/type/
category/sub-category, WebApp URL, PAN, GST, address, pincode, city, state,
payin/payout tax rate, payin/payout callback URL, real-time settlement
on/off, account holder name, bank name...), IP WhiteList (Add IP Address
button; table S No/Whitelist IP/Creation Datetime/Delete), WebHook (Edit
Webhook button; table Tag/Callback/Creation Time, rows Payin/Payout), API KEY
(table Client ID / Regenerate Key / View Keys), Help Document (static
"Download Integration Document" button, no dynamic data).

**Source**: none (no `partner/Setting.aspx`).

**Procs — all found, all already partner-scoped, high confidence**:
- IP Business Details → plain columns off `UserMaster` (no dedicated proc
  found; likely a direct `SELECT * FROM UserMaster WHERE UserId = ?` rather
  than a stored proc — every field shown maps 1:1 to a real `UserMaster`
  column per the schema, see below).
- IP WhiteList → `USP_CRUD_IP_WHITELIST(v_flag, v_WhitelistIPId, v_UserID, v_WhitelistIP, OUT v_loginStatus)`
  (flag 1=list, 2=add, 3=update, 4=delete) against a separate `IpWhitelistMst`
  table.
- WebHook → `USP_CRUD_WEB_HOOK(v_flag, v_UserID, v_Callback_URL, v_Payout_Url, OUT v_loginStatus)`
  (flag 1=read, 3=update, 4=clear), reading/writing `UserMaster.Callback_URL`/
  `Payout_Url` directly.
- API KEY → `USP_CRUD_API_KEY(v_flag, v_UserID, v_ClientID, v_ClientSecret, ...6 more key fields, OUT v_loginStatus)`
  (flag 1=read, 2=regenerate/update) against `UserMaster`'s `ClientID`/
  `ClientSecret`/`Request*Key`/`Response*Key` columns.
- Help Document → static asset, no proc. Needs an actual PDF/doc file to link
  to, which I don't have — placeholder until you provide one.

### My Account (4 tabs)

**Screenshot**: Account Info (Name, Business Name, Email ID, Mobile Number,
Submit), Change Password (User ID readonly, Old/New/Confirm Password,
Submit), Message (not shown open in any screenshot), Support (static
"Email:COINNECTED@gmail.com").

**Source**: only Change Password has real source (`changepassword.aspx.cs`,
already ported as `Partner\PasswordController`). Account Info, Message, and
Support have none.

**Procs**: Account Info's 4 fields are again plain `UserMaster` columns
(`Name`, `BusinessName`, `EmailId`, `Mobile`) — no dedicated "get my profile"
proc found, and no "update my profile" proc either (the closest, `GET_USER_BY_ID`/
`Update_USER_BY_ID`, are admin-side procs I haven't confirmed are safe/scoped
for a partner to call on themselves). Message tab: completely unknown — no
screenshot of its content, no source, no obviously-matching proc name. I'd
need either a screenshot of it open or your description before building it.

### Reports

No screenshot shows this page open (it's only visible as a sidebar link), no
source, and I don't have enough information to plan it at all yet.

## Proc summary table

| Page | Proc | Scope confirmed? | Column match confirmed? |
|---|---|---|---|
| Advance Search | `USP_USER_PAYOUT` | Yes (`v_userID` param) | Yes (mirrors admin's `USP_USER_PAYOUT_ADMIN`) |
| Pay In / Collection (existing behavior) | `USP_TRANS_PAY_IN_AGENT` | Yes, already shipped | Yes, already shipped |
| Pay In / Collection (KPI tiles + date filter seen live) | none found | — | — |
| Pay Out (existing shipped port) | `USP_TRANSACTION_LIST_ForAdmin` | Yes, already shipped | **No — columns don't match the live screenshot** |
| Pay Out (columns actually shown live) | `USP_USER_PAYOUT` (v_trFlag=2) | Yes | Yes |
| Pay Out KPI tiles | none found | — | — |
| Settlement | `USP_REQUEST_SETTLEMENT_LIST` | Yes | Yes, exact match |
| Setting → IP Business Details | none (direct `UserMaster` read, proposed) | — | Columns all exist on `UserMaster` |
| Setting → IP WhiteList | `USP_CRUD_IP_WHITELIST` | Yes | Yes |
| Setting → WebHook | `USP_CRUD_WEB_HOOK` | Yes | Yes |
| Setting → API KEY | `USP_CRUD_API_KEY` | Yes | Yes |
| Setting → Help Document | none (static asset) | — | — |
| My Account → Account Info | none (direct `UserMaster` read/write, proposed) | — | Columns all exist on `UserMaster` |
| My Account → Change Password | `USP_AGENT_CHANGE_PASSWORD` | Yes, already shipped | Yes, already shipped |
| My Account → Message | unknown | — | — |
| My Account → Support | none (static text) | — | — |
| Dashboard | `Admin_Dashbaord` / `Admin_Dashbaord_payout` | **Not confirmed for single-partner scope** | Plausible, unverified |
| Reports | unknown | — | — |

## Velzon styling

Per your instruction, this is the one deliberate change across every page in
this batch, faithful-port or reconstructed alike: same Velzon visual system
already applied to the admin portal — `.np-card` panels, the Inter font
stack, DataTables with the Buttons extension (csv/excel/pdf) for the daywise
report tables, Remix icons, the `#405189` sidebar. The partner layout
(`resources/views/layouts/partner.blade.php`, if that's the right file —
worth confirming it exists and matches the admin layout's conventions before
I start, since I haven't audited it in this pass) should get the same
treatment already applied to `layouts/admin.blade.php`.

## Before I start building, I need from you

1. **Dashboard**: confirm `Admin_Dashbaord`/`Admin_Dashbaord_payout` actually
   scope correctly to one partner when given their own `UserID` (vs. always
   meaning "all users"), or tell me another proc name to check.
2. **Nav restructure**: OK to drop "User List" from the partner nav (route
   stays alive, just unlinked) since it's not in your screenshots' sidebar,
   and move Change Password out of the top-level nav into the My Account
   tabs?
3. **Pay Out proc mismatch**: should I switch the live Pay Out page to
   `USP_USER_PAYOUT` (matching what's actually on screen) and leave the
   existing `USP_TRANSACTION_LIST_ForAdmin`-based code alone/unused, or is
   there a reason it's wired the way it currently is that I'm missing?
4. **KPI tiles** (Dashboard, Pay Out, Collection): compute client-side from
   the fetched rows, or should these hit a real aggregate query? I don't have
   a proc that produces them directly for the Collection/Pay Out cases.
5. **My Account → Message tab**: no screenshot of its content — need one, or
   a description of what it should show.
6. **Reports page**: no screenshot at all yet — need one before I can plan it.
7. **Help Document**: need the actual integration-document file to link, or
   should the button stay a placeholder for now?

Once these are answered I'll write the controllers/views/routes the same way
I've done for every other page so far — faithful port where source exists,
clearly-labeled reconstruction where it doesn't, Velzon throughout.
