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

class EVOController extends Controller
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
    public function checkUser(Request $request)
    {
        try 
        {
            $authToken = env('EVO_AUTH_TOKEN');

            if ($authToken != $request['authToken']) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_AUTH_TOKEN'];
            }

            $userId = $request['userId'];

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_MEMBER'];
            }

            return ['sid' => $request['sid'],
                    'uuid' => $request['uuid'],
                    'status' => 'OK'];
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'INTERNAL_ERROR'];
        }
    }

    public function getBalance(Request $request)
    {
        try 
        {
            log::debug('Balance');
            log::debug($request);

            $authToken = env('EVO_AUTH_TOKEN');

            if ($authToken != $request['authToken']) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_AUTH_TOKEN'];
            }

            $userId = $request['userId'];

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_MEMBER'];
            }

            $balance = $db[0]->available;

            return ['bonus' => 0,
                    'balance' => $balance,
                    'uuid' => $request['uuid'],
                    'status' => 'OK'];
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'INTERNAL_ERROR'];
        }
    }

    public function debit(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            log::debug('Debit');
            log::debug($request);

            $authToken = env('EVO_AUTH_TOKEN');

            if ($authToken != $request['authToken']) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_AUTH_TOKEN'];
            }

            $txnId = $request['transaction']['refId'];
            $amount = $request['transaction']['amount'];
            $tableId = $request['game']['details']['table']['id'];
            $gameId = $request['game']['id'];

            $userId = $request['userId'];

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_MEMBER'];
            }

            $balance = $db[0]->available;

            if ($balance < $amount) 
            {
                return ['ErrorCode' => 1001,
                        'ErrorMessage' => 'INSUFFICIENT_FUNDS'];
            }

            $db = DB::select("SELECT txn_id
                        FROM evo_debit 
                        WHERE txn_id = ?",[$txnId]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 1001,
                        'ErrorMessage' => 'BET_ALREADY_EXISTS'];
            }

            DB::insert("INSERT INTO evo_debit(txn_id, table_id, game_id, member_id, amount, created_at)
                    VALUES (?,?,?,?,?,NOW())"
                    ,[$txnId
                        ,$tableId
                        ,$gameId
                        ,$userId
                        ,$amount
                    ]);

            DB::update("UPDATE member_credit
                        SET available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

            DB::commit();

            return ['bonus' => 0,
                    'balance' => $balance - $amount,
                    'uuid' => $request['uuid'],
                    'status' => 'OK'];
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            DB::rollback();

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'INTERNAL_ERROR'];
        }
    }

    public function credit(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            log::debug('credit');
            log::debug($request);

            $authToken = env('EVO_AUTH_TOKEN');

            if ($authToken != $request['authToken']) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_AUTH_TOKEN'];
            }

            $userId = $request['userId'];
            $txnId = $request['transaction']['refId'];
            $exttxnId = $request['transaction']['id'];
            $amount = $request['transaction']['amount'];
            $tableId = $request['game']['details']['table']['id'];
            $gameId = $request['game']['id'];

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_MEMBER'];
            }

            $balance = $db[0]->available;

            $db = DB::select("SELECT txn_id
                        FROM evo_credit 
                        WHERE txn_id = ?",[$txnId]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 1001,
                        'ErrorMessage' => 'BET_ALREADY_SETTLED'];
            }

            $db = DB::select("SELECT txn_id
                        FROM evo_debit 
                        WHERE game_id = ?",[$gameId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 10005,
                        'ErrorMessage' => 'BET_DOES_NOT_EXIST'];
            }

            foreach ($db as $d) 
            {
                if ($d->txn_id == $txnId) 
                {
                    DB::insert("INSERT INTO evo_credit(txn_id, type, amount, ext_txn_id, created_at)
                    VALUES (?,?,?,?,NOW())"
                    ,[$d->txn_id
                        ,'c'
                        ,$amount
                        ,$exttxnId
                    ]);
                }
                else
                {
                    DB::insert("INSERT INTO evo_credit(txn_id, type, amount, ext_txn_id, created_at)
                    VALUES (?,?,?,?,NOW())"
                    ,[$d->txn_id
                        ,'c'
                        ,0
                        ,$exttxnId
                    ]);
                }
            }

            DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

            DB::commit();

            return ['bonus' => 0,
                    'balance' => $balance + $amount,
                    'uuid' => $request['uuid'],
                    'status' => 'OK'];
            
        } 
        catch (Exception $e) 
        {
            DB::rollback();
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'INTERNAL_ERROR'];
        }
    }

    public function cancel(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            log::debug('cancel');
            log::debug($request);

            $authToken = env('EVO_AUTH_TOKEN');

            if ($authToken != $request['authToken']) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_AUTH_TOKEN'];
            }

            $userId = $request['userId'];
            $txnId = $request['transaction']['refId'];
            $exttxnId = $request['transaction']['id'];
            $amount = $request['transaction']['amount'];
            $tableId = $request['game']['details']['table']['id'];
            $gameId = $request['game']['id'];

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            INNER JOIN member_credit b ON a.id = b.member_id
                            WHERE a.id = ?
                            ",[$userId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 1049,
                        'ErrorMessage' => 'INVALID_MEMBER'];
            }

            $balance = $db[0]->available;

            $db = DB::select("SELECT txn_id
                        FROM evo_debit 
                        WHERE txn_id = ?",[$txnId]);

            if (sizeOf($db) == 0) 
            {
                return ['ErrorCode' => 10005,
                        'ErrorMessage' => 'BET_DOES_NOT_EXIST'];
            }

            $db = DB::select("SELECT txn_id
                        FROM evo_credit 
                        WHERE txn_id = ?",[$txnId]);

            if (sizeOf($db) != 0) 
            {
                return ['ErrorCode' => 1001,
                        'ErrorMessage' => 'BET_ALREADY_SETTLED'];
            }


            DB::insert("INSERT INTO evo_credit(txn_id, type, amount, ext_txn_id, created_at)
                    VALUES (?,?,?,?,NOW())"
                    ,[$txnId
                        ,'x'
                        ,$amount
                        ,$exttxnId
                    ]);

            DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

            DB::commit();

            return ['bonus' => 0,
                    'balance' => $balance + $amount,
                    'uuid' => $request['uuid'],
                    'status' => 'OK'];
            
        } 
        catch (Exception $e) 
        {
            DB::rollback();
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'INTERNAL_ERROR'];
        }
    }
}
