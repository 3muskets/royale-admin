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
use Str;

class PTController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    //***********************************
    //  Call from provider
    //***********************************
    public function backUrl(Request $request)
    {
        try 
        {
            log::debug('backUrl');
            log::debug($request);
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ['ErrorCode' => 7,
                    'ErrorMessage' => 'INTERNAL_ERROR'];
        }
    }

    public function auth(Request $request)
    {
        try 
        {
            log::debug('auth');
            $request = $request->getContent();
            $request = json_decode($request,true);

            header('Content-Type: application/json; charset=utf-8');

            if (env('PLAYTECH_BRAND_ID') != $request['brandId']) 
            {
                log::debug(["requestId"=> $request['requestId'],"error"=>"P_03"]);

                return ["requestId"=> $request['requestId']
                ,"error"=>"P_03"];
            }

            // $convData = self::checkRawData($request);

            // if (md5($convData) != $request['hash']) 
            // {
            //     return ["requestId"=> $request['requestId']
            //     ,"error"=>"P_02"];
            // }
            $token = $request['token'];
            $sessionId = Helper::generateRandomString(32);

            $db = DB::select("SELECT b.id,b.username, c.available
                            FROM pt_users_token a
                            LEFT JOIN member b ON a.member_id = b.id
                            LEFT JOIN member_credit c ON c.member_id = b.id
                            WHERE a.token = ?",
                             [$token]);

            if (sizeOf($db) == 0) 
            {
                log::debug(["requestId"=> $request['requestId'],"error"=>"P_07"]);

                return ["requestId"=> $request['requestId']
                ,"error"=>"P_07"];
            }

            $memberId = $db[0]->id;
            $balance = $db[0]->available;
            $username = $db[0]->username;

            $response = ['playerId'=>$memberId
                        ,'playerName'=>$username
                        ,'requestId'=> $request['requestId']
                        ,'playerSessionId'=>$sessionId
                        ,'currency'=>env('CURRENCY')
                        ,'country'=>'MY'
                        ,'balance'=>$balance
                        ,'error'=>0
                        ,'message'=>'Success'
                    ];

            DB::update("UPDATE pt_users_token
                        SET session_id = ?
                        WHERE member_id = ?"
                        ,[$sessionId,$memberId]);

            // $convData = self::checkRawData($response);
            // $convData = md5($convData);

            // $response['hash'] = $convData;

            log::debug($response);


            return $response;
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ["requestId"=> $request['requestId']
                ,"error"=>"P_00"];
        }
    }

    public function balance(Request $request)
    {
        try 
        {
            log::debug('balance');

            $request = $request->getContent();
            $request = json_decode($request,true);

            header('Content-Type: application/json; charset=utf-8');

            if (env('PLAYTECH_BRAND_ID') != $request['brandId']) 
            {
                log::debug(["requestId"=> $request['requestId'],"error"=>"P_03"]);
                return ["requestId"=> $request['requestId']
                ,"error"=>"P_03"];
            }

            // $convData = self::checkRawData($request);

            // if (md5($convData) != $request['hash']) 
            // {
            //     return ["requestId"=> $request['requestId']
            //     ,"error"=>"P_02"];
            // }
            $memberId = $request['playerId'];

            $db = DB::select("SELECT b.id, c.available, a.session_id
                            FROM pt_users_token a
                            LEFT JOIN member b ON a.member_id = b.id
                            LEFT JOIN member_credit c ON c.member_id = b.id
                            WHERE b.id = ?",
                             [$memberId]);

            if (sizeOf($db) == 0) 
            {
                log::debug(["requestId"=> $request['requestId'],"error"=>"P_08"]);

                return ["requestId"=> $request['requestId']
                ,"error"=>"P_07"];
            }

            $memberId = $db[0]->id;
            $balance = $db[0]->available;
            $sessionId = $db[0]->session_id;

            // log::debug($sessionId);
            // log::debug($request['playerSessionId']);

            // if ($sessionId != $request['playerSessionId']) 
            // {
            //     log::debug(["requestId"=> $request['requestId'],"error"=>"P_07"]);

            //     return ["requestId"=> $request['requestId']
            //     ,"error"=>"P_07"];
            // }

            $response = ['playerId'=>$memberId
                        ,'requestId'=> $request['requestId']
                        ,'currency'=>env('CURRENCY')
                        ,'balance'=>$balance
                        ,'bonusBalance'=>0
                        ,'error'=>0
                        ,'message'=>'Success'
                    ];

            // $convData = self::checkRawData($response);
            // $convData = md5($convData);

            // $response['hash'] = $convData;

            log::debug($response);


            return $response;
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ["requestId"=> $request['requestId']
                ,"error"=>"P_00"];
        }
    }

    public function transaction(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            log::debug('transaction');

            $request = $request->getContent();
            $request = json_decode($request,true);

            header('Content-Type: application/json; charset=utf-8');

            log::debug($request);


            if (env('PLAYTECH_BRAND_ID') != $request['brandId']) 
            {
                DB::rollback();
                log::debug(["requestId"=> $request['requestId'],"error"=>"P_03"]);
                return ["requestId"=> $request['requestId']
                ,"error"=>"P_03"];
            }

            log::debug($request);
            return;

            // $convData = self::checkRawData($request);

            // if (md5($convData) != $request['hash']) 
            // {
            //     return ["requestId"=> $request['requestId']
            //     ,"error"=>"P_02"];
            // }
            $memberId = $request['playerId'];

            $db = DB::select("SELECT b.id, c.available, a.session_id
                            FROM pt_users_token a
                            LEFT JOIN member b ON a.member_id = b.id
                            LEFT JOIN member_credit c ON c.member_id = b.id
                            WHERE b.id = ?",
                             [$memberId]);

            if (sizeOf($db) == 0) 
            {
                DB::rollback();

                log::debug(["requestId"=> $request['requestId'],"error"=>"P_08"]);

                return ["requestId"=> $request['requestId']
                ,"error"=>"P_07"];
            }

            $memberId = $db[0]->id;
            $balance = $db[0]->available;
            $sessionId = $db[0]->session_id;

            // log::debug($sessionId);
            // log::debug($request['playerSessionId']);

            // if ($sessionId != $request['playerSessionId']) 
            // {
            //     log::debug(["requestId"=> $request['requestId'],"error"=>"P_07"]);

            //     return ["requestId"=> $request['requestId']
            //     ,"error"=>"P_07"];
            // }

            $trans = $request['trans'];
            $gameCode = $request['gameCode'];
            $memberId = $request['playerId'];

            foreach ($trans as $t) 
            {
                $txnId = $t['transId'];
                $amount = $t['amount'];
                $transType = $t['transType'];
                $transTime = $t['transTime'];
                $roundId = $t['roundId'];
                $roundType = $t['roundType'];
                $endRound = $t['endRound'];

                // $roundId as txnId

                if ($transType == 'bet') 
                {
                    DB::insert("INSERT INTO pt_debit(txn_id,member_id,game_code,amount,ext_txn_id,round_id,txn_time)
                                VALUES(?,?,?,?,?,?,?)"
                                ,[$roundId,$memberId,$gameCode,$amount,$txnId,$roundId,$transTime]);

                    DB::update("UPDATE member_credit
                        SET available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

                    $balance = $balance - $amount;
                }
                else if (condition) 
                {
                    DB::insert("INSERT INTO pt_credit(txn_id,amount,ext_txn_id,round_id)
                                VALUES(?,?,?,?)"
                                ,[$roundId,$amount,$txnId,$roundId]);

                    DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

                    $balance = $balance + $amount;

                }

                
            }

            $response = ['playerId'=>$memberId
                        ,'requestId'=> $request['requestId']
                        ,'currency'=>env('CURRENCY')
                        ,'balance'=>$balance
                        ,'error'=>0
                    ];

            $convData = self::checkRawData($response);
            $convData = md5($convData);

            $response['hash'] = $convData;

            DB::commit();

            return $response;
            
        } 
        catch (Exception $e) 
        {
            DB::rollback();

            Log::debug($e);

            return ["requestId"=> $request['requestId']
                ,"error"=>"P_00"];
        }
    }

    public function payUp(Request $request)
    {
        try 
        {
            log::debug('payup');

            $request = $request->getContent();
            $request = json_decode($request,true);

            log::debug($request);
            return;
            

            if (env('PLAYTECH_BRAND_ID') != $request['brandId']) 
            {
                log::debug(["requestId"=> $request['requestId'],"error"=>"P_03"]);
                return ["requestId"=> $request['requestId']
                ,"error"=>"P_03"];
            }

            // $convData = self::checkRawData($request);

            // if (md5($convData) != $request['hash']) 
            // {
            //     return ["requestId"=> $request['requestId']
            //     ,"error"=>"P_02"];
            // }
            $memberId = $request['playerId'];

            $db = DB::select("SELECT b.id, c.available, a.session_id
                            FROM pt_users_token a
                            LEFT JOIN member b ON a.member_id = b.id
                            LEFT JOIN member_credit c ON c.member_id = b.id
                            WHERE b.id = ?",
                             [$memberId]);

            if (sizeOf($db) == 0) 
            {
                log::debug(["requestId"=> $request['requestId'],"error"=>"P_08"]);

                return ["requestId"=> $request['requestId']
                ,"error"=>"P_07"];
            }

            $memberId = $db[0]->id;
            $balance = $db[0]->available;
            $sessionId = $db[0]->session_id;

            // log::debug($sessionId);
            // log::debug($request['playerSessionId']);

            // if ($sessionId != $request['playerSessionId']) 
            // {
            //     log::debug(["requestId"=> $request['requestId'],"error"=>"P_07"]);

            //     return ["requestId"=> $request['requestId']
            //     ,"error"=>"P_07"];
            // }

            $trans = $request['trans'];
            $gameCode = $request['gameCode'];
            $memberId = $request['playerId'];

            foreach ($trans as $t) 
            {
                $txnId = $t['transId'];
                $amount = $t['amount'];
                $transType = $t['transType'];
                $transTime = $t['transTime'];
                $roundId = $t['roundId'];
                $roundType = $t['roundType'];
                $endRound = $t['endRound'];

                // $roundId as txnId

                if ($transType == 'bet') 
                {
                    DB::insert("INSERT INTO pt_debit(txn_id,member_id,game_code,amount,ext_txn_id,round_id,txn_time)
                                VALUES(?,?,?,?,?,?,?)"
                                ,[$roundId,$memberId,$gameCode,$amount,$txnId,$roundId,$transTime]);

                    DB::update("UPDATE member_credit
                        SET available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

                    $balance = $balance - $amount;
                }
                else if (condition) 
                {
                    DB::insert("INSERT INTO pt_credit(txn_id,amount,ext_txn_id,round_id)
                                VALUES(?,?,?,?)"
                                ,[$roundId,$amount,$txnId,$roundId]);

                    DB::update("UPDATE member_credit
                        SET available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$userId]);

                    $balance = $balance + $amount;

                }

                
            }

            $response = ['playerId'=>$memberId
                        ,'requestId'=> $request['requestId']
                        ,'currency'=>env('CURRENCY')
                        ,'balance'=>$balance
                        ,'error'=>0
                    ];

            $convData = self::checkRawData($response);
            $convData = md5($convData);

            $response['hash'] = $convData;

            DB::commit();

            return $response;
            
        } 
        catch (Exception $e) 
        {
            Log::debug($e);

            return ["requestId"=> $request['requestId']
                ,"error"=>"P_00"];
        }
    }

    public static function checkRawData($data)
    {
        try 
        {
            $rawData = '';
            $secret = env('PLAYTECH_SECRET_KEY');
            ksort($data);

            foreach ($data as $key => $value) 
            {
                if ($key == 'hash') 
                {
                    continue;
                }

                // $rawKey = strtolower($key);
                $rawValue = $value;

                if ($rawData != '') 
                {
                    $rawData = $rawData.'&';
                }

                $rawData = $rawData.$key.'='.$rawValue;
            }

            return $rawData.$secret;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return '';
        }   
    }
}
