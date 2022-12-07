<?php
namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\Log;
// use App\Http\Controllers\UserController;
// use App\Http\Controllers\Provider;
use Auth;
use App;
use DateTime;

class IBCController extends Controller
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
    //**********************************

    //IBC use refId as TxnId

    public function getBalance(Request $request)
    {
        try 
        {
            log::debug('Balance');

            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                return ['status' => 203,
                        'msg' => 'Account is not exist'];
            }

            $balance = $db[0]->available;

            $date = new DateTime();
            $date = $date->format('Y-m-d\TH:i:s.u');

            return ['userId' => $request['message']['userId'],
                    'balance' => $balance,
                    'balanceTs' => $date,
                    'status' => 0];
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    //single
    public function placebet(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            log::debug('Debit');

            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

            $betTime = $request['message']['betTime'];
            $refId = $request['message']['refId'];
            $odds = $request['message']['odds'];
            $amount = $request['message']['actualAmount'];
            $matchId = $request['message']['matchId'];


            //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                return ['status' => 203,
                        'msg' => 'Account is not exist'];
            }

            $balance = $db[0]->available;

            if ($balance < $amount) 
            {
                return ['status' => 502,
                        'msg' => 'Player Has Insufficient Funds'];
            }

            $db = DB::select("SELECT txn_id
                        FROM ibc_debit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) != 0) 
            {
                return ['status' => 1,
                        'msg' => 'Duplicate Transaction'];
            }

            $licenseeTxId = Helper::generateUniqueId();

            DB::insert("INSERT INTO ibc_debit(txn_id, licensee_tx_id, match_id, odds, member_id, amount, created_at)
                    VALUES (?,?,?,?,?,?,NOW())"
                    ,[$refId
                        ,$licenseeTxId
                        ,$matchId
                        ,$odds
                        ,$userId
                        ,$amount
                    ]);

            DB::update("UPDATE member_credit
                        SET available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

            DB::commit();

            return [
                    'licenseeTxId' => $licenseeTxId,
                    'refId' => $request['message']['refId'],
                    'status' => 0];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            DB::rollback();

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    public function confirmbet(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            log::debug('Confirm');

            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

            $refId = $request['message']['txns'][0]['refId'];
            $txId = $request['message']['txns'][0]['txId'];
            $odds = $request['message']['txns'][0]['odds'];
            $isOddsChanged = $request['message']['txns'][0]['isOddsChanged'];
            $creditAmount = $request['message']['txns'][0]['creditAmount'];
            $debitAmount = $request['message']['txns'][0]['debitAmount'];
            $actualAmount = $request['message']['txns'][0]['actualAmount'];

            //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                DB::rollback();
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                DB::rollback();
                return ['status' => 203,
                        'msg' => 'Account is not exist'];
            }

            $balance = $db[0]->available;

            $db = DB::select("SELECT txn_id, amount
                        FROM ibc_debit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) == 0) 
            {
                DB::rollback();
                return ['status' => 309,
                        'msg' => 'Invalid Transaction Status'];
            }

            $debit = $db[0]->amount;

            if ($isOddsChanged) 
            {
                if ($debit != $actualAmount) 
                {
                    DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$creditAmount,$userId]);

                    DB::update("UPDATE ibc_debit
                        SET amount = ?
                        ,odds = ?
                        WHERE member_id = ?"
                        ,[$debitAmount,$odds,$userId]);
                }
            }

            DB::commit();

            return [
                    'balance' => $balance + $creditAmount,
                    'status' => 0];
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            DB::rollback();

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    public function credit(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            log::debug('credit');
            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

            foreach ($request['message']['txns'] as  $value) 
            {
                $refId = $value['refId'];
                $txnId = $value['txId'];
                $amount = $value['creditAmount'];
                $status = $value['status'];

                 //only for testing
                $userId = $value['userId'];
                $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

                //only for staging
                $userId = str_replace('_test',"",$userId);

                $key = env('IBC_KEY');

                if ($key != $request['key']) 
                {
                    DB::rollback();
                    return ['status' => 311,
                            'msg' => 'Invalid Authentication Key'];
                }

                $db = DB::select("SELECT a.id, b.available
                                FROM member a
                                INNER JOIN member_credit b ON a.id = b.member_id
                                WHERE a.id = ?
                                ",[$userId]);

                if (sizeOf($db) == 0) 
                {
                    DB::rollback();
                    return ['status' => 203,
                            'msg' => 'Account is not exist'];
                }

                $balance = $db[0]->available;

                $db = DB::select("SELECT txn_id
                            FROM ibc_debit 
                            WHERE txn_id = ?",[$refId]);

                if (sizeOf($db) == 0) 
                {
                    return ['status' => 10005,
                            'msg' => 'BET_DOES_NOT_EXIST'];
                }

                $db = DB::select("SELECT txn_id
                            FROM ibc_credit 
                            WHERE txn_id = ?",[$refId]);

                if (sizeOf($db) != 0) 
                {
                    return ['status' => 1001,
                            'msg' => 'BET_ALREADY_SETTLED'];
                }


                DB::insert("INSERT INTO ibc_credit(txn_id, ext_txn_id, type, amount, status, created_at)
                        VALUES (?,?,?,?,?,NOW())"
                        ,[$refId
                            ,$txnId
                            ,'c'
                            ,$amount
                            ,$status
                        ]);

                DB::update("UPDATE member_credit
                            SET available = available + ?
                            WHERE member_id = ?"
                            ,[$amount,$userId]);
            }

            DB::commit();

            $db = DB::select("SELECT a.id, b.available
                                FROM member a
                                INNER JOIN member_credit b ON a.id = b.member_id
                                WHERE a.id = ?
                                ",[$userId]);

            $balance = $db[0]->available;

            return ['balance' => $balance,
                    'status' => 'OK'];
            
        } 
        catch (Exception $e) 
        {
            DB::rollback();
            Log::debug($e);

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    public function cancel(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            log::debug('cancel');
            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

            $refId = $request['message']['txns'][0]['refId'];
            $amount = $request['message']['txns'][0]['creditAmount'];

             //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                DB::rollback();
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                DB::rollback();
                return ['status' => 203,
                        'msg' => 'Account is not exist'];
            }

            $balance = $db[0]->available;

            $db = DB::select("SELECT txn_id
                        FROM ibc_debit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) == 0) 
            {
                return ['status' => 10005,
                        'msg' => 'BET_DOES_NOT_EXIST'];
            }

            $db = DB::select("SELECT txn_id
                        FROM ibc_credit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) != 0) 
            {
                return ['status' => 1001,
                        'msg' => 'BET_ALREADY_SETTLED'];
            }


            DB::insert("INSERT INTO ibc_credit(txn_id, type, amount, created_at)
                    VALUES (?,?,?,NOW())"
                    ,[$refId
                        ,'x'
                        ,$amount
                    ]);

            DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

            DB::commit();

            return ['balance' => $balance + $amount,
                    'status' => 'OK'];
            
        } 
        catch (Exception $e) 
        {
            DB::rollback();
            Log::debug($e);

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    public function resettle(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            log::debug('resettle');
            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

            $refId = $request['message']['txns'][0]['refId'];
            $creamount = $request['message']['txns'][0]['creditAmount'];
            $deamount = $request['message']['txns'][0]['debitAmount'];
            $payout = $request['message']['txns'][0]['payout'];

             //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                DB::rollback();
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                DB::rollback();
                return ['status' => 203,
                        'msg' => 'Account is not exist'];
            }

            $balance = $db[0]->available;

            $db = DB::select("SELECT txn_id
                        FROM ibc_debit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) == 0) 
            {
                return ['status' => 10005,
                        'msg' => 'BET_DOES_NOT_EXIST'];
            }

            $db = DB::select("SELECT txn_id
                        FROM ibc_credit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) == 0) 
            {
                return ['status' => 10005,
                        'msg' => 'BET_DOES_NOT_EXIST'];
            }

            DB::insert("UPDATE ibc_credit
                        SET status = ?
                        ,amount = ?
                        WHERE txn_id = ?"
                    ,['r'
                        ,$payout
                        ,$refId
                    ]);

            DB::update("UPDATE member_credit
                        SET available = available + ? - ?
                        WHERE member_id = ?"
                        ,[$creamount,$deamount,$userId]);

            DB::commit();

            return ['balance' => $balance + $creamount - $deamount,
                    'status' => 'OK'];
            
        } 
        catch (Exception $e) 
        {
            DB::rollback();
            Log::debug($e);

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    public function unsettle(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            log::debug('unsettle');
            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

            $refId = $request['message']['txns'][0]['refId'];
            $creamount = $request['message']['txns'][0]['creditAmount'];
            $deamount = $request['message']['txns'][0]['debitAmount'];
            $payout = $request['message']['txns'][0]['payout'];

             //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                DB::rollback();
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                DB::rollback();
                return ['status' => 203,
                        'msg' => 'Account is not exist'];
            }

            $balance = $db[0]->available;

            $db = DB::select("SELECT txn_id
                        FROM ibc_debit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) == 0) 
            {
                return ['status' => 10005,
                        'msg' => 'BET_DOES_NOT_EXIST'];
            }

            $db = DB::select("SELECT txn_id
                        FROM ibc_credit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) == 0) 
            {
                return ['status' => 10005,
                        'msg' => 'BET_DOES_NOT_EXIST'];
            }

            DB::insert("DELETE FROM ibc_credit
                        WHERE txn_id = ?"
                    ,[$refId
                    ]);

            DB::update("UPDATE member_credit
                        SET available = available + ? - ?
                        WHERE member_id = ?"
                        ,[$creamount,$deamount,$userId]);

            DB::commit();

            return ['balance' => $balance + $creamount - $deamount,
                    'status' => 'OK'];
            
        } 
        catch (Exception $e) 
        {
            DB::rollback();
            Log::debug($e);

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    //parlay
    public function placebetparlay(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            log::debug('placebetparlay');

            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

             //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            $betTime = $request['message']['betTime'];

            $array = [];

            foreach ($request['message']['txns'] as $value) 
            {
                $refId = $value['refId'];
                $odds = (isset($value['detail'][0]['odds']))?$value['detail'][0]['odds']:NULL;
                $amount = $request['message']['debitAmount'];
                $type = (isset($value['parlayType']))?$value['parlayType']:NULl;

                $db = DB::select("SELECT a.id, b.available
                                FROM member a
                                INNER JOIN member_credit b ON a.id = b.member_id
                                WHERE a.id = ?
                                ",[$userId]);

                if (sizeOf($db) == 0) 
                {
                    return ['status' => 203,
                            'msg' => 'Account is not exist'];
                }

                $balance = $db[0]->available;

                if ($balance < $amount) 
                {
                    return ['status' => 502,
                            'msg' => 'Player Has Insufficient Funds'];
                }

                $db = DB::select("SELECT txn_id
                            FROM ibc_debit 
                            WHERE txn_id = ?",[$refId]);

                if (sizeOf($db) != 0) 
                {
                    return ['status' => 1,
                            'msg' => 'Duplicate Transaction'];
                }

                $licenseeTxId = Helper::generateUniqueId();

                DB::insert("INSERT INTO ibc_debit(txn_id, odds, licensee_tx_id, member_id, is_parlay, type, amount, created_at)
                        VALUES (?,?,?,?,1,?,?,NOW())"
                        ,[$refId
                            ,$odds
                            ,$licenseeTxId
                            ,$userId
                            ,$type
                            ,$amount
                        ]);

                DB::update("UPDATE member_credit
                            SET available = available - ?
                            WHERE member_id = ?"
                            ,[$amount,$userId]);

                array_push($array, ['refId'=>$refId
                                    ,'licenseeTxId'=>$licenseeTxId]);
            }

            DB::commit();

            return [
                    'txns' => $array,
                    'status' => 0];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            DB::rollback();

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    public function confirmbetparlay(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            log::debug('confirmbetparlay');

            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

            $refId = $request['message']['txns'][0]['refId'];
            $txId = $request['message']['txns'][0]['txId'];
            $odds = $request['message']['txns'][0]['odds'];
            $isOddsChanged = $request['message']['txns'][0]['isOddsChanged'];
            $creditAmount = $request['message']['txns'][0]['creditAmount'];
            $debitAmount = $request['message']['txns'][0]['debitAmount'];
            $actualAmount = $request['message']['txns'][0]['actualAmount'];

            //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                DB::rollback();
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                DB::rollback();
                return ['status' => 203,
                        'msg' => 'Account is not exist'];
            }

            $balance = $db[0]->available;

            $db = DB::select("SELECT txn_id, amount
                        FROM ibc_debit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) == 0) 
            {
                DB::rollback();
                return ['status' => 309,
                        'msg' => 'Invalid Transaction Status'];
            }

            $debit = $db[0]->amount;

            if ($isOddsChanged) 
            {
                if ($debit != $actualAmount) 
                {
                    DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$creditAmount,$userId]);

                    DB::update("UPDATE ibc_debit
                        SET amount = ?
                        ,odds = ?
                        WHERE member_id = ?"
                        ,[$debitAmount,$odds,$userId]);
                }
            }

            DB::commit();

            return [
                    'balance' => $balance + $creditAmount,
                    'status' => 0];
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            DB::rollback();

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }

    //adjust balance
    public function adjustbalance(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            log::debug('adjustbalance');

            $request = gzdecode($request->getContent());
            $request = json_decode($request, true);
            log::debug($request);

             //only for testing
            $userId = $request['message']['userId'];
            $userId = str_replace(env('IBC_OPERATOR_ID').'_',"",$userId);

            //only for staging
            $userId = str_replace('_test',"",$userId);

            $refId = $request['message']['refId'];
            $debitAmount = $request['message']['debitAmount'];
            $creditAmount = $request['message']['creditAmount'];
            $type = $request['message']['betTypeName'];

            $key = env('IBC_KEY');

            if ($key != $request['key']) 
            {
                return ['status' => 311,
                        'msg' => 'Invalid Authentication Key'];
            }

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                return ['status' => 203,
                        'msg' => 'Account is not exist'];
            }

            $db = DB::select("SELECT txn_id
                        FROM ibc_debit 
                        WHERE txn_id = ?",[$refId]);

            if (sizeOf($db) != 0) 
            {
                return ['status' => 1,
                        'msg' => 'Duplicate Transaction'];
            }

            DB::insert("INSERT INTO ibc_debit(txn_id, member_id, type, amount, created_at)
                        VALUES (?,?,?,?,NOW())"
                        ,[$refId
                            ,$userId
                            ,$type
                            ,$debitAmount
                        ]);

            DB::insert("INSERT INTO ibc_credit(txn_id, type, amount, created_at)
                        VALUES (?,?,?,?,NOW())"
                        ,[$refId
                            ,'c'
                            ,$creditAmount
                        ]);

            DB::update("UPDATE member_credit
                        SET available = available - ? + ?
                        WHERE member_id = ?"
                        ,[$debitAmount,$creditAmount,$userId]);

            DB::commit();

            $betTime = $request['message']['betTime'];

            DB::commit();

            return ['status' => 0];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            DB::rollback();

            return ['status' => 999,
                    'msg' => 'System Error'];
        }
    }
}
