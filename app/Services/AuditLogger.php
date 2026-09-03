<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Port of PortalApp.Classes.Logger.WriteLog(username, status), which calls
 * the "sp_AditTrail" stored procedure with the same 5 fields on every
 * failed login attempt. Same proc, same fields, called from PHP instead of C#.
 */
class AuditLogger
{
    public static function write(Request $request, string $username, string $loginLogoutStatus): void
    {
        try {
            StoredProcedure::call('sp_AditTrail', [
                '@Username' => $username,
                '@IPAddress' => $request->ip() ?? '',
                '@LoginLogoutStatus' => $loginLogoutStatus,
                '@Referrer' => $request->headers->get('referer', ''),
                '@UserAgent' => $request->userAgent() ?? '',
            ]);
        } catch (\Throwable $e) {
            // Same as the original: swallow logging failures, don't block the request.
            report($e);
        }
    }
}
