<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDO;

/**
 * Thin wrapper around the converted MySQL stored procedures (originally SQL
 * Server, ported per MIGRATION_NOTES.md - see the "MySQL conversion" section
 * there for exactly what was and wasn't verified).
 *
 * Deliberate design choice for this migration: business logic stays in
 * stored procedures (USP_*, Get*, sp_AditTrail, forgotPasswordCode,
 * forgotPasswordUpdate, ...). Laravel never re-implements that logic in PHP
 * and never runs migrations against this database - it only calls the
 * converted procedures.
 *
 * IMPORTANT DIFFERENCE FROM THE SQL SERVER VERSION OF THIS FILE:
 * SQL Server's `EXEC proc @Param = value, ...` accepts named parameters in
 * any order. MySQL's `CALL proc(?, ?, ...)` is strictly positional - it has
 * no named-parameter syntax at all. Every caller in this codebase still
 * passes an associative ['@Param' => value] array (unchanged, so no
 * controller needed editing), so this class looks up each procedure's real
 * parameter order from INFORMATION_SCHEMA.PARAMETERS at call time and
 * reorders the values to match before building the positional CALL.
 */
class StoredProcedure
{
    /**
     * Call a stored procedure that returns one result set and no OUTPUT
     * parameters. Equivalent to the old SqlCommand + SqlDataAdapter.Fill(dt)
     * pattern used throughout the .aspx.cs files.
     *
     * @param  string  $name  Stored procedure name, e.g. "Get_Agent_UserList".
     * @param  array<string, mixed>  $params  ['@AgentID' => 5, '@fromdate' => '2026-08-19']
     * @return array<int, object> Rows as stdClass objects (like DB::select()).
     */
    public static function call(string $name, array $params = []): array
    {
        [$sql, $bindings] = self::buildPositionalCall($name, $params);

        return DB::connection()->select($sql, $bindings);
    }

    /**
     * Same as call(), but returns rows as plain numeric arrays (fetch by
     * ordinal position) instead of objects. A couple of the legacy pages
     * (e.g. ForgotPassword's forgotPasswordCode) read the result set by
     * column index rather than name, so we preserve that here rather than
     * guessing column names we were never given.
     *
     * @return array<int, array<int, mixed>>
     */
    public static function callIndexed(string $name, array $params = []): array
    {
        [$sql, $bindings] = self::buildPositionalCall($name, $params);

        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        // Drain any further result sets MySQL's CALL semantics leave queued
        // up (a CALL always appends its own "status" result set behind the
        // real one) so the connection isn't left in an unusable state for
        // the next query on it.
        while ($stmt->nextRowset()) {
            //
        }

        return $rows;
    }

    /**
     * Call a stored procedure that returns MORE THAN ONE result set (e.g.
     * USP_GROUP_Payin_Revenue, which does three separate SELECTs in one
     * proc body) and no OUTPUT parameters. call()/DB::connection()->select()
     * only ever surfaces the first result set, so this exists specifically
     * for that case - mirrors the old code's `DataSet` + multiple
     * `ds.Tables[N]` pattern.
     *
     * @param  array<string, mixed>  $params
     * @return array<int, array<int, object>> One array of stdClass rows per result set, in order.
     */
    public static function callMultiRowset(string $name, array $params = []): array
    {
        [$sql, $bindings] = self::buildPositionalCall($name, $params);

        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);

        $rowsets = [];
        do {
            $rowsets[] = $stmt->fetchAll(PDO::FETCH_OBJ);
        } while ($stmt->nextRowset());

