<?php

namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use App\Http\Controllers\UserController;
use Log;
use DateTime;
use Auth;
use App;
use DES;

class SAController extends Controller
{
    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en_US'
                    ,'zh-cn'  => 'zh-Hans'
                    ,'ar'  => 'en'
                );

        return $locale[App::getLocale()];
    }

    public static function getGame($isMobile)
    {
        try 
        {
            $username = Auth::user()->username;
            //check status
            $status = Auth::user()->status;
            
            if ($status != 'a') 
            {
                return 'Inactive Member!';
            }

            $hostName = env('SA_HOSTNAME');
            $loader = env('SA_LOADER');
            $secretKey = env('SA_SECRET_KEY');
            $key = env('SA_ENCRYPTKEY');
            $md5key = env('SA_MD5Key');
            $lobbyCode = env('SA_LOBBY_CODE');
            $date = date('YmdHis', time());
            $currency = env('CURRENCY');
            $language = self::mapLocale();

            // $isMobile = false;

            // $language = 'en';
            $qs = "method=LoginRequest&Key=".$secretKey."&Time=".$date."&Username=".$username."&CurrencyType=".$currency;
            $s = md5($qs.$md5key.$date.$secretKey);

            $q = self::encrypt($qs,$key);

            $data = http_build_query(array('q' => $q, 's' => $s));

            $header = array('Content-Type: application/x-www-form-urlencoded');

            $response = Helper::postData($hostName,$data,$header);

            $xml = simplexml_load_string($response) or die("Error: Cannot create object");

            if ($xml->ErrorMsgId == 0)
            {
                $token = $xml->Token;
                $displayName = $xml->DisplayName;

                $launchURL = $loader.'?username='.$displayName.'&token='.$token.'&lobby='.$lobbyCode.'&mobile='.$isMobile;

                log::debug($launchURL);
                

                $response = ['status' => 1,
                            'iframe' => $launchURL];
            }
            else
            {
                $response = ['status' => 0,
                                'error' => 'INVALID_PARAMETER'];
            }

            return $response;

        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return $response = ['status' => 0,
                                'error' => 'INVALID_PARAMETER'];
        }
    }

    public static function encrypt($str,$key) 
    {
        return base64_encode(openssl_encrypt($str, 'DES-CBC', $key, OPENSSL_RAW_DATA, $key));
    }

    public static function decrypt($str,$key) 
    {
        $str = urldecode($str);

        $str = openssl_decrypt(base64_decode($str), 'DES-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $key);

        return rtrim($str, "\x1\x2\x3\x4\x5\x6\x7\x8");
    }

    //***********************************
    //  Call from provider
    //***********************************

    public function balance(Request $request)
    {
        try
        {
            $key = env('SA_ENCRYPTKEY');

            $data = $request->getContent();
            $data = self::decrypt($data,$key);

            $parts = parse_url($data);
            parse_str($parts['path'], $data);

            $username = $data['username'];
            $currency = $data['currency'];

            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->available;

                $response = [
                    "error" => 0,
                    "username" => $username,
                    "currency" => $currency,
                    "amount" => $balance
                ];
            }
            else
            {
                $response = [
                        "error" => 1000,
                        "description" => "User account doesn’t exist"
                    ];
            }

            log::debug(self::convertToXML($response));

            return self::convertToXML($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            $response = [
                        "error" => 9999,
                        "description" => "Internal Error"
                    ];

            return self::convertToXML($response);
        }
    }

    public function debit(Request $request)
    {
        try
        {
            $key = env('SA_ENCRYPTKEY');

            $data = $request->getContent();
            $data = self::decrypt($data,$key);

            $parts = parse_url($data);
            parse_str($parts['path'], $data);

            $username = $data['username'];
            $currency = $data['currency'];
            $amount = $data['amount'];
            $extTxnId = $data['txnid'];
            $txnId = $data['gameid'];
            $platform = $data['platform'];
            $gameType = self::getSAGame($data['gametype']);
            $timestamp = $data['timestamp'];

            //check user exist and map merchant details
            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?"
                            ,[$username]);

            if(sizeof($db) == 0)
            {
                $response = [
                        "error" => 1000,
                        "description" => "User account doesn’t exist"
                    ];

                return self::convertToXML($response);
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            //Insufficient balance
            if($balance < $amount)
            {
                $response = [
                        "error" => 1004,
                        "description" => "Insufficient balance"
                    ];

                return self::convertToXML($response);
            }

            //Difference currency
            if($currency != env("CURRENCY"))
            {
                $response = [
                        "error" => 1001,
                        "description" => "Invalid currency"
                    ];

                return self::convertToXML($response);
            }

            $checkExist = DB::select("
                                SELECT txn_id
                                FROM sa_debit 
                                WHERE txn_id = ?
                                    AND ext_txn_id = ?
                                    AND game_id = ?
                                ",
                                [$txnId,$extTxnId,$gameType]);

            //check is it betslip already exist in thier own db
            if (sizeOf($checkExist) != 0) 
            {
                $response = [
                        "error" => 1005,
                        "description" => "Debit already exist"
                    ];

                return self::convertToXML($response);
            }

            try
            {
                DB::insert("
                    INSERT INTO sa_debit 
                    (member_id,ext_txn_id,game_id,txn_id,amount,created_at)
                    VALUES
                    (?,?,?,?,?,?)"
                    ,[  $memberId
                        ,$extTxnId
                        ,$gameType
                        ,$txnId
                        ,$amount
                        ,$timestamp]);


                $addToBetSlip = true;
            } 
            catch(\Exception $e)
            {
                log::debug($e);
                $addToBetSlip = false;
            }

            if (!$addToBetSlip) 
            {
                $response = [
                        "error" => 1005,
                        "description" => "Debit betslip error"
                    ];

                return self::convertToXML($response);
            }

            //update balance
            $db = DB::update('
                UPDATE member_credit
                SET available = available - ?
                WHERE member_id = ?'
                ,[  $amount
                    ,$memberId]);

            $balance = $balance - $amount;

            $response = [
                    "error" => 0,
                    "username" => $username,
                    "currency" => $currency,
                    "amount" => $balance
                ];

            return self::convertToXML($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            $response = [
                        "error" => 9999,
                        "description" => "Internal Error"
                    ];

            return self::convertToXML($response);
        }
    }

    public function creditWin(Request $request)
    {
        try
        {
            $key = env('SA_ENCRYPTKEY');

            $data = $request->getContent();
            $data = self::decrypt($data,$key);

            $parts = parse_url($data);
            parse_str($parts['path'], $data);

            $username = $data['username'];
            $currency = $data['currency'];
            $amount = $data['amount'];
            $extTxnId = $data['txnid'];
            $txnId = $data['gameid'];
            $gametype = self::getSAGame($data['gametype']);
            $timestamp = $data['timestamp'];

            //check user exist and map merchant details
            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?"
                            ,[$username]);

            if(sizeof($db) == 0)
            {
                $response = [
                        "error" => 1000,
                        "description" => "User account doesn’t exist"
                    ];

                return self::convertToXML($response);
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            //Difference currency
            if($currency != env("CURRENCY"))
            {
                $response = [
                        "error" => 1001,
                        "description" => "Invalid currency"
                    ];

                return self::convertToXML($response);
            }

            $checkExist = DB::select("
                                SELECT txn_id 
                                FROM sa_credit 
                                WHERE txn_id = ?
                                ",
                                [$txnId]);


            $checkDebitExist = DB::select("
                            SELECT txn_id , amount
                            FROM sa_debit 
                            WHERE txn_id = ?
                            ",
                            [$txnId]);


            //check is it exist in debit where integrator_status = new (CREDIT)
            if (sizeOf($checkDebitExist) == 0) 
            {
                $response = [
                        "error" => 1005,
                        "description" => "Debit does not exist"
                    ];

                return self::convertToXML($response);
            }

            //check is it betslip already exist in thier own db
            if (sizeOf($checkExist) != 0) 
            {
                $response = [
                        "error" => 1005,
                        "description" => "Credit already exist"
                    ];

                return self::convertToXML($response);
            }

            DB::beginTransaction();

            try
            {
                 //pair the credit with unpaired debit by roundId
                $debitTxnId = $checkDebitExist[0]->txn_id;
                $debitAmt = $checkDebitExist[0]->amount;

                //insert transaction
                $db = DB::insert('
                        INSERT INTO sa_credit
                        (txn_id,ext_txn_id,type,amount
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,NOW())'
                        ,[  $txnId,$extTxnId,'c',$amount]);

                DB::commit();
            } 
            catch(\Exception $e)
            {
                log::debug($e);
                $response = [
                            "error" => 9999,
                            "description" => "Internal Error"
                        ];

                return self::convertToXML($response);
            }

            //update balance
            $db = DB::update('
                UPDATE member_credit
                SET available = available + ?
                WHERE member_id = ?'
                ,[  $amount
                    ,$memberId]);

            $balance = $balance + $amount;

            $response = [
                    "error" => 0,
                    "username" => $username,
                    "currency" => $currency,
                    "amount" => $balance
                ];

            return self::convertToXML($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            $response = [
                        "error" => 9999,
                        "description" => "Internal Error"
                    ];

            return self::convertToXML($response);
        }
    }

    public function creditLose(Request $request)
    {
        try
        {
            $key = env('SA_ENCRYPTKEY');

            $data = $request->getContent();
            $data = self::decrypt($data,$key);

            $parts = parse_url($data);
            parse_str($parts['path'], $data);

            $username = $data['username'];
            $currency = $data['currency'];
            $extTxnId = $data['txnid'];
            $txnId = $data['gameid'];
            $gametype = self::getSAGame($data['gametype']);
            $timestamp = $data['timestamp'];

            //check user exist and map merchant details
            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?"
                            ,[$username]);

            if(sizeof($db) == 0)
            {
                $response = [
                        "error" => 1000,
                        "description" => "User account doesn’t exist"
                    ];

                return self::convertToXML($response);
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            //Difference currency
            if($currency != env("CURRENCY"))
            {
                $response = [
                        "error" => 1001,
                        "description" => "Invalid currency"
                    ];

                return self::convertToXML($response);
            }

            $checkExist = DB::select("
                                SELECT txn_id 
                                FROM sa_credit 
                                WHERE txn_id = ?
                                ",
                                [$txnId]);


            $checkDebitExist = DB::select("
                            SELECT txn_id , amount
                            FROM sa_debit 
                            WHERE txn_id = ?
                            ",
                            [$txnId]);


            //check is it exist in debit where integrator_status = new (CREDIT)
            if (sizeOf($checkDebitExist) == 0) 
            {
                $response = [
                        "error" => 1005,
                        "description" => "Debit does not exist"
                    ];

                return self::convertToXML($response);
            }

            //check is it betslip already exist in thier own db
            if (sizeOf($checkExist) != 0) 
            {
                $response = [
                        "error" => 1005,
                        "description" => "Credit already exist"
                    ];

                return self::convertToXML($response);
            }

            DB::beginTransaction();

            try
            {
                //insert transaction
                $db = DB::insert('
                        INSERT INTO sa_credit
                        (txn_id,ext_txn_id,type,amount
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,NOW())'
                        ,[  $txnId,$extTxnId,'c',0]);

                DB::commit();
            } 
            catch(\Exception $e)
            {
                log::debug($e);
                $response = [
                            "error" => 9999,
                            "description" => "Internal Error"
                        ];

                return self::convertToXML($response);
            }

            $response = [
                    "error" => 0,
                    "username" => $username,
                    "currency" => $currency,
                    "amount" => $balance
                ];

            return self::convertToXML($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            $response = [
                        "error" => 9999,
                        "description" => "Internal Error"
                    ];

            return self::convertToXML($response);
        }
    }

    public function cancel(Request $request)
    {
        try
        {
            $key = env('SA_ENCRYPTKEY');

            $data = $request->getContent();
            $data = self::decrypt($data,$key);

            $parts = parse_url($data);
            parse_str($parts['path'], $data);

            $username = $data['username'];
            $currency = $data['currency'];
            $amount = $data['amount'];
            $extTxnId = $data['txnid'];
            $txnId = $data['gameid'];
            $gametype = self::getSAGame($data['gametype']);
            $timestamp = $data['timestamp'];

            //check user exist and map merchant details
            $db = DB::select("SELECT a.id, b.available
                            FROM member a
                            LEFT JOIN member_credit b ON a.id = b.member_id
                            WHERE a.username = ?"
                            ,[$username]);

            if(sizeof($db) == 0)
            {
                $response = [
                        "error" => 1000,
                        "description" => "User account doesn’t exist"
                    ];

                return self::convertToXML($response);
            }

            $balance = $db[0]->available;
            $memberId = $db[0]->id;

            //Difference currency
            if($currency != env("CURRENCY"))
            {
                $response = [
                        "error" => 1001,
                        "description" => "Invalid currency"
                    ];

                return self::convertToXML($response);
            }

            $checkExist = DB::select("
                                SELECT txn_id 
                                FROM sa_credit 
                                WHERE txn_id = ?
                                ",
                                [$txnId]);


            $checkDebitExist = DB::select("
                            SELECT txn_id , amount
                            FROM sa_debit 
                            WHERE txn_id = ?
                            ",
                            [$txnId]);


            //check is it exist in debit where integrator_status = new (CREDIT)
            if (sizeOf($checkDebitExist) == 0) 
            {
                $response = [
                        "error" => 1005,
                        "description" => "Debit does not exist"
                    ];

                return self::convertToXML($response);
            }

            //check is it betslip already exist in thier own db
            if (sizeOf($checkExist) != 0) 
            {
                $response = [
                        "error" => 1005,
                        "description" => "Credit already exist"
                    ];

                return self::convertToXML($response);
            }

            DB::beginTransaction();

            try
            {


                //insert transaction
                $db = DB::insert('
                        INSERT INTO sa_credit
                        (txn_id,ext_txn_id,type,amount
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,NOW())'
                        ,[  $txnId,$extTxnId,'x',$amount]);

                DB::commit();
            } 
            catch(\Exception $e)
            {
                log::debug($e);
                $response = [
                            "error" => 9999,
                            "description" => "Internal Error"
                        ];

                return self::convertToXML($response);
            }

            //update balance
            $db = DB::update('
                UPDATE member_credit
                SET available = available + ?
                WHERE member_id = ?'
                ,[  $amount
                    ,$memberId]);

            $balance = $balance + $amount;

            $response = [
                    "error" => 0,
                    "username" => $username,
                    "currency" => $currency,
                    "amount" => $balance
                ];

            return self::convertToXML($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            $response = [
                        "error" => 9999,
                        "description" => "Internal Error"
                    ];

            return self::convertToXML($response);
        }
    }

    public static function convertToXML($response)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $requestResponse = $xml->createElement("RequestResponse");
        foreach ($response as $key => $value) 
        {
            $xmlNode = $xml->createElement($key,$value);
            $requestResponse->appendChild($xmlNode);
        }
        $xml->appendChild($requestResponse);

        return $xml->saveXML();
    }

    public static function getSAGame($gameType)
    {
        $gameId = DB::select("SELECT id
                            FROM sa_games
                            WHERE game_type = ?",
                            [$gameType]);

        if (sizeOf($gameId) > 0) 
        {
            $gameId = $gameId[0]->id;
        }
        else
        {
            $id = DB::select("SELECT MAX(id) id
                            FROM sa_games");

            $gameId = $id[0]->id + 1;

            DB::insert('INSERT INTO sa_games(game_type,status)
                        VALUES (?,?)'
                        ,[$gameType,1]);
        }
        
        return $gameId;
    }
}