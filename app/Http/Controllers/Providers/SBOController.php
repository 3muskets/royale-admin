<?php
namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\Log;
// use App\Http\Controllers\UserController;
// use App\Http\Controllers\Provider;
use Auth;
use App;

class SBOController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en'
                    ,'zh-cn'  => 'zh-cn'
                    ,'th'  => 'th-th'
                );

        return $locale[App::getLocale()];
    }

    //***********************************
    //  Call from provider
    //***********************************
    public function getBalance(Request $request)
    {
        try 
        {
            log::debug('Balance');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");

            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $productType = $request['ProductType'];
            $gameType = $request['GameType'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist',
                        'Balance' => 0];
            }

            $balance = $db[0]->available;

            return ['AccountName' => $username,
                    'Balance' => $balance,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];
        }
    }

    public function debit(Request $request)
    {
        try 
        {
            log::debug('debit');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");

            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $amount = $request['Amount'];
            $trfCode = $request['TransferCode'];
            $txnId = $request['TransactionId'];
            $betTime = $request['BetTime'];
            $productType = $request['ProductType'];
            $gameType = $request['GameType'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            log::debug($db);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            if ($balance < $amount) 
            {
                return ['ErrorCode' => 5,
                        'ErrorMessage' => 'Not enough balance',
                        'Balance' => $balance,
                        'AccountName' => $username];
            }


            $db = DB::select("SELECT txn_id, amount
                        FROM sbo_debit
                        WHERE txn_id = ?
                        ",[$txnId]);

            if (sizeOf($db) != 0) 
            {
                //Casino
                if ($productType == 7 || $productType == 3) 
                {
                    if ($db[0]->amount < $amount) 
                    {
                        DB::update("UPDATE member_credit
                        SET available = available + ? - ?
                        WHERE member_id = ?"
                        ,[$db[0]->amount,$amount,$memberId]);

                        DB::update("UPDATE sbo_debit
                            SET amount = ?
                            WHERE trf_code = ?"
                            ,[$amount,$trfCode]);

                         return ['AccountName' => $username,
                                'Balance' => $balance + $db[0]->amount - $amount,
                                'ErrorCode' => 0,
                                'ErrorMessage' => 'No Error'];
                    }

                    $db = DB::select("SELECT txn_id
                            FROM sbo_credit
                            WHERE txn_id = ?
                            ",[$txnId]);

                    if (sizeOf($db) != 0) 
                    {
                        return ['ErrorCode' => 5003,
                        'ErrorMessage' => 'Bet With Same RefNo Exists',
                        'Balance' => $balance,
                        'AccountName' => $username];
                    }

                    return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error',
                    'Balance' => $balance];
                }

                return ['ErrorCode' => 5003,
                    'ErrorMessage' => 'Bet With Same RefNo Exists',
                    'Balance' => $balance,
                    'AccountName' => $username];
            }

            DB::insert("INSERT INTO sbo_debit(txn_id,trf_code,member_id,amount,bet_time,created_at)
                        VALUES(?,?,?,?,?,NOW())"
                        ,[$txnId,$trfCode,$memberId,$amount,$betTime]);

            log::debug($productType);

            if ($productType == 9) 
            {
                DB::insert("INSERT INTO sbo_credit(txn_id,trf_code,created_at)
                        VALUES(?,?,NOW())"
                        ,[$txnId,$trfCode]);
            }

            DB::update("UPDATE member_credit
                        SET available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);


            return ['AccountName' => $username,
                    'Balance' => $balance - $amount,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];
        }
    }

    public function credit(Request $request)
    {
        try 
        {
            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");

            log::debug('credit');
            Log::debug($request);

            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $winLoss = $request['WinLoss'];
            $trfCode = $request['TransferCode'];
            $txnId = $request['TransferCode'];
            $resultType = $request['ResultType'];
            $resultTime = $request['ResultTime'];
            $productType = $request['ProductType'];
            $gameType = $request['GameType'];
            // $prdId = Provider::SBO;

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            $db = DB::select("SELECT trf_code
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND status = 'c'
                            ",[$trfCode]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 2001,
                    'ErrorMessage' => 'Bet Already Settled',
                    'Balance' => $balance,
                    'AccountName' => $username];
            }

            if ($productType == 9) 
            {
                $db = DB::select("SELECT trf_code
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND status = 'x'
                            ",[$trfCode]);

                if (sizeOf($db) != 0) 
                {
                    $db = DB::select("SELECT txn_id
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND winloss is NULL
                            ",[$trfCode]);

                    if (sizeOf($db) != 0) 
                    {
                        $db = DB::update('UPDATE sbo_credit 
                                SET winloss = ?
                                ,result_type = ? 
                                ,result_time = ? 
                                ,status = ?
                                WHERE trf_code = ?
                                    AND txn_id = ?'
                        ,[$winLoss,$resultType,$resultTime,'c',$trfCode,$db[0]->txn_id]);

                        DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$winLoss,$memberId]);

                        return ['AccountName' => $username,
                                'Balance' => $balance + $winLoss,
                                'ErrorCode' => 0,
                                'ErrorMessage' => 'No Error'];
                    }

                    return ['ErrorCode' => 2002,
                        'ErrorMessage' => 'Bet Already Canceled',
                        'Balance' => $balance,
                        'AccountName' => $username];
                }
            }
            else
            {
                $db = DB::select("SELECT trf_code
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND status = 'x'
                            ",[$trfCode]);

                if (sizeOf($db) != 0) 
                {
                    return ['ErrorCode' => 2002,
                        'ErrorMessage' => 'Bet Already Canceled',
                        'Balance' => $balance,
                        'AccountName' => $username];
                }
            }

            $db = DB::select('
                SELECT a.txn_id,a.amount
                FROM sbo_debit a
                LEFT JOIN sbo_credit b ON a.txn_id = b.txn_id
                WHERE  a.trf_code = ?
                ORDER BY a.created_at
                LIMIT 1'
                ,[$trfCode]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 6,
                    'ErrorMessage' => 'Bet Does Not Exist',
                    'Balance' => $balance];
            }

            $debitTxnId = $db[0]->txn_id;
            $debitAmt = $db[0]->amount;

            //calculate PT and COMM
            // $db = DB::select('
            //     SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
            //     FROM users a
            //     LEFT JOIN pt_eff b ON a.admin_id = b.admin_id
            //     LEFT JOIN admin_comm c ON a.admin_id = c.admin_id
            //     WHERE a.id = ?
            //         AND b.prd_id = ?'
            //     ,[$memberId,$prdId]);

            // $tier1PT = $db[0]->tier1_pt;
            // $tier2PT = $db[0]->tier2_pt;
            // $tier3PT = $db[0]->tier3_pt;
            // $tier4PT = $db[0]->tier4_pt;
            // $tier4Comm = $db[0]->comm;

            // $wlAmt = $debitAmt - $winLoss;

            // $tier4PTAmt = $wlAmt * ($tier4PT / 100);
            // Helper::removePrecision($tier4PTAmt);

            // $tier3PTAmt = $wlAmt * ($tier3PT / 100);
            // Helper::removePrecision($tier3PTAmt);

            // $tier2PTAmt = $wlAmt * ($tier2PT / 100);
            // Helper::removePrecision($tier2PTAmt);

            // $tier1PTAmt = $wlAmt - $tier4PTAmt - $tier3PTAmt - $tier2PTAmt;

            // $tier4CommAmt = $wlAmt * ($tier4Comm / 100);

            $db = DB::select("SELECT trf_code
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND status = 'r'
                            ",[$trfCode]);

            if (sizeOf($db) != 0) 
            {
                DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$winLoss,$memberId]);

                DB::update("UPDATE sbo_credit
                            SET status = ?,
                            winloss = ?
                            WHERE trf_code = ?",
                            ['c',
                                $winLoss,
                                $trfCode]);

                return ['AccountName' => $username,
                    'Balance' => $balance + $winLoss,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
            }

            if ($productType == 9) 
            {
                $db = DB::update('UPDATE sbo_credit 
                                SET winloss = ?
                                ,result_type = ? 
                                ,result_time = ? 
                                ,status = ?
                                WHERE trf_code = ?'
                        ,[$winLoss,$resultType,$resultTime,'c',$trfCode]);
            }
            else
            {
                $db = DB::insert('
                        INSERT INTO sbo_credit
                        (txn_id,trf_code,winloss,result_type,result_time,status
                        ,created_at)
                        VALUES
                        (?,?,?,?,?,?
                        ,NOW())'
                        ,[$txnId,$trfCode,$winLoss,$resultType,$resultTime,'c']);
            }

            DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$winLoss,$memberId]);

            return ['AccountName' => $username,
                    'Balance' => $balance + $winLoss,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];
        }
    }

    public function rollback(Request $request)
    {
        try 
        {
            log::debug('rollback');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");

            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $trfCode = $request['TransferCode'];
            $productType = $request['ProductType'];
            $gameType = $request['GameType'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;
            $amount = 0;

            $db = DB::select("SELECT amount
                            FROM sbo_debit
                            WHERE trf_code = ?
                            ",[$trfCode]);

            foreach ($db as $d) 
            {
                $amount = $amount + $d->amount;
            }
            
            //if already rollback
            $db = DB::select("SELECT winloss
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND status = 'r'
                            ",[$trfCode]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 2003,
                        'ErrorMessage' => 'Bet Already Rollback',
                        'Balance' => $balance,
                        'AccountName' => $username
                    ];
            }

            //if already sbo_credit
            $db = DB::select("SELECT winloss, status
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND status != 'r'
                            ",[$trfCode]);

            if (sizeof($db) == 0) 
            {
                return ['ErrorCode' => 6,
                    'ErrorMessage' => 'Bet not exists',
                        'Balance' => $balance];
            }

            if ($db[0]->status == 'c') 
            {
                $amount = $db[0]->winloss;
            }

            DB::update("UPDATE member_credit
                        SET available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

            DB::update("UPDATE sbo_credit
                        SET status = ?
                        WHERE trf_code = ?"
                        ,['r',$trfCode]);

            return ['AccountName' => $username,
                    'Balance' => $balance - $amount,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];

            log::debug($request);
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];

            Log::debug($e);
        }
    }

    public function cancel(Request $request)
    {
        try 
        {
            log::debug('cancel');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");
            // $prdId = Provider::SBO;

            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $trfCode = $request['TransferCode'];
            $productType = $request['ProductType'];
            $gameType = $request['GameType'];
            $isCancelAll = $request['IsCancelAll'];
            $txnId = $request['TransactionId'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;
            $amount = 0;

            //live coin
            if ($productType == 10) 
            {
                $db = DB::select('
                        SELECT amount, status
                        FROM sbo_live_coin 
                        WHERE  trf_code = ?
                            AND txn_id = ?'
                        ,[$trfCode,$txnId]);

                if (sizeOf($db) == 0) 
                {
                    return ['ErrorCode' => 6,
                        'ErrorMessage' => 'Bet Does Not Exist',
                        'Balance' => $balance];
                }

                if ($db[0]->status == 'x') 
                {
                    return ['ErrorCode' => 2002,
                        'ErrorMessage' => 'Bet Already Canceled',
                        'Balance' => $balance,
                        'AccountName' => $username];
                }

                $amount = $db[0]->amount;

                $db = DB::update('UPDATE sbo_live_coin 
                                SET status = ?
                                WHERE trf_code = ?
                                    AND txn_id = ?'
                        ,['x',$trfCode,$txnId]);

                DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

                return ['AccountName' => $username,
                        'Balance' => $balance + $amount,
                        'ErrorCode' => 0,
                        'ErrorMessage' => 'No Error'];
            }

            $db = DB::select('
                SELECT a.trf_code,a.amount
                FROM sbo_debit a
                LEFT JOIN sbo_credit b ON a.txn_id = b.txn_id
                WHERE  a.trf_code = ?
                ORDER BY a.created_at
                LIMIT 1'
                ,[$trfCode]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 6,
                    'ErrorMessage' => 'Bet Does Not Exist',
                    'Balance' => $balance];
            }

            $debitTxnId = $db[0]->trf_code;
            $debitAmt = $db[0]->amount;

            if (!$isCancelAll) 
            {
                $db = DB::select("SELECT amount
                            FROM sbo_debit
                            WHERE trf_code = ?
                                AND txn_id = ?
                            ",[$trfCode,$txnId]);

                $amount = $db[0]->amount;


                $db = DB::select("SELECT winloss
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND txn_id = ?
                                AND status = 'c'
                            ",[$trfCode,$txnId]);

                if (sizeOf($db) != 0) 
                {
                    $winloss = $db[0]->winloss;

                    DB::update("UPDATE member_credit
                            SET available = available - ? + ?
                            WHERE member_id = ?"
                            ,[$winloss,$amount,$memberId]);

                    DB::update("UPDATE sbo_credit
                            SET status = ?
                            WHERE trf_code = ?
                                AND txn_id = ?"
                            ,['x',$trfCode,$txnId]);

                    return ['AccountName' => $username,
                        'Balance' => $balance - $winloss,
                        'ErrorCode' => 0,
                        'ErrorMessage' => 'No Error'];
                }

                $db = DB::select("SELECT winloss
                                FROM sbo_credit
                                WHERE trf_code = ?
                                    AND txn_id = ?
                                    AND status = 'x'
                                ",[$trfCode,$txnId]);

                if (sizeOf($db) != 0) 
                {
                    return ['ErrorCode' => 2002,
                        'ErrorMessage' => 'Bet Already Canceled',
                        'Balance' => $balance,
                        'AccountName' => $username];
                }
            }
            else
            {
                $db = DB::select("SELECT amount
                            FROM sbo_debit
                            WHERE trf_code = ?
                            ",[$trfCode]);

                foreach ($db as $d) 
                {
                    $amount = $amount + $d->amount;
                }

                $db = DB::select("SELECT winloss
                            FROM sbo_credit
                            WHERE trf_code = ?
                                AND status = 'c'
                            ",[$trfCode]);

                if (sizeOf($db) != 0) 
                {
                    $winloss = $db[0]->winloss;

                    $db = DB::select("SELECT return_stake
                        FROM sbo_return_stake
                        WHERE txn_id = ?"
                        ,[$txnId]);

                    if (sizeof($db) != 0) 
                    {
                        $winloss = $winloss - $db[0]->return_stake;
                    }

                    DB::update("UPDATE member_credit
                            SET available = available - ? + ?
                            WHERE member_id = ?"
                            ,[$winloss,$amount,$memberId]);

                    DB::update("UPDATE sbo_credit
                            SET status = ?
                            WHERE trf_code = ?"
                            ,['x',$trfCode]);

                    return ['AccountName' => $username,
                        'Balance' => $balance - $winloss,
                        'ErrorCode' => 0,
                        'ErrorMessage' => 'No Error'];
                }

                $db = DB::select("SELECT winloss
                                FROM sbo_credit
                                WHERE trf_code = ?
                                    AND status = 'x'
                                ",[$trfCode]);

                if (sizeOf($db) != 0) 
                {
                    return ['ErrorCode' => 2002,
                        'ErrorMessage' => 'Bet Already Canceled',
                        'Balance' => $balance,
                        'AccountName' => $username];
                }
            }

            //calculate PT and COMM
            // $db = DB::select('
            //     SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
            //     FROM users a
            //     LEFT JOIN pt_eff b ON a.admin_id = b.admin_id
            //     LEFT JOIN admin_comm c ON a.admin_id = c.admin_id
            //     WHERE a.id = ?
            //         AND b.prd_id = ?'
            //     ,[$memberId,$prdId]);

            // $tier1PT = $db[0]->tier1_pt;
            // $tier2PT = $db[0]->tier2_pt;
            // $tier3PT = $db[0]->tier3_pt;
            // $tier4PT = $db[0]->tier4_pt;
            // $tier4Comm = $db[0]->comm;

            // $wlAmt = $debitAmt - $amount;

            // $tier4PTAmt = $wlAmt * ($tier4PT / 100);
            // Helper::removePrecision($tier4PTAmt);

            // $tier3PTAmt = $wlAmt * ($tier3PT / 100);
            // Helper::removePrecision($tier3PTAmt);

            // $tier2PTAmt = $wlAmt * ($tier2PT / 100);
            // Helper::removePrecision($tier2PTAmt);

            // $tier1PTAmt = $wlAmt - $tier4PTAmt - $tier3PTAmt - $tier2PTAmt;

            // $tier4CommAmt = $wlAmt * ($tier4Comm / 100);

            if ($productType == 9) 
            {
                $db = DB::update('UPDATE sbo_credit 
                                SET winloss = ?
                                ,status = ?
                                WHERE trf_code = ?
                                    AND txn_id = ?'
                        ,[$amount,'x',$trfCode,$txnId]);
            }
            else
            {
                DB::insert('
                        INSERT INTO sbo_credit
                        (txn_id,trf_code,winloss,status
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,NOW())'
                        ,[  $txnId,$trfCode,$amount,'x']);
            }

            DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

            return ['AccountName' => $username,
                    'Balance' => $balance + $amount,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
            log::debug($request);
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];

            Log::debug($e);
        }
    }

    public function tip(Request $request)
    {
        try 
        {
            log::debug('tip');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");

            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $amount = $request['Amount'];
            $tipTime = $request['TipTime'];
            $productType = $request['ProductType'];
            $gameType = $request['GameType'];
            $trfCode = $request['TransferCode'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            if ($balance < $amount) 
            {
                return ['ErrorCode' => 5,
                        'ErrorMessage' => 'Not enough balance'];
            }

            $db = DB::select("SELECT trf_code, amount
                            FROM sbo_tip
                            WHERE trf_code = ?
                            ",[$trfCode]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 5003,
                    'ErrorMessage' => 'Tips With Same RefNo Exists',
                    'Balance' => $balance,
                    'AccountName' => $username];
            }

            DB::update("UPDATE member_credit
                        SET available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

            DB::insert("INSERT INTO sbo_tip(trf_code,member_id,amount,tip_time,created_at)
                        VALUES(?,?,?,?,NOW())"
                        ,[$trfCode,$memberId,$amount,$tipTime]);

            return ['AccountName' => $username,
                    'Balance' => $balance - $amount,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];

            Log::debug($e);
        }
    }

    public function bonus(Request $request)
    {
        try 
        {
            log::debug('bonus');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");
            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $amount = $request['Amount'];
            $bonusTime = $request['BonusTime'];
            $productType = $request['ProductType'];
            $gameType = $request['GameType'];
            $trfCode = $request['TransferCode'];
            $txnId = $request['TransactionId'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            if ($balance < $amount) 
            {
                return ['ErrorCode' => 5,
                        'ErrorMessage' => 'Not enough balance'];
            }

            $db = DB::select("SELECT trf_code, amount
                            FROM sbo_bonus
                            WHERE trf_code = ?
                            ",[$trfCode]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 5003,
                    'ErrorMessage' => 'Bonus With Same RefNo Exists',
                    'Balance' => $balance,
                    'AccountName' => $username];
            }

            DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

            DB::insert("INSERT INTO sbo_bonus(txn_id,trf_code,member_id,amount,bonus_time,created_at)
                        VALUES(?,?,?,?,?,NOW())"
                        ,[$txnId,$trfCode,$memberId,$amount,$bonusTime]);

            return ['AccountName' => $username,
                    'Balance' => $balance + $amount,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];

            Log::debug($e);
        }
    }

    public function getBetStatus(Request $request)
    {
        try 
        {
            log::debug('getBetStatus');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");
            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $productType = $request['ProductType'];
            $gameType = $request['GameType'];
            $trfCode = $request['TransferCode'];
            $txnId = $request['TransactionId'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT txn_id
                            FROM sbo_debit
                            WHERE trf_code = ?
                                AND txn_id = ?
                            ",[$trfCode,$txnId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 6,
                        'ErrorMessage' => 'Bet not exist'];
            }

            $db = DB::select("SELECT b.txn_id, a.amount, b.winloss, b.status
                            FROM sbo_debit a
                            LEFT JOIN sbo_credit b
                                ON a.trf_code = b.trf_code
                            WHERE a.trf_code = ?
                            ",[$trfCode]);

            if ($db[0]->txn_id != '') 
            {
                $db = DB::select("SELECT a.txn_id, a.amount, b.winloss, b.status
                            FROM sbo_debit a
                            LEFT JOIN sbo_credit b
                                ON a.txn_id = b.txn_id
                            WHERE a.trf_code = ?
                                AND a.txn_id = ?
                            ",[$trfCode,$txnId]);

                if (sizeOf($db) == 0) 
                {
                    return ['ErrorCode' => 6,
                            'ErrorMessage' => 'Bet not exist'];
                }
            }

            if ($db[0]->status != NULL) 
            {
                if ($db[0]->status == 'c') 
                {
                    $status = 'Settled';
                }
                else if ($db[0]->status == 'x') 
                {
                    $status = 'Void';
                }
                else
                {
                    $status = 'Running';
                }
            }
            else
            {
                $status = 'Running';
            }

            $amount = $db[0]->amount;
            $winloss = $db[0]->winloss;

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'userName empty',
                        'Balance' => 0];
            }

            return [
                    'transferCode' => $trfCode,
                    'transactionId' => $txnId,
                    'status' => $status,
                    'winloss' => $winloss,
                    'stake' => $amount,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
            log::debug($request);
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];

            Log::debug($e);
        }
    }

    public static function checkIp()
    {
        return true;
        $ips = explode(",",env('SBO_IPADDRESS'));

        $allowIp = false;

        foreach ($ips as $ip) 
        {
            if ($_SERVER['REMOTE_ADDR'] == $ip) 
            {
                $allowIp = true;
            }
        }

        return $allowIp;
    }

    public function liveCoinTransaction(Request $request)
    {
        try 
        {
            log::debug('livecoin');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");

            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $amount = $request['Amount'];
            $txnTime = $request['TranscationTime'];
            $txnId = $request['TransactionId'];
            $trfCode = $request['TransferCode'];
            $gameType = $request['GameType'];
            $productType = $request['ProductType'];
            $selection = $request['Selection'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            if ($balance < $amount) 
            {
                return ['ErrorCode' => 5,
                        'ErrorMessage' => 'Not enough balance',
                        'Balance' => $balance,
                        'AccountName' => $username];
            }


            $db = DB::select("SELECT txn_id, amount
                        FROM sbo_live_coin
                        WHERE txn_id = ?
                        ",[$txnId]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 5003,
                    'ErrorMessage' => 'Bet With Same RefNo Exists',
                    'Balance' => $balance,
                    'AccountName' => $username];
            }

            DB::insert("INSERT INTO sbo_live_coin(txn_id,trf_code,member_id,amount,status,created_at)
                        VALUES(?,?,?,?,?,NOW())"
                        ,[$txnId,$trfCode,$memberId,$amount,'c']);

            // log::debug($productType);

            // if ($productType == 9) 
            // {
            //     DB::insert("INSERT INTO sbo_credit(txn_id,trf_code,created_at)
            //             VALUES(?,?,NOW())"
            //             ,[$txnId,$trfCode]);
            // }

            DB::update("UPDATE member_credit
                        SET available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);


            return ['AccountName' => $username,
                    'Balance' => $balance - $amount,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];
        }
    }

    public function returnStake(Request $request)
    {
        try 
        {
            log::debug('returnStake');
            log::debug($request);

            if (!self::checkIp()) 
            {
                return ['ErrorCode' => 2,
                    'ErrorMessage' => 'Invalid Ip'];
            }

            $ownCompanyKey = env("SBO_COMPANY_KEY");

            $companyKey = $request['CompanyKey'];
            $username = $request['Username'];
            $currentStake = $request['CurrentStake'];
            $returnStakeTime = $request['ReturnStakeTime'];
            $txnId = $request['TransactionId'];
            $trfCode = $request['TransferCode'];
            $gameType = $request['GameType'];
            $productType = $request['ProductType'];

            if ($companyKey != $ownCompanyKey) 
            {
                return ['ErrorCode' => 4,
                        'ErrorMessage' => 'CompanyKey Error',
                        'Balance' => 0];
            }

            if ($username == '') 
            {
                return ['ErrorCode' => 3,
                        'ErrorMessage' => 'Username Empty',
                        'Balance' => 0];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?
                            ",[$username]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1,
                        'ErrorMessage' => 'Member not exist'];
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            $db = DB::select("SELECT txn_id, amount
                        FROM sbo_debit
                        WHERE txn_id = ?
                        ",[$txnId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 6,
                    'ErrorMessage' => 'Bet Does Not Exist',
                    'Balance' => $balance];
            }

            $debitAmt = $db[0]->amount;

            $db = DB::select("SELECT a.txn_id, b.status
                        FROM sbo_return_stake a
                        LEFT JOIN sbo_credit b
                            ON a.txn_id = b.txn_id
                        WHERE a.txn_id = ?
                        ",[$txnId]);

            if (sizeOf($db) != 0 && $db[0]->status == 'x') 
            {
                return ['ErrorCode' => 5003,
                    'ErrorMessage' => 'Bet with same refNo already exists.',
                    'Balance' => $balance,
                    'AccountName' => $username];
            }

            $db = DB::select("SELECT txn_id
                        FROM sbo_return_stake
                        WHERE txn_id = ?
                        ",[$txnId]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 5008,
                    'ErrorMessage' => 'Bet Already Returned Stake',
                    'Balance' => $balance,
                    'AccountName' => $username];
            }

            $amount = $debitAmt - $currentStake;

            DB::insert("INSERT INTO sbo_return_stake(txn_id,trf_code,member_id,current_stake,return_stake,return_stake_time,created_at)
                        VALUES(?,?,?,?,?,?,NOW())"
                        ,[$txnId,$trfCode,$memberId,$currentStake,$amount,$returnStakeTime]);

            // log::debug($productType);

            // if ($productType == 9) 
            // {
            //     DB::insert("INSERT INTO sbo_credit(txn_id,trf_code,created_at)
            //             VALUES(?,?,NOW())"
            //             ,[$txnId,$trfCode]);
            // }

            DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);


            return ['AccountName' => $username,
                    'Balance' => $balance + $amount,
                    'ErrorCode' => 0,
                    'ErrorMessage' => 'No Error'];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'Internal Error'];
        }
    }
}
