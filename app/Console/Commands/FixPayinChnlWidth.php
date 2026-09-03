<?php

namespace App\Console\Commands;

use App\Services\StoredProcedure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Manual entry point for the same fix UserController::store() and
 * EditUserController::update() now apply automatically the moment they hit
 * the "1406 Data too long for column 'v_Payin_Chnl'" error (see
 * StoredProcedure::fixPayinChnlWidth() and MIGRATION_NOTES.md, "Payin_Chnl
 * width fix, round 5"). Kept as a standalone command for two reasons:
 * - It's still the right thing to run once up front, so the error never
 *   happens in the app at all (the self-heal-and-retry adds one extra
 *   round-trip the first time it fires; running this first avoids that).
 * - If the DB user lacks ALTER / CREATE ROUTINE privilege, the in-app
 *   self-heal will fail silently into the normal error message - running
 *   this command by hand shows exactly which step failed and why.
 *
 * Usage: php artisan twike:fix-payin-chnl-width
 * 
 * Safe to run more than once - every step in
 * StoredProcedure::fixPayinChnlWidth() is idempotent.
 */
class FixPayinChnlWidth extends Command
{
    protected $signature = 'twike:fix-payin-chnl-width';

    protected $description = 'Widen UserMaster.Payin_Chnl and the USP_ADD_USER/USP_UPDATE_USER v_Payin_Chnl parameter to VARCHAR(20), fixing the recurring 1406 truncation error on Add/Edit User.';

    public function handle(): int
    {
        $this->info('Fixing Payin_Chnl width...');

        try {
            StoredProcedure::fixPayinChnlWidth();
            $this->line('  [OK] ran ALTER TABLE + re-created both procedures');
        } catch (\Throwable $e) {
            $this->error('  [FAILED] '.$e->getMessage());
        }

        $this->newLine();
        $this->info('Verifying...');
        $rows = DB::select(<<<'SQL'
            SELECT 'UserMaster.Payin_Chnl (table column)' AS `what`, CHARACTER_MAXIMUM_LENGTH AS `width`
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'UserMaster' AND COLUMN_NAME = 'Payin_Chnl'
            UNION ALL
            SELECT CONCAT(SPECIFIC_NAME, '.v_Payin_Chnl (procedure param)'), CHARACTER_MAXIMUM_LENGTH
            FROM INFORMATION_SCHEMA.PARAMETERS
            WHERE SPECIFIC_SCHEMA = DATABASE() AND PARAMETER_NAME = 'v_Payin_Chnl'
              AND SPECIFIC_NAME IN ('USP_ADD_USER', 'USP_UPDATE_USER')
            SQL);

        $this->table(['what', 'width'], array_map(fn ($r) => (array) $r, $rows));

        $allTwenty = count($rows) === 3 && collect($rows)->every(fn ($r) => (int) $r->width === 20);

        if ($allTwenty) {
            $this->newLine();
            $this->info('Done - all three show width 20. Add/Edit User should work now, and the app will no longer need to self-heal on this.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->error('Something did not apply cleanly - see the table above and any error printed. Common cause: the database user in .env (DB_USERNAME) lacks ALTER / CREATE ROUTINE privilege on this database.');

        return self::FAILURE;
    }
}
