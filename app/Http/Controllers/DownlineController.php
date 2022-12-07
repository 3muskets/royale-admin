<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\CreditController;
use App\Http\Controllers\AdminCreditController;
use App\Http\Controllers\DownlineSettingController;
use App\Http\Controllers\Helper;

use Auth;
use Log;

class DownlineController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public static function checkUser(Request $request)
    {
        $error = '';

        $username = $request->input('username');

        if(strlen($username) >= 4 && strlen($username) <= 20 && Helper::checkInputFormat('alphanumeric', $username))
        {

            $db = DB::select('SELECT username FROM admin 
                WHERE username = ?',[$username]);

            if(sizeof($db) > 0)
            {
                $error = __('error.merchant.input.duplicate_username');
                return json_encode($error);
            }
            else
            {
                return json_encode($error);
            }
        }
        elseif(preg_match('/[^a-zA-Z0-9]/', $username))
        {
            $error = __('error.merchant.input.special_character');
            return json_encode($error);
        }
        elseif(!Helper::checkInputLength($username, 4, 20))
        {
            $error = __('error.merchant.input.invalid_username_length');
            return json_encode($error);
        }
    }

    // 1- Evo
    // 2- Haba
    // 3- Prag
    // 4- WM
    public static function create(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $username = $request->input('username');
            $fullname = $request->input('fullname');
            $status = $request->input('status');
            $password = $request->input('password');
            $confirmPassword = $request->input('confirmpassword');
            $currency = $request->input('currency');

            $user = Auth::user();
            $userId = $user->id;

            //validation
            $errMsg = [];


            $db = DB::select('SELECT username FROM admin 
                                WHERE username = ?',[$username]);

            if(sizeof($db) > 0)
            {
                array_push($errMsg, __('error.merchant.input.duplicate_username'));
            }


            //Validate username length - must between 4 to 20
            if(!Helper::checkInputLength($username, 4, 20))
            {
                array_push($errMsg, __('error.merchant.input.invalid_username_length'));
            }

            if(!Helper::checkInputFormat('alphanumeric', $username))
            {
                array_push($errMsg, __('error.merchant.input.special_character'));
            }

            //Validate password length - must between 8 to 15
            if(!Helper::checkInputLength($password, 8, 15))
            {
                array_push($errMsg, __('error.merchant.invalid_password_length'));
            }

            if(!Helper::checkInputFormat('alphanumericwithSymbol', $password))
            {
                array_push($errMsg, __('error.merchant.password.input'));
            }

            if($password != $confirmPassword)
            {
                array_push($errMsg, __('error.merchant.passwords_not_match'));
            }

            if(!Helper::checkInputFormat('alphanumeric', $fullname))
            {
                array_push($errMsg,  __('error.merchant.fullname.alphanumeric'));
            }

            if(!Helper::checkInputLength($fullname, 4, 20))
            {
                array_push($errMsg, __('error.merchant.fullname.4_20'));
            }

            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                array_push($errMsg, __('error.merchant.invalid_status'));
            }

            if(!Helper::checkValidOptions(self::getOptionsCurrency(),$currency))
            {
                array_push($errMsg, __('error.merchant.invalid_currency'));
            }


            if($errMsg)
            {
                $response = ['status' => 0
                ,'error' => $errMsg
                ];

                return json_encode($response);

            }   

            $sql2 = "
                INSERT INTO admin(username,password,status,suspended,ws_channel,created_at,fullname,is_sub)
                VALUES(:username,:password,:status,0,:wschannel,NOW(),:fullname,0)
                ";

            $params2 = [
                    'username' => strtoupper($username)
                    ,'password' => Hash::make($password)
                    ,'status' => $status
                    ,'fullname' => $fullname
                    ,'wschannel' => strtoupper($username)
                ];

            $data2 = DB::insert($sql2,$params2);

            $id = DB::getPdo()->lastInsertId();

            DB::update("UPDATE admin
                        SET admin_id = ?
                        WHERE id = ?
                        "
                        ,[$id, $id]);

            $sql3 = "
                 INSERT INTO tiers(admin_id) VALUES(:id)
                ";

            $params3 = [
                    'id' => $id
                ];

            $data3 = DB::insert($sql3,$params3);

            $sql4 = "
                INSERT INTO admin_currency(admin_id, currency_cd)
                VALUES(:id,:currency)
                ";

            $params4 = [
                    'id' => $id
                    ,'currency' => $currency
                ];

            $data4 = DB::insert($sql4,$params4);

            $txnId = Helper::prepareRefId(1); // 1- admin_credit_txn 



            //logging
            $request["log_old"] = "{}";
            $request["password"] = "*";
            $request["username"] = strtoupper($username);
            $request["action_details"] = 1;
            Helper::log($request,'Create');

            $response = ['status' => 1];

            DB::commit();

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            $errMsg = '';

            if($e instanceof \PDOException) 
            {
                if($e->errorInfo[1] == 1062)
                    $errMsg = __('error.merchant.duplicate_username');
            }

            if($errMsg == '')
                $errMsg = __('error.merchant.internal_error');
            
            $response = ['status' => 0
                        ,'error' => $errMsg
                        ];

            DB::rollback();

            return json_encode($response);
        }   
    }

    public static function update(Request $request) 
    {
        DB::beginTransaction();
        
        try 
        {
            $merchantCode = $request->input('merchant_code');
            $id = $request->input('id');
            $fullname = $request->input('fullname');
            $status = $request->input('status');
            $suspended = $request->input('suspended');


            if(!Helper::checkInputFormat('alphanumeric', $fullname))
            {

                $response = ['status' => 0
                            ,'error' => __('error.merchant.fullname.alphanumeric')
                            ];

                return json_encode($response);
            }

            if(!Helper::checkInputLength($fullname, 4, 20))
            {

                $response = ['status' => 0
                            ,'error' => __('error.merchant.fullname.4_20')
                            ];

                return json_encode($response);
            }

            //validation for status
            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                $response = ['status' => 0
                            ,'error' => __('error.merchant.invalid_status')
                            ];

                return json_encode($response);
            }

            //validation for suspended
            if($suspended != 0 && $suspended != 1)
            {
                $response = ['status' => 0
                            ,'error' => __('error.merchant.invalid_suspended')
                            ];

                return json_encode($response);
            }
            
         

            DB::update("UPDATE admin
                        SET fullname = ? , status = ?, suspended = ? , login_token = NULL
                        WHERE id = ?"
                        ,[$fullname, $status, $suspended, $id]);


            if($status == 'a')
            {
                $downLineStatus = null;
            }
            else
            {
                $downLineStatus = 1;
            }

            if($suspended == 0)
            {
                $downLineSuspend = null;
            }
            else
            {
                $downLineSuspend = 1;
            }


            //update member status
            $dbMember = DB::select('SELECT id
                                    FROM member 
                                    admin_id =?'
                                    ,[$id]
                                );

            foreach($dbMember as $data)
            {   

                DB::update('UPDATE member
                            SET up1_inactive = ?
                            WHERE id = ?'
                            ,[$downLineStatus,$data->id]);
               
            }

            
            //logging
            $request["username"] = $merchantCode;
            $request["action_details"] = 2;
            Helper::log($request,'Update');
                
            $response = ['status' => 1];


            DB::commit();

            return json_encode($response);

        } 
        catch (\Exception $e) 
        {
            DB::rollback();
            log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.merchant.internal_error')
                        ];
            return json_encode($response);
        }
    }

    public static function getList(Request $request) 
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $username = $request->input('username');
            $tier = $request->input('tier');
            $startLastLogin = $request->input('slast_login');
            $endLastLogin = $request->input('elast_login');
            $startLastDeposit = $request->input('slast_deposit');
            $endLastDeposit = $request->input('elast_deposit');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            if($startLastLogin == null)
                $startLastLogin = '';
            else
                $startLastLogin = date('Y-m-d 00:00:00',strtotime($startLastLogin));

            if($endLastLogin == null)
                $endLastLogin = '';
            else
                $endLastLogin = date('Y-m-d 23:59:59',strtotime($endLastLogin));

            if($startLastDeposit == null)
                $startLastDeposit = '';
            else
                $startLastDeposit = date('Y-m-d 00:00:00',strtotime($startLastDeposit));

            if($endLastDeposit == null)
                $endLastDeposit = '';
            else
                $endLastDeposit = date('Y-m-d 23:59:59',strtotime($endLastDeposit));

            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));


            $user = Auth::user();
            $userLevel = $user->level;
            $adminId = $user->admin_id;
            $level = '';   
            $bypass = '';    



                $sql = "
                        SELECT a.id, a.mobile, a.email, a.username, a.status, a.suspended, a.wallet_address, a.is_duplicate_ip
                        , (a.created_at + INTERVAL 8 HOUR) 'created_at', (a.last_login + INTERVAL 8 HOUR) 'last_login'
                        , a.last_ip, b.available, c.bank, c.acc_no, c.name
                        , (b.member_deposit_request + INTERVAL 8 HOUR) 'member_deposit'
                        , (b.member_withdraw_request + INTERVAL 8 HOUR) 'member_withdraw'
                        , (b.admin_deposit_response + INTERVAL 8 HOUR) 'admin_deposit' 
                        , (b.admin_withdraw_response + INTERVAL 8 HOUR) 'admin_withdraw' 
                        , d.unread_msg
                        FROM member a
                        LEFT JOIN member_credit b ON a.id = b.member_id
                        LEFT JOIN member_bank_info c ON a.id = c.member_id
                        LEFT JOIN 
                        (
                            SELECT a.id, sum(CASE WHEN b.is_read = 0 AND b.send_by = 'm' AND b.is_deleted IS NULL 
                                            THEN 1 else 0 END) 'unread_msg'
                                    FROM member a
                                    LEFT JOIN member_msg b ON a.id = b.member_id
                                    WHERE a.admin_id = :id1
                                    GROUP BY a.id
                        ) d ON a.id = d.id
                        WHERE a.username LIKE :username
                            AND a.admin_id = :id
                            AND (:slastlogin = '' OR (a.last_login + INTERVAL 8 HOUR) >= :slastlogin1)
                            AND (:elastlogin = '' OR (a.last_login + INTERVAL 8 HOUR) <= :elastlogin1)
                            AND (:sdeposit = '' OR (b.admin_deposit_response + INTERVAL 8 HOUR) >= :sdeposit1)
                            AND (:edeposit = '' OR (b.admin_deposit_response + INTERVAL 8 HOUR) <= :edeposit1)
                            AND (:start_date = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date1)
                            AND (:end_date = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date1)
 
                    ";

                $params = 
                [
                    'username' => '%' . $username . '%'
                    ,'id' => $tier
                    ,'id1' => $tier
                    ,'slastlogin' => $startLastLogin
                    ,'slastlogin1' => $startLastLogin
                    ,'elastlogin' => $endLastLogin
                    ,'elastlogin1' => $endLastLogin
                    ,'sdeposit' => $startLastDeposit
                    ,'sdeposit1' => $startLastDeposit
                    ,'edeposit' => $endLastDeposit
                    ,'edeposit1' => $endLastDeposit
                    ,'start_date' => $startDate
                    ,'start_date1' => $startDate
                    ,'end_date' => $endDate
                    ,'end_date1' => $endDate
  
                ];

                $orderByAllow = ['id','username','created_at'];



            
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $db = Helper::paginateData($sql,$params,$page, 500);

            $aryStatus = self::getOptionsStatus();
            $arySuspended = self::getOptionsSuspended();


            foreach($db['results'] as $d)
            {

                $d->status_desc = Helper::getOptionsValue($aryStatus, $d->status);
                $d->suspended_desc = Helper::getOptionsValue($arySuspended, $d->suspended);
            
                if($level != 4)
                {
                    if($d->level == 1)
                    {
                        $d->maxEvoPt = 100;
                        $d->maxHabaPt = 100;
                        $d->maxPragPt = 100;
                        $d->maxWmPt = 100;
                    }
                    else
                    {
                        $d->maxEvoPt = $maxEvo;
                        $d->maxHabaPt = $maxHaba;
                        $d->maxPragPt = $maxPrag;
                        $d->maxWmPt = $maxWm;
                    }

                }
                else
                {
                    $d->duplicate_desc = Helper::getOptionsValue($arySuspended, $d->is_duplicate_ip);
                }

            }

            return json_encode($db);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }

    public static function changePassword(Request $request)
    {
        try 
        {
            $user_id = $request->input('id');
            $password = $request->input('password');

                //validation
            $errMsg = [];

            if(!$password)
            {
                array_push($errMsg, __('error.admin.invalid_password'));
            }

            if(!Helper::checkInputLength($password, 8, 15))
            {
                array_push($errMsg, __('error.admin.invalid_password_length'));
            }

            if(!Helper::checkInputFormat('alphanumericwithSymbol', $password))
            {
                array_push($errMsg, __('error.merchant.password.input'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                ,'error' => $errMsg
            ];

            return json_encode($response);
        }

        $new_password = Hash::make($password);

        $db = DB::update('UPDATE admin SET password = ? WHERE id = ?',
            [$new_password,$user_id]
        );

                //logging
        $request["password"] = "*";

        Helper::log($request,'update');

        $response = ['status' => 1];

        return json_encode($response);

        } 
        catch (\Exception $e) 
        {
            $response = ['status' => 0
            ,'error' => __('error.admin.internal_error')
        ];

        return json_encode($response);
        }
    }

    public static function checkIsOwnDownLine($tier,$type)
    {
        try
        {
            $id = Auth::user()->admin_id;
            $userLevel = Auth::user()->level;

            if($userLevel == 0)
                return true;

            //If type a is $tier is admin id
            if($type == 'a')
            {
                $db = DB::select('SELECT admin_id
                            FROM tiers 
                            WHERE admin_id = ? AND (up1_tier = ? OR up2_tier = ?)
                            ',[$tier,$id,$id]
                        );

            }
            //If type m is $tier is member id
            else if($type == 'm')
            {
                $db = DB::select('SELECT id 
                            FROM member a
                            LEFT JOIN tiers b
                             ON a.admin_id = b.admin_id
                            WHERE a.id = ?
                             AND (b.up1_tier = ? OR b.up2_tier = ? OR b.admin_id = ?)
                            ',[$tier,$id,$id,$id]
                        );
            }

            if(sizeof($db) == 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return false;
        }
    }

    public static function getOptionsStatus()
    {
        return  [
            ['a', __('option.merchant.active')]
            ,['i', __('option.merchant.inactive')]
        ];
    }

    public static function getOptionsSuspended()
    {
        return  [
            ['0', __('option.merchant.suspended.no')]
            ,['1', __('option.merchant.suspended.yes')]
        ];
    }

    public static function getOptionsCurrency()
    {
        return [
            
            ['MYR', 'MYR']
            // ,['CNY', 'CNY']
            // ,['USD', 'USD']

        ];
    }

    public static function getAvailableCurrency($mercId)
    {
        $db = DB::select('SELECT currency_cd 
            FROM admin_currency 
            WHERE admin_id = :id', 
            ['id' => $mercId]);

        $currency = $db[0]->currency_cd;

        return $currency;
    }


}
