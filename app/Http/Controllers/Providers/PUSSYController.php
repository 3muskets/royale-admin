<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Providers;

use Auth;
use App;
use Log;
use Lang;
use DateTime;

//transfer
class PUSSYController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // log::debug('here');
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public static function createUser($account)
    {
        try
        {
            $url = env('PUSSY_API_URL_1').'/ashx/account/account.ashx';
            $secretKey = env('PUSSY_SECRET_KEY');
            $username = (string)$account;
            $name = Auth::user()->username;
            $memberId = Auth::id();

            $time = floor(microtime(true) * 1000);

            $authCode = env('PUSSY_AUTH');
            $sign = strtoupper(md5(strtolower($authCode.$username.$time.$secretKey)));
            $action = '?action=addUser';
            $agent = env('PUSSY_AGENT');
            $password = Helper::generateRandomString(8);
            $userType = 1; //1 正式玩家 100 代理级别

            $url = $url.$action.'&agent='.$agent.'&PassWd='.$password.'&pwdtype=1&UserAreaId=1&Name='.$name.'&userName='.$username.'&UserType='.$userType.'&time='.$time.'&authcode='.$authCode.'&sign='.$sign;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['success'] == true) 
            {
                DB::insert("INSERT INTO pussy_users (member_id, login_id, ft_password)
                            VALUES(?,?,?)"
                            ,[$memberId, $username, $password]);

                return ['success' => 1, 'login_id' => $username, 'ft_password' => $password];
            }
            else
            {
                return ['success' => 0, 'error_code' => $response['code']];
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'INTERNAL_ERROR'];
        }
    }

    public static function createAccount()
    {
        try 
        {
            $memberId = Auth::id();

            $db = DB::select("SELECT login_id, ft_password
                            FROM pussy_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if (sizeof($db) !== 0) 
            {
                return ['success' => 1, 'login_id' => $db[0]->login_id, 'ft_password' => $db[0]->ft_password];
            }

            $url = env('PUSSY_API_URL_1').'/ashx/account/account.ashx';
            $secretKey = env('PUSSY_SECRET_KEY');
            $username = env('PUSSY_USERNAME');

            $time = floor(microtime(true) * 1000);

            $authCode = env('PUSSY_AUTH');
            // $authCode = 'kissapimyrb';
            $sign = strtoupper(md5(strtolower($authCode.$username.$time.$secretKey)));
            $action = '?action=RandomUserName';

            $url = $url.$action.'&userName='.$username.'&time='.$time.'&authcode='.$authCode.'&sign='.$sign;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['success'] == true) 
            {
                $account = $response['account'];

                return self::createUser($account);
            }
            else
            {
                return ['success' => 0, 'error_code' => $response['code']];
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'INTERNAL_ERROR'];
        }
    }

    public static function launchGames()
    {
        try 
        {
            //mobile
            $userdlUrl = env('PUSSY_GAMEDL_URL');

            $response = self::createAccount();

            if ($response['success'] == 0) 
            {
                return ['success' => 0, 'error_code' => $response['error_code'], 'launch_url' => '', 'login_id' => '', 'ft_password' => ''];
            }

            return ['success' => 1, 'launch_url' => $userdlUrl, 'login_id' => $response['login_id'], 'ft_password' => $response['ft_password']];

        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return '';
        }
    }

    //add credit
    public static function setMemberScore($amount)
    {
        try 
        {
            $memberId = Auth::id();

            $db = DB::select("SELECT login_id
                            FROM pussy_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if(sizeof($db) == 0)
            {
                $response = self::createAccount();

                if ($response['success'] == 0) 
                {
                    return ['success' => 0, 'error_code' => $response['error_code'], 'launch_url' => '', 'login_id' => '', 'ft_password' => ''];
                }

                $username = $response['login_id'];
                $actionUser = $response['login_id'];
            }
            else
            {
                $username = $db[0]->login_id;
                $actionUser = $db[0]->login_id;
            }

            $url = env('PUSSY_API_URL_1').'/ashx/account/setScore.ashx';
            $action = '?action=setServerScore';
            $scoreNumber = $amount;
            $actionIp = $_SERVER['SERVER_ADDR'];
            $time = floor(microtime(true) * 1000);
            $authCode = env('PUSSY_AUTH');
            $secretKey = env('PUSSY_SECRET_KEY');
            $sign = strtoupper(md5(strtolower($authCode.$username.$time.$secretKey)));
            $type = ($amount>0)?'d':'w';

            $balanceBef = DB::select("SELECT available 
                        FROM member_credit
                        WHERE member_id = ?
                        FOR UPDATE"
                        ,[$memberId]);

            $balanceBef = $balanceBef[0]->available;

            DB::insert("INSERT INTO pussy_wallet_transfer(prd_id, member_id, transfer_to, type, amount, status, created_at)
                            VALUES(?,?,?,?,?,?,NOW())"
                            ,[Providers::PUSSY, Auth::id(), 'm', $type, $amount, 'n']);

            $db = DB::select("SELECT DISTINCT last_insert_id() as id FROM pussy_wallet_transfer");

            $orderId = $db[0]->id;

            $url = $url.$action.'&scoreNum='.$scoreNumber.'&orderid='.$orderId.'&userName='.$username.'&ActionUser='.$actionUser.'&ActionIp='.$actionIp.'&time='.$time.'&authcode='.$authCode.'&sign='.$sign;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['success'] == true) 
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        ,available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

                DB::update("UPDATE pussy_wallet_transfer
                            SET status = 'a'
                            WHERE id = ?"
                            ,[$orderId]);

                Helper::storeTxnCredit($orderId,Providers::PUSSY,$memberId,$balanceBef,$amount);

            }
            else
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        WHERE member_id = ?"
                        ,[$memberId]);

                DB::update("UPDATE pussy_wallet_transfer
                            SET status = 'x'
                                AND error_code = ?
                            WHERE id = ?"
                            ,[$orderId,$response['code']]);

                return ['success' => 0, 'error_code' => $response['code']];
            }

            return ['success' => 1, 'error_code' => $response['code'], 'balance' => $response['money']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => __('error.dw.internal_error')];
        }
    }

    //add agent credit
    public static function setAgentScore($amount)
    {
        try 
        {
            $url = env('PUSSY_API_URL_1').'/ashx/account/setScore.ashx';
            $action = '?action=setAgentScore';
            $scoreNumber = $amount;
            $username = env('PUSSY_AGENT');
            $actionUser = env('PUSSY_AGENT');
            $actionIp = $_SERVER['SERVER_ADDR'];
            $time = floor(microtime(true) * 1000);
            $authCode = env('PUSSY_AUTH');
            $secretKey = env('PUSSY_SECRET_KEY');
            $sign = strtoupper(md5(strtolower($authCode.$username.$time.$secretKey)));
            $type = ($amount>0)?0:1;

            DB::insert("INSERT INTO pussy_wallet_transfer(prd_id, member_id, transfer_to, type, amount, status, created_at)
                            VALUES(?,?,?,?,?,?,NOW())"
                            ,[Providers::PUSSY, Auth::id(), 'a', $type, $amount, 'n']);

            $db = DB::select("SELECT DISTINCT last_insert_id() as id FROM pussy_wallet_transfer");

            $orderId = $db[0]->id;

            $url = $url.$action.'&scoreNum='.$scoreNumber.'&orderid='.$orderId.'&userName='.$username.'&ActionUser='.$actionUser.'&ActionIp='.$actionIp.'&time='.$time.'&authcode='.$authCode.'&sign='.$sign;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['success'] == true) 
            {
                DB::update("UPDATE pussy_wallet_transfer
                            SET status = 'a'
                            WHERE id = ?"
                            ,[$orderId]);
            }
            else
            {
                DB::update("UPDATE pussy_wallet_transfer
                            SET status = 'x'
                                AND error_code = ?
                            WHERE id = ?"
                            ,[$orderId,$response['code']]);

                return ['success' => 0, 'error_code' => $response['code']];
            }

            return ['success' => 1, 'error_code' => $response['code'], 'balance' => $response['money']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }


    //get user information(undone)
    public static function getUserInfo()
    {
        try 
        {
            $response = self::createAccount();

            if ($response['success'] == 0) 
            {
                return ['status' => 0, 'error' => 'Create PUSSY Member failed'];
            }
            
            $db = DB::select("SELECT login_id
                            FROM pussy_users
                            WHERE member_id = ?"
                            ,[Auth::id()]);

            if(sizeof($db) == 0)
            {
                return ['status' => 0, 'error' => 'INVALID_LOGIN_ID'];
            }

            $url = env('PUSSY_API_URL_1').'/ashx/account/account.ashx';
            $action = '?action=getSearchUserInfo';
            $username = $db[0]->login_id;
            $time = floor(microtime(true) * 1000);
            $authCode = env('PUSSY_AUTH');
            $secretKey = env('PUSSY_SECRET_KEY');
            $sign = strtoupper(md5(strtolower($authCode.$username.$time.$secretKey)));

            $url = $url.$action.'&userName='.$username.'&time='.$time.'&authcode='.$authCode.'&sign='.$sign;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['success'] == true) 
            {
                return ['success' => 1, 'balance' => (double)$response['results'][0]['ScoreNum']];
            }
            else
            {
                 return ['success' => 0];
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public static function withdrawAllAmount()
    {
        try 
        {
            $getbalance = self::getUserInfo();
            $responseArr = [];

            if ($getbalance['success'] == 1) 
            {
                $response = self::setMemberScore(-$getbalance['balance']);
            }
            else
            {
                return ['success' => 0, 'error_code' => 'Get PUSSY balance failed'];
            }

            $responseArr[Providers::PUSSY] = $response;

            return $responseArr;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'PUSSY withdraw all: Internal Error'];
        }
    }

    public static function getGameLog()
    {
        try 
        {
            $db = DB::select("SELECT login_id, member_id
                            FROM pussy_users");

            foreach ($db as $users) 
            {
                $username = $users->login_id;
                $memberId = $users->member_id;

                $url = env('PUSSY_API_URL_2').'/ashx/GameLog.ashx';
                $time = floor(microtime(true) * 1000);
                $authCode = env('PUSSY_AUTH');
                $secretKey = env('PUSSY_SECRET_KEY');
                $sign = strtoupper(md5(strtolower($authCode.$username.$time.$secretKey)));
                $endDate = date('Y-m-d H:i:s', time() + 86400);
                $startDate = date("Y-m-d", strtotime('+8 hours'))."%2000:00:00";
                $endDate = date("Y-m-d", strtotime('+8 hours'))."%2023:59:59";
                $pageSize = 1000;

                $pageIndex = 1;

                for ($pageIndex=1; $pageIndex < 10000; $pageIndex++) 
                { 
                    if ($pageIndex!=1) 
                    {
                        sleep(1);
                    }
                    $url = env('PUSSY_API_URL_2').'/ashx/GameLog.ashx'.'?userName='.$username.'&sDate='.$startDate.'&eDate='.$endDate.'&time='.$time.'&authcode='.$authCode.'&sign='.$sign.'&pageSize='.$pageSize.'&pageIndex='.$pageIndex;

                    $response = Helper::getData($url);
                    $response = json_decode($response,true);

                    if ($response['code'] != 0) 
                    {
                        log::debug('Insert Pussy Game Log Error: '.$response['code'].' '.$response['msg']);
                        return;
                    }

                    if (sizeof($response['results']) == 0) 
                    {
                        break;
                    }

                    foreach ($response['results'] as $value) 
                    {
                        $beginBalance = $value['BeginBlance'];
                        $classId = $value['ClassID'];
                        $createTime = $value['CreateTime'];
                        $endBlance = $value['EndBlance'];
                        $gameID = $value['GameID'];
                        $win = $value['Win'];
                        $bet = $value['bet'];
                        $uuid = $value['uuid'];

                        DB::insert("INSERT INTO pussy_debit(txn_id, member_id, prd_id, category, class_id, begin_bal, end_bal, game_id, bet, created_time, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                                ON DUPLICATE KEY UPDATE
                                     begin_bal = VALUES(begin_bal)
                                        ,end_bal = VALUES(end_bal)
                                        ,member_id = VALUES(member_id)
                                        ,bet = VALUES(bet)
                                        ,created_time = VALUES(created_time)
                                        ,updated_at = NOW()
                                        "
                                ,
                                [
                                    $uuid,
                                    $memberId,
                                    Providers::PUSSY,
                                    3,
                                    $classId,
                                    $beginBalance,
                                    $endBlance,
                                    $gameID,
                                    $bet,
                                    $createTime,
                                ]);

                        //calculate PT and COMM
                        $db = DB::select('
                            SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                            FROM member a
                            INNER JOIN pt_eff b ON a.admin_id = b.admin_id
                            INNER JOIN admin_comm c ON a.admin_id = c.admin_id
                            WHERE a.id = ?
                                AND b.prd_id = ?'
                            ,[$memberId,Providers::PUSSY]);

                        $tier1PT = $db[0]->tier1_pt;
                        $tier2PT = $db[0]->tier2_pt;
                        $tier3PT = $db[0]->tier3_pt;
                        $tier4PT = $db[0]->tier4_pt;
                        $tier4Comm = $db[0]->comm;
                        $debitAmt = $bet;
                        $amount = $win;

                        $wlAmt = $debitAmt - $amount;

                        $tier4PTAmt = $wlAmt * ($tier4PT / 100);
                        Helper::removePrecision($tier4PTAmt);

                        $tier3PTAmt = $wlAmt * ($tier3PT / 100);
                        Helper::removePrecision($tier3PTAmt);

                        $tier2PTAmt = $wlAmt * ($tier2PT / 100);
                        Helper::removePrecision($tier2PTAmt);

                        $tier1PTAmt = $wlAmt - $tier4PTAmt - $tier3PTAmt - $tier2PTAmt;

                        $tier4CommAmt = $wlAmt * ($tier4Comm / 100);

                        $type = 'c';

                        //insert transaction
                        $db = DB::insert('
                                INSERT INTO pussy_credit
                                (prd_id,txn_id,type,amount
                                ,tier1_pt,tier2_pt,tier3_pt,tier4_pt
                                ,tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt
                                ,tier4_comm,tier4_comm_amt
                                ,created_at)
                                VALUES
                                (?,?,?,?
                                ,?,?,?,?
                                ,?,?,?,?
                                ,?,?
                                ,NOW())'
                                ,[  Providers::PUSSY,$uuid,$type,$win
                                    ,$tier1PT,$tier2PT,$tier3PT,$tier4PT
                                    ,$tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt
                                    ,$tier4Comm,$tier4CommAmt]);
                    }
                }
            }

            return 'Success';
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }
    public static function getAccountReport($memberId)
    {
        try 
        {
            $db = DB::select("SELECT login_id
                            FROM pussy_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if(sizeof($db) == 0)
            {
                return ['status' => 0, 'error' => 'INVALID_LOGIN_ID'];
            }

            $url = env('PUSSY_API_URL_1').'/ashx/AccountReport.ashx';
            $username = $db[0]->login_id;
            $time = floor(microtime(true) * 1000);
            $authCode = env('PUSSY_AUTH');
            $secretKey = env('PUSSY_SECRET_KEY');
            $sign = strtoupper(md5(strtolower($authCode.$username.$time.$secretKey)));
            $endDate = date('Y-m-d H:i:s', time() + 86400);
            $startDate = "2022-07-13 00:00:00";
            $endDate = "2022-07-13 23:59:59";

            $url = $url.'?userName='.$username.'&sDate='.$startDate.'&eDate='.$endDate.'&time='.$time.'&authcode='.$authCode.'&sign='.$sign;

            log::debug($url);

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            return $response;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }
}