        return $rowsets;
    }

    /**
     * Call a stored procedure that has a single scalar OUT parameter,
     * mirroring the old code's:
     *   cmd.Parameters.Add("@loginStatus", SqlDbType.Int, 100);
     *   cmd.Parameters["@loginStatus"].Direction = ParameterDirection.Output;
     *
     * PDO_MYSQL has no bound-OUTPUT-parameter support on CALL the way
     * PDO_SQLSRV does, so this uses MySQL's own session-variable pattern
     * instead: CALL proc(?, ?, @out_var); SELECT @out_var;
     *
     * @param  array<string, mixed>  $params  Regular (non-output) input params.
     * @return int|string|null The value bound to the OUT parameter after execution.
     */
    public static function callWithOutput(string $name, array $params, string $outputParam, int $pdoType = PDO::PARAM_INT, int $length = 100): mixed
    {
        $outNameNorm = self::normalizeParamName($outputParam);
        // Prefixed so it can't collide with a same-named session variable
        // from an unrelated call earlier in the request.
        $sessionVar = '@sp_out_' . preg_replace('/[^A-Za-z0-9_]/', '', $outNameNorm);

        $order = self::paramOrder($name);

        if (empty($order)) {
            // INFORMATION_SCHEMA lookup found nothing for this procedure
            // (most likely it isn't in the converted export at all - see
            // MIGRATION_NOTES.md's "not ported" list). Fail loudly rather
            // than silently sending a malformed CALL.
            throw new \RuntimeException("StoredProcedure::callWithOutput(\"{$name}\"): procedure not found in the database (it may not have been included in the MySQL conversion - see MIGRATION_NOTES.md).");
        }

        $placeholders = [];
        $bindings = [];

        foreach ($order as $p) {
            if (self::normalizeParamName($p->PARAMETER_NAME) === $outNameNorm) {
                $placeholders[] = $sessionVar;

                continue;
            }

            $placeholders[] = '?';
            $bindings[] = self::lookupParam($params, $p->PARAMETER_NAME, $name);
        }

        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL ' . $name . ' (' . implode(', ', $placeholders) . ')');
        $stmt->execute($bindings);

        // Drain the result set(s) the CALL itself produced before issuing
        // the SELECT below on the same connection - MySQL will otherwise
        // reject it with "Cannot execute queries while other unbuffered
        // queries are active".
        while ($stmt->nextRowset()) {
            //
        }

        $row = DB::connection()->selectOne("SELECT {$sessionVar} AS value");

        return $row->value ?? null;
    }

    /**
     * Like callWithOutput(), but for procedures that ALSO produce a real
     * SELECT result set on at least one branch (the partner Setting page's
     * "read my WebHook/API Key/IP Whitelist" procs: USP_CRUD_WEB_HOOK,
     * USP_CRUD_API_KEY, USP_CRUD_IP_WHITELIST - all `v_flag`-branched CRUD
     * procs where flag=1 SELECTs rows and flag=2/3/4 write and only set the
     * OUT status). callWithOutput() alone can't be used for these: it drains
     * every rowset before reading the OUT value, so the flag=1 SELECT's rows
     * would be silently discarded.
     *
     * @param  array<string, mixed>  $params
     * @return array{rows: array<int, object>, output: int|string|null}
     */
    public static function callWithOutputRows(string $name, array $params, string $outputParam, int $pdoType = PDO::PARAM_INT, int $length = 100): array
    {
        $outNameNorm = self::normalizeParamName($outputParam);
        $sessionVar = '@sp_out_' . preg_replace('/[^A-Za-z0-9_]/', '', $outNameNorm);

        $order = self::paramOrder($name);

        if (empty($order)) {
            throw new \RuntimeException("StoredProcedure::callWithOutputRows(\"{$name}\"): procedure not found in the database (it may not have been included in the MySQL conversion - see MIGRATION_NOTES.md).");
        }

        $placeholders = [];
        $bindings = [];

        foreach ($order as $p) {
            if (self::normalizeParamName($p->PARAMETER_NAME) === $outNameNorm) {
                $placeholders[] = $sessionVar;

                continue;
            }

            $placeholders[] = '?';
            $bindings[] = self::lookupParam($params, $p->PARAMETER_NAME, $name);
        }

        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL ' . $name . ' (' . implode(', ', $placeholders) . ')');
        $stmt->execute($bindings);

        // Grab whatever the FIRST rowset is (the flag=1 SELECT, when this
        // branch produced one - an empty array otherwise) before draining
        // the rest, same "drain before the follow-up SELECT" reasoning as
        // callWithOutput().
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

        while ($stmt->nextRowset()) {
            //
        }

        $row = DB::connection()->selectOne("SELECT {$sessionVar} AS value");

        return ['rows' => $rows, 'output' => $row->value ?? null];
    }

    /**
     * SQL Server's EncryptByPassPhrase has no MySQL/MariaDB equivalent, and
     * porting its (undocumented, proprietary) key-derivation algorithm to
     * PHP was out of scope for this migration. Per the explicit decision to
     * leave passwords as plain text for now, this returns the cleartext
     * unchanged rather than pretending to encrypt it - do not treat this
     * value as encrypted anywhere downstream.
     *
     * The only caller of this method (partner forgot-password reset) also
     * calls the "forgotPasswordUpdate" stored procedure immediately after,
     * which was NOT included in the SQL export this migration was given -
     * that whole reset flow is non-functional until forgotPasswordUpdate
     * (and forgotPasswordCode, used earlier in the same flow) are supplied
     * and ported. See MIGRATION_NOTES.md.
     */
    public static function encryptByPassPhrase(string $passphrase, string $cleartext): string
    {
        return $cleartext;
    }

    /**
     * Look up a stored procedure's declared parameters, in order, from
     * MySQL's own INFORMATION_SCHEMA - this is what makes it safe for every
     * existing call site to keep passing an associative ['@Param' => value]
     * array without caring what order MySQL actually wants them in.
     *
     * @return array<int, object{PARAMETER_NAME: string, PARAMETER_MODE: string, ORDINAL_POSITION: int}>
     */
    private static function paramOrder(string $name): array
    {
        static $cache = [];

        if (array_key_exists($name, $cache)) {
            return $cache[$name];
        }

        $rows = DB::connection()->select(
            'SELECT PARAMETER_NAME, PARAMETER_MODE, ORDINAL_POSITION '.
            'FROM INFORMATION_SCHEMA.PARAMETERS '.
            'WHERE SPECIFIC_SCHEMA = DATABASE() AND SPECIFIC_NAME = ? AND PARAMETER_NAME IS NOT NULL '.
            'ORDER BY ORDINAL_POSITION',
            [$name]
        );

        return $cache[$name] = $rows;
    }

    /**
     * Case-insensitive lookup of a parameter's value in the caller-supplied
     * ['@Param' => value] array, tolerant of naming differences between the
     * original SQL Server "@Param" style still used at every call site and
     * the converted procedures' MySQL-safe "v_Param" parameter names (the
     * T-SQL->MySQL converter renamed every "@param" to "v_param" since "@"
     * isn't a legal leading character for a MySQL routine parameter).
     */
    private static function lookupParam(array $params, string $paramName, string $procName): mixed
    {
        $target = self::normalizeParamName($paramName);

        foreach ($params as $key => $value) {
            if (self::normalizeParamName((string) $key) === $target) {
                return $value;
            }
        }

        throw new \RuntimeException("StoredProcedure: no value supplied for parameter \"{$paramName}\" of procedure \"{$procName}\".");
    }

    /**
     * Strips a leading "@" (SQL Server style, still used at every call site)
     * or "v_" (MySQL style, what the converter renamed parameters to) and
     * lowercases what's left, so both naming styles compare equal.
     */
    private static function normalizeParamName(string $name): string
    {
        $name = ltrim($name, '@');

        if (stripos($name, 'v_') === 0) {
            $name = substr($name, 2);
        }

        return strtolower($name);
    }

    /**
     * @return array{0: string, 1: array<int, mixed>}
     */
    private static function buildPositionalCall(string $name, array $params): array
    {
        $order = self::paramOrder($name);

        if (empty($order)) {
            // Procedure not found in INFORMATION_SCHEMA - most likely not
            // part of the converted export (see MIGRATION_NOTES.md's "not
            // ported" list). Fall back to the caller's array order as-is
            // rather than guessing; this will only work if that order
            // happens to already match the procedure's real signature.
            $sql = 'CALL '.$name.' ('.implode(', ', array_fill(0, count($params), '?')).')';

            return [$sql, array_values($params)];
        }

        $bindings = [];

        foreach ($order as $p) {
            if (strcasecmp($p->PARAMETER_MODE, 'OUT') === 0) {
                // call()/callIndexed() don't support OUT parameters - use
                // callWithOutput() for procedures that have one.
                continue;
            }

            $bindings[] = self::lookupParam($params, $p->PARAMETER_NAME, $name);
        }

        $sql = 'CALL '.$name.' ('.implode(', ', array_fill(0, count($bindings), '?')).')';

        return [$sql, $bindings];
    }

    /**
     * True when $e is specifically the "database still has the old,
     * VARCHAR(5) v_Payin_Chnl parameter" error (MySQL 1406, "Data too long
     * for column" on a v_-prefixed stored-procedure parameter). Factored
     * out of friendlyError() so callers can also use it to decide whether
     * to self-heal (see fixPayinChnlWidth() + its use in
     * UserController::store() / EditUserController::update()).
     */
    public static function isPayinChnlWidthError(\Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, '1406')
            && str_contains($message, 'Data too long for column')
            && str_contains($message, "'v_Payin_Chnl'");
    }

    /**
     * Widens UserMaster.Payin_Chnl and both USP_ADD_USER/USP_UPDATE_USER's
     * v_Payin_Chnl parameter to VARCHAR(20) - the actual fix for
     * isPayinChnlWidthError(), not just a message about it. Shared by:
     * - The `php artisan twike:fix-payin-chnl-width` command (manual,
     *   explicit run).
     * - The automatic self-heal-and-retry in UserController::store() /
     *   EditUserController::update() (see MIGRATION_NOTES.md, "Payin_Chnl
     *   width fix, round 5" - after four rounds of asking the user to run
     *   a fix by hand and the same error coming back each time, this
     *   makes the app fix its own database the moment it hits the error,
     *   with no separate step required).
     *
     * Idempotent - safe to call even when already fixed (MODIFY on an
     * already-correct column is a no-op; DROP IF EXISTS + CREATE always
     * leaves exactly one correct definition). Every statement runs through
     * PDO (via DB::statement()/DB::unprepared()), which sends each string
     * to the server as a single query - no DELIMITER trick needed, unlike
     * running the equivalent through the mysql CLI or phpMyAdmin.
     *
     * @throws \Throwable if any step fails (most likely cause: the DB user
     *   in .env lacks ALTER / CREATE ROUTINE privilege) - the caller
     *   decides what to do (the artisan command reports which step failed;
     *   the controllers fall back to friendlyError()'s message).
     */
    public static function fixPayinChnlWidth(): void
    {
        DB::statement('ALTER TABLE `UserMaster` MODIFY `Payin_Chnl` VARCHAR(20) NULL');

        DB::unprepared('DROP PROCEDURE IF EXISTS `USP_ADD_USER`');
        DB::unprepared(self::addUserProcedureSql());

        DB::unprepared('DROP PROCEDURE IF EXISTS `USP_UPDATE_USER`');
        DB::unprepared(self::updateUserProcedureSql());
    }

    /**
     * Turns a raw DB exception into something a non-technical admin can act
     * on, instead of dumping the full SQLSTATE + bound-value CALL string
     * onto the page (which is what every controller here used to flash
     * straight from $e->getMessage()).
     *
     * Currently only special-cases MySQL error 1406 ("Data too long for
     * column") on a v_-prefixed stored-procedure parameter - that specific
     * error means the database still has an old, narrower procedure
     * definition than what's in twike_mysql_procs.sql (see
     * MIGRATION_NOTES.md, "Payin_Chnl width fix"). As of round 5, the
     * controllers try fixPayinChnlWidth() + one retry BEFORE ever calling
     * this - so reaching this message at all means the self-heal itself
     * also failed (almost always a DB privilege problem), which is why the
     * message below points at the manual command as a fallback rather than
     * promising it'll just fix itself. Anything else falls back to
     * $fallback (or, if none was given, the original exception message)
     * unchanged - this is deliberately narrow, not a general error
     * "prettifier" that could end up hiding a real new problem.
     *
     * @param  string|null  $fallback  What to return when the exception isn't
     *   this specific known case. Pass null to keep returning the raw
     *   $e->getMessage() (matches the old behavior at call sites that used
     *   to flash it directly); pass a fixed string for call sites that
     *   intentionally never show raw DB errors to the admin.
     */
    public static function friendlyError(\Throwable $e, ?string $fallback = null): string
    {
        $message = $e->getMessage();
 
        if (self::isPayinChnlWidthError($e)) {
            return 'Save failed, and this app just tried to fix its own database automatically and could not '
                ."(most likely the database user in .env doesn't have ALTER / CREATE ROUTINE privilege). "
                .'Run `php artisan twike:fix-payin-chnl-width` from the project folder once, then try again. '
                ."(Raw error: {$message})";
        }

        // General case: ANY other MySQL 1406 "Data too long for column" on a
        // stored-procedure parameter - e.g. a value longer than the field's
        // real database width (Business_Type is VARCHAR(10), etc.). Unlike
        // isPayinChnlWidthError() this is NOT a known source-conversion bug
        // to auto-widen - it usually just means the value entered is too
        // long for that field, which is the database correctly rejecting
        // it. The Add/Edit User forms now have matching `maxlength`
        // attributes on every text field (see MIGRATION_NOTES.md,
        // "Payin_Chnl width fix, round 6") so this should rarely fire from
        // the UI itself anymore - this is the fallback for anything that
        // still gets through (pasted input, no-JS, a field this pass
        // missed), so it at least reads as a real message instead of a
        // wall of raw SQL.
        if (str_contains($message, '1406') && str_contains($message, 'Data too long for column') && preg_match("/column '(v_[A-Za-z0-9_]+)'/", $message, $m)) {
            $field = str_replace('v_', '', $m[1]);

            return "Save failed: the value entered for \"{$field}\" is longer than the database allows for that field. "
                .'Shorten it and try again. '
                ."(Raw error: {$message})";
        }

        if ($fallback !== null) {
            return $fallback;
        }

        return $message;
    }

    /**
     * @see fixPayinChnlWidth()
     */
    private static function addUserProcedureSql(): string
    {
        return <<<'SQL'
            CREATE PROCEDURE `USP_ADD_USER`(
                IN v_Name VARCHAR(200),
                IN v_BusinessName VARCHAR(200),
                IN v_Mobile VARCHAR(15),
                IN v_EmailId VARCHAR(100),
                IN v_Gender CHAR(1),
                IN v_Dob VARCHAR(50),
                IN v_PAN VARCHAR(10),
                IN v_Pincode VARCHAR(6),
                IN v_Address VARCHAR(200),
                IN v_City VARCHAR(100),
                IN v_State VARCHAR(100),
                IN v_GSTNo VARCHAR(16),
                IN v_Status SMALLINT,
                IN v_Password VARCHAR(20),
                IN v_SID VARCHAR(20),
                IN v_payout_flag INT,
                IN v_RTSettlementOn INT,
                IN v_Payin_Chnl VARCHAR(20),
                IN v_Payin_tax DECIMAL(18,2),
                IN v_Payout_tax DECIMAL(18,2),
                IN v_Business_Type VARCHAR(10),
                IN v_Business_Category VARCHAR(2000),
                IN v_Business_Sub_Category VARCHAR(2000),
                IN v_WebAppURL VARCHAR(2000),
                IN v_Account VARCHAR(50),
                IN v_ifsc VARCHAR(15),
                IN v_BankName VARCHAR(500),
                IN v_AccountHolderName VARCHAR(500),
                IN v_Payout_Chnl INT,
                IN v_AgentID INT,
                IN v_workingKey VARCHAR(500)
            )
            BEGIN
            BEGIN
              DECLARE v_lastinsertedID int;
              DECLARE EXIT HANDLER FOR SQLEXCEPTION
              BEGIN
                 select 'fail' as message,0 status;
              END;

             INSERT INTO `UserMaster`
                       (`Name`
                       ,`BusinessName`
                       ,`Mobile`
                       ,`EmailId`
                       ,`Gender`
                       ,`Dob`
                       ,`PAN`
                       ,`Pincode`
                       ,`Address`
                       ,`City`
                       ,`State`
                       ,`GSTNo`
                       ,`Status`
                       ,`CreatedOn`
                       ,`Password`
                       ,`Available_Amount`
                 ,`SID`
                  ,`payout_flag`
                 ,`RTSettlementOn`
                  ,`Payin_Chnl`
              , Business_Type,
             Business_Category,
             Business_Sub_Category,
             WebAppURL,
               Account,
            ifsc,
             BankName,
             AccountHolderName,
             PayoutServiceID  ,
             AgentID,CCAvenueWorkingKey

                 )
                 VALUES
                       (v_Name
                       ,v_BusinessName
                       ,v_Mobile
                       ,v_EmailId
                       ,v_Gender
                       ,v_Dob
                       ,v_PAN
                       ,v_Pincode
                       ,v_Address
                       ,v_City
                       ,v_State
                       ,v_GSTNo
                       ,v_Status
                       ,NOW()
                       ,v_Password
                       ,0
                  ,v_SID
             ,v_payout_flag
                ,v_RTSettlementOn ,
                 v_Payin_Chnl ,
             v_Business_Type,
             v_Business_Category,
             v_Business_Sub_Category,
             v_WebAppURL  ,
              v_Account,
            v_ifsc,
             v_BankName,
             v_AccountHolderName  ,
              v_Payout_Chnl ,
              v_AgentID  ,
              v_workingKey
                 )

             ;set v_lastinsertedID=(SELECT LAST_INSERT_ID());

            INSERT INTO `User_Tax_Mst`
                       (`Tax`
                       ,`UserID`
                       ,`Pay_Tax`)
                 VALUES
                       (v_Payin_tax
                       ,v_lastinsertedID,
                       v_Payout_tax);

                select 'success' as message,1 status;

            END;
            END
            SQL;
    }

    /**
     * @see fixPayinChnlWidth()
     */
    private static function updateUserProcedureSql(): string
    {
        return <<<'SQL'
            CREATE PROCEDURE `USP_UPDATE_USER`(
                IN v_UserID VARCHAR(200),
                IN v_Name VARCHAR(200),
                IN v_BusinessName VARCHAR(200),
                IN v_Mobile VARCHAR(15),
                IN v_EmailId VARCHAR(100),
                IN v_Gender CHAR(1),
                IN v_Dob VARCHAR(50),
                IN v_PAN VARCHAR(10),
                IN v_Pincode VARCHAR(6),
                IN v_Address VARCHAR(200),
                IN v_City VARCHAR(100),
                IN v_State VARCHAR(100),
                IN v_GSTNo VARCHAR(16),
                IN v_Status SMALLINT,
                IN v_Password VARCHAR(20),
                IN v_SID VARCHAR(20),
                IN v_CallbackPayin VARCHAR(500),
                IN v_CallbackPayOut VARCHAR(500),
                IN v_payout_flag INT,
                IN v_RTSettlementOn INT,
                IN v_Payin_Chnl VARCHAR(20),
                IN v_Payin_tax DECIMAL(18,2),
                IN v_Payout_tax DECIMAL(18,2),
                IN v_FailedCount INT,
                IN v_Business_Type VARCHAR(10),
                IN v_Business_Category VARCHAR(2000),
                IN v_Business_Sub_Category VARCHAR(2000),
                IN v_WebAppURL VARCHAR(2000),
                IN v_Account VARCHAR(50),
                IN v_ifsc VARCHAR(15),
                IN v_BankName VARCHAR(500),
                IN v_AccountHolderName VARCHAR(500),
                IN v_Payout_Chnl INT,
                IN v_AgentID INT,
                IN v_workingKey VARCHAR(500)
            )
            BEGIN
            BEGIN
              DECLARE v_lastinsertedID int;
              DECLARE EXIT HANDLER FOR SQLEXCEPTION
              BEGIN
                 select 'fail' as message,0 status;
              END;

            IF ((v_Password !='')) THEN
              UPDATE `UserMaster`
               SET `Name` = v_Name
                  ,`BusinessName` = v_BusinessName
                  ,`Mobile` = v_Mobile
                  ,`EmailId` = v_EmailId
                  ,`Gender` = v_Gender
                  ,`Dob` = v_Dob
                  ,`PAN` = v_PAN
                  ,`Pincode` = v_Pincode
                  ,`Address` = v_Address
                  ,`City` = v_City
                  ,`State` = v_State
                  ,`GSTNo` = v_GSTNo

                  ,`Password` = v_Password
                  ,`SID` = v_SID
               ,`Status` = v_Status
                  ,`payout_flag` = v_payout_flag
                  ,`Callback_URL` = v_CallbackPayin
                  ,`Payin_Chnl` = v_Payin_Chnl
                  ,`Payout_Url` = v_CallbackPayOut

                  ,`RTSettlementOn` = v_RTSettlementOn
                  ,`FailedCount` = v_FailedCount  ,

                 Business_Type =v_Business_Type,
             Business_Category =v_Business_Category,
             Business_Sub_Category =v_Business_Sub_Category,
             WebAppURL =v_WebAppURL  ,

               Account =v_Account,
            ifsc=v_ifsc,
             BankName=v_BankName,
             AccountHolderName  =v_AccountHolderName,
                PayoutServiceID  =v_Payout_Chnl ,
              AgentID  =v_AgentID

             WHERE UserId =v_UserID;

             UPDATE `User_Tax_Mst`
               SET `Tax` = v_Payin_tax
                  ,`Pay_Tax` = v_Payout_tax
             WHERE `UserID` =v_UserID;

                select 'success' as message,1 status;
              ELSE
              UPDATE `UserMaster`
               SET `Name` = v_Name
                  ,`BusinessName` = v_BusinessName
                  ,`Mobile` = v_Mobile
                  ,`EmailId` = v_EmailId
                  ,`Gender` = v_Gender                ,`Dob` = v_Dob
                  ,`PAN` = v_PAN
                  ,`Pincode` = v_Pincode
                  ,`Address` = v_Address
                  ,`City` = v_City
                  ,`State` = v_State
                  ,`GSTNo` = v_GSTNo
                  ,`SID` = v_SID
               ,`Status` = v_Status
               ,`payout_flag` = v_payout_flag
                  ,`Callback_URL` = v_CallbackPayin
                  ,`Payin_Chnl` = v_Payin_Chnl
                  ,`Payout_Url` = v_CallbackPayOut

                  ,`RTSettlementOn` = v_RTSettlementOn
                  ,`FailedCount` = v_FailedCount ,
                   Business_Type =v_Business_Type,
             Business_Category =v_Business_Category,
             Business_Sub_Category =v_Business_Sub_Category,
             WebAppURL =v_WebAppURL,
                Account =v_Account,
            ifsc=v_ifsc,
             BankName=v_BankName,
             AccountHolderName  =v_AccountHolderName,
                PayoutServiceID  =v_Payout_Chnl ,
              AgentID  =v_AgentID


             WHERE UserId =v_UserID;
              END IF;

             UPDATE `User_Tax_Mst`
              SET `Tax` = v_Payin_tax
                  ,`Pay_Tax` = v_Payout_tax
             WHERE `UserID` =v_UserID;

                select 'success' as message,1 status;

            END;
            END
            SQL;
    }
}
