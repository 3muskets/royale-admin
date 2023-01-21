<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\MerchantSettingController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\AdminCreditController;

use Auth;
use Log;

class MemberController extends Controller
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
    public static function getList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            $username = $request->input('username');
            $startLastLogin = $request->input('slast_login');
            $endLastLogin = $request->input('elast_login');
            $startLastDeposit = $request->input('slast_deposit');
            $endLastDeposit = $request->input('elast_deposit');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $agentId = $request->input('agent_id');

            if($agentId == null)
                $agentId = '';
            
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

            if($adminId == 1)
                $adminId = '';

            $sql = "
                    SELECT a.id, a.mobile, a.username, a.status, a.suspended,a.wallet_address, a.is_duplicate_ip,a.admin_id
                        , (a.created_at + INTERVAL 8 HOUR) 'created_at', (a.last_login + INTERVAL 8 HOUR) 'last_login'
                        , a.last_ip, b.available, c.bank, c.acc_no, c.name,e.username 'agent'
                        , d.unread_msg
                        , a.level_id
                        , a.address
                    FROM member a
                    LEFT JOIN member_credit b ON a.id = b.member_id
                    LEFT JOIN member_bank_info c ON a.id = c.member_id
                    LEFT JOIN 
                    (
                        SELECT a.id, sum(CASE WHEN b.is_read = 0 AND b.send_by = 'm' AND b.is_deleted IS NULL 
                                        THEN 1 else 0 END) 'unread_msg'
                                FROM member a
                                LEFT JOIN member_msg b ON a.id = b.member_id
                                GROUP BY a.id
                    ) d ON a.id = d.id
                    LEFT JOIN admin e
                        ON a.admin_id = e.id
                    WHERE a.username LIKE :username
                        AND (:slastlogin = '' OR (a.last_login + INTERVAL 8 HOUR) >= :slastlogin1)
                        AND (:elastlogin = '' OR (a.last_login + INTERVAL 8 HOUR) <= :elastlogin1)
                        AND (:sdeposit = '' OR (b.admin_deposit_response + INTERVAL 8 HOUR) >= :sdeposit1)
                        AND (:edeposit = '' OR (b.admin_deposit_response + INTERVAL 8 HOUR) <= :edeposit1)
                        AND (:start_date = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date1)
                        AND (:end_date = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date1)
                        AND (a.admin_id = :admin_id OR :admin_id1 = '')
                        AND (a.admin_id = :agent_id OR :agent_id1 = '')
                    ";

            $params = 
            [
                'username' => '%' . $username . '%'
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
                ,'admin_id'=> $adminId
                ,'admin_id1'=> $adminId
                ,'agent_id'=> $agentId
                ,'agent_id1'=> $agentId
            ];

            $orderByAllow = ['id','username','created_at'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page, 500);

            $aryStatus = self::getOptionsStatus();
            $arySuspended = self::getOptionsSuspended();

            foreach($data['results'] as $d)
            {
                $d->status_desc = Helper::getOptionsValue($aryStatus, $d->status);
                $d->suspended_desc = Helper::getOptionsValue($arySuspended, $d->suspended);
                $d->duplicate_desc = Helper::getOptionsValue($arySuspended, $d->is_duplicate_ip);

            }


            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }

    public static function create(Request $request)
    {
        DB::beginTransaction();

        try
        {
            $username = $request->input('username');
            $status = $request->input('status');
            $password = $request->input('password');
            $mobile = $request->input('mobile');
            $email = $request->input('email');
            $confirmPassword = $request->input('confirmpassword');
            $credit = $request->input('credit');
            $credit = str_replace( ',', '', $credit);

            $user = Auth::user();
            $userId = $user->id;
            $userLevel = $user->level;
            $mercId = $user->admin_id;

            if($userLevel == '0')
            {
                $availableCredit = AdminCreditController::getCreditBalance();
                $mercId = 1;
            }
            else
            {
                $availableCredit = CreditController::getCreditBalance($mercId);

            }

            //validation
            $errMsg = [];

            $select = DB::select('SELECT username FROM member WHERE username=?',[$username]);

            if(sizeOf($select) > 0)
            {
                array_push($errMsg, __('error.member.input.duplicate_username'));
            }

            $db = DB::select('SELECT email FROM member WHERE email=?',[$email]);

            if(sizeOf($db) > 0)
            {
                array_push($errMsg, __('error.member.input.duplicate_email'));
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
            {
                array_push($errMsg, __('error.member.input.invalid_email'));
            }

            //Validate username length - must between 4 to 10
            if(!Helper::checkInputLength($username, 4, 20))
            {
                array_push($errMsg, __('error.member.input.invalid_username_length'));
            }

            if(!Helper::checkInputFormat('alphanumeric', $username))
            {
                array_push($errMsg, __('error.member.input.special_character'));
            }

            if(!is_numeric($mobile))
            {
                array_push($errMsg, __('error.member.input.mobile_numeric'));
            }

            if (!$password) 
            {
                array_push($errMsg,__('error.member.invalid_password'));
            }

            if(!Helper::checkInputLength($password, 8, 15))
            {
                array_push($errMsg, __('error.member.invalid_password_length'));
            }

            if(!Helper::checkInputFormat('alphanumericwithSymbol', $password))
            {
                array_push($errMsg, __('error.member.password.input'));
            }

            if($password != $confirmPassword)
            {
                array_push($errMsg, __('error.member.passwordsnotmatch'));
            }

            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                array_push($errMsg, __('error.member.invalid_status'));
            }

            if (!Helper::checkInputFormat('numeric',$credit)) 
            {
                array_push($errMsg, __('error.member.credit.is_numeric'));
            }

            if($credit < 0)
            {
               array_push($errMsg, __('error.member.credit.nonnegative')); 
            }

            if(!Helper::validAmount($credit))
            {
                array_push($errMsg, __('error.member.invalid_credit_length'));
            }

            if($credit > $availableCredit)
            {
                array_push($errMsg,  __('error.member.insufficient_credit'));
            }

            // return error msg
            if($errMsg)
            {

                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            
            $select = DB::select('SELECT max(id) "level_id" FROM member_lvl WHERE min_deposit_amt <= ?',[$credit]);

            $level = 1;

            if(sizeof($select) != 0)
            {
                $level = $select[0]->level_id;
            }


            // insert users details
            $sql = DB::insert("
                INSERT INTO member(username,level_id,password,email,mobile,suspended,status,created_at,admin_id)
                VALUES(?,?,?,?,?,0,?,NOW(),?)
                "
                ,[  strtoupper($username)
                    ,$level
                    ,Hash::make($password)
                    ,$email
                    ,$mobile
                    ,$status
                    ,$mercId
                ]);

            $memberId = DB::getPdo()->lastInsertId();

            //credit
            $creditSql = "
                    INSERT INTO member_credit(member_id, available, dw_turnover)
                    VALUES(:memberid,:available,0)
                    ";

            $creditParams = [
                    'memberid' => $memberId
                    ,'available' => $credit
                ];

            DB::insert($creditSql,$creditParams);

            //member bonus turnover 
            $sql = "
                    INSERT INTO member_bonus_turnover(member_id, category,turnover)
                    VALUES(:memberid,1,0),(:memberid1,2,0),(:memberid2,3,0)
                    ";

            $params = [
                    'memberid' => $memberId
                    ,'memberid1' => $memberId
                    ,'memberid2' => $memberId
                ];

            DB::insert($sql,$params);

            if($userLevel != '0')
            {
                // update merchant balance 
                $sql = "
                        UPDATE admin_credit
                        SET available = available - :credit
                        WHERE admin_id= :id
                                ";

                $params = [
                    'credit' => $credit
                    ,'id' => $mercId
                ];
            }
            else
            {
                 // update ca balance when create member
                $sql = "
                                    UPDATE ca_credit
                                    SET available = available - :credit
                                ";

                 $params = [
                        'credit' => $credit
                    ];

            }

            DB::update($sql,$params);

             //member bank info
            DB::insert('INSERT INTO member_bank_info(member_id)
                    VALUES (?)'
                    ,[$memberId]);

            

            if($userLevel != '0')
            {
                $db = DB::select('SELECT username FROM admin WHERE id=?',[$mercId]);

                if(sizeOf($db) > 0)
                {
                    $merchantCode = $db[0]->username;
                }

                $remarkFrom = 'From '.$merchantCode;
            }
            else
            {
                $mercId = 0;
                $remarkFrom = 'From Company';
            }

            $remarkTo = 'To '.strtoupper($username);
            $before = $availableCredit;

            $txnId = Helper::prepareRefId(2); // 2- member_credit_txn

            $memberTxnSql = "
                            INSERT INTO member_credit_txn (ref_id,type,member_id,credit_before,amount,credit_by,remark)
                            VALUES (:refid,1,:memberid,0,:amount,:by,:remark)
                            ";

            $memberTxnParams = [

                    'refid' => $txnId
                    ,'memberid' => $memberId
                    ,'amount' => $credit
                    ,'by' => $userId
                    ,'remark' => $remarkFrom

                ];

            $memberTxnDb = DB::insert($memberTxnSql,$memberTxnParams);

            $adminTxnSql = "
                            INSERT INTO admin_credit_txn (ref_id,type,admin_id,credit_before,amount,credit_by,remark)
                            VALUES (:refid,5,:id,:before,:amount,:by,:remark)
                            ";

            $adminTxnParams = [

                    'refid' => $txnId
                    ,'id' => $mercId
                    ,'amount' => -$credit
                    ,'before' => $before
                    ,'by' => $userId
                    ,'remark' => $remarkTo

                ];

            $adminTxnDb = DB::insert($adminTxnSql,$adminTxnParams);

            //logging
            $request["password"] = "*";
            $request["username"] = $username;
            // $request["action_details"] = 14;
            $request["log_old"] = "{}";

            Helper::log($request,'Create');

            $response = ['status' => 1];

            DB::commit();

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            log::debug($e);

            $errMsg = '';

            if($e instanceof \PDOException) 
            {
                if($e->errorInfo[1] == 1062)
                    $errMsg = __('error.member.duplicate_username');
            }

            if($errMsg == '')
                $errMsg = __('error.member.internal_error');
            
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
            $username = $request->input('username');
            $id = $request->input('id');
            $status = $request->input('status');    
            $suspended = $request->input('suspended');        

            $errMsg = [];

            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                array_push($errMsg, __('error.merchant.invalid_status'));
            }

            // return error msg
            if($errMsg)
            {

                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            if($status == 'i') 
            {
                DB::update("UPDATE member
                        SET login_token = NULL
                        WHERE id = ?"
                        ,[$id]);
            }

            DB::update("
                        UPDATE member
                        SET status = ?, suspended = ?
                        WHERE id= ?
                        "
                        ,[
                            $status
                            ,$suspended
                            ,$id
                        ]);

                
            $response = ['status' => 1];

            Helper::log($request,'Update');
              
            DB::commit();

            return json_encode($response);

        } 
        catch (\Exception $e) 
        {
            DB::rollback();
            log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.member.internal_error')
                        ];
            return json_encode($response);
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
                array_push($errMsg, __('error.member.password.input'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                ,'error' => $errMsg
            ];

            return json_encode($response);
            }

            $new_password = Hash::make($password);

            $db = DB::update('UPDATE member SET password = ? WHERE id = ?',
                [$new_password,$user_id]
            );

            //logging
            $request["password"] = "*";

            Helper::log($request,'update');

            $response = ['status' => 1];

            return json_encode($response);

        } 
        catch (Exception $e) 
        {
            $response = ['status' => 0
            ,'error' => __('error.admin.internal_error')
        ];

        return json_encode($response);
        }
    }



    public static function getOptionsSuspended()
    {
        return  [
            ['0', __('option.member.suspended.no')]
            ,['1', __('option.member.suspended.yes')]
        ];
    }

    public static function getOptionsStatus()
    {
        return  [
                ['a',__('option.member.active')]
                ,['i',__('option.member.inactive')]
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

    public static function checkUser(Request $request)
    {
        $error = '';

        $username = $request->input('username');

        if(strlen($username) >= 4 && strlen($username) <= 20 && Helper::checkInputFormat('alphanumeric', $username))
        {

            $db = DB::select('SELECT username FROM member 
                WHERE username = ?',[$username]);

            if(sizeof($db) > 0)
            {
                $error = __('error.member.input.duplicate_username');
                return json_encode($error);
            }
            else
            {
                return json_encode($error);
            }
        }
        elseif(preg_match('/[^a-zA-Z0-9]/', $username))
        {
            $error = __('error.member.input.special_character');
            return json_encode($error);
        }
        elseif(!Helper::checkInputLength($username, 4, 20))
        {
            $error = __('error.member.input.invalid_username_length');
            return json_encode($error);
        }
    }
}

