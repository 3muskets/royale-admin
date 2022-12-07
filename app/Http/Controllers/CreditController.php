<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;
use App\Http\Controllers\AdminCreditController;

use Auth;
use Log;

class CreditController extends Controller
{

    public static function getCreditBalance($mercId)
    {
        $select = DB::select('SELECT available FROM admin_credit WHERE admin_id=?',[$mercId]);

        $availableCredit = $select[0]->available;

        return $availableCredit;
    }

    public static function getCreditList(Request $request) 
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $username = $request->input('username');
            $tier = $request->input('tier');

            $user = Auth::user();
            $userLevel = $user->level;
            $adminId = $user->admin_id;
            $availableCredit = '';

            if($userLevel != 0)
            {
                $availableCredit = self::getCreditBalance($adminId);
            }
            else
            {
                $availableCredit = AdminCreditController::getCreditBalance();
            }
            
            $level = '';   
            $bypass = '';         

            if($tier == null)
            {
                $level = $userLevel + 1;
                $tier = $adminId;
            }
            else
            {
                $level = Helper::getLevelByTier($tier);

                if($userLevel > $level)
                    $level = '';
                else
                    $level += 1;

                $checkOwnDownTier = DownlineController::checkIsOwnDownLine($tier,'a');

                if($checkOwnDownTier == false)
                {
                    $tier = '';
                }
            }

            if($level == 1)
            {
                $bypass = 1;     
            }

            if($level == 4)
            {
                $sql = "
                    SELECT a.id, a.username, b.available
                    FROM member a
                    LEFT JOIN member_credit b
                        ON a.id = b.member_id
                    WHERE a.username LIKE :username
                        AND a.admin_id = :id
                    ";

                $params = 
                [
                    'username' => '%' . $username . '%'
                    ,'id' => $tier

                ];
            }
            else
            {
                $sql = "
                    SELECT a.id, a.username, a.level, b.available
                    FROM admin a
                    LEFT JOIN admin_credit b
                        ON a.admin_id = b.admin_id
                    LEFT JOIN tiers c
                        ON a.admin_id = c.admin_id
                    WHERE a.username LIKE :username
                        AND (c.up1_tier = :id OR :bypass = 1)
                        AND a.level = :level
                        AND ifnull(a.is_sub,0) = 0
                    ";

                $params = 
                [
                    'username' => '%' . $username . '%'
                    ,'id' => $tier
                    ,'bypass' => $bypass
                    ,'level' => $level
                ];
            }

            

            $orderByAllow = ['username'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
            
            $data = Helper::paginateData($sql,$params,$page, 500);

            return Response::make(json_encode([$data,$availableCredit]), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }

    public static function getMemberCreditList(Request $request) 
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $username = $request->input('username');

            $user = Auth::user();
            $id = $user->admin_id;
            $level = $user->level;

            if($id == 1)
                $id = '';

            $sql = "
                    SELECT a.id, a.username 'username', b.available 'available'
                    , d.level
                    FROM member a
                    LEFT JOIN member_credit b
                        ON a.id = b.member_id
                    LEFT JOIN admin d 
                           ON a.admin_id = d.id
                    WHERE a.username LIKE :username
                    AND (a.admin_id = :id OR :id2 = '')
                    ";

            $params = 
            [
                'username' => '%' . $username . '%'
                ,'id' => $id
                ,'id2' => $id

            ];

            $orderByAllow = ['username'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
            
            $data = Helper::paginateData($sql,$params,$page, 500);


            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }



    public static function memberCreditTransfer(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            $memberID = $request->input('id');
            $memberName = $request->input('username');
            $amount = $request->input('amount');
            $actionType = $request->input('type'); //1-deposit 2-withdraw 3-adjustment
            $amount = str_replace( ',', '', $amount);
            $remarks = $request->input('remarks');

            $adjType = $request->input('adj_type'); //1-add 2-deduct

            $user = Auth::user();
            $userId = $user->id;

            $txnId = Helper::prepareRefId(2);
            $isAdjustment = null;

            $errMsg = [];
           
            if(!Helper::checkInputFormat('numeric',$amount))
            {
                array_push($errMsg, __('error.member.credit.is_numeric'));
            }

            if($amount < 0)
            {
               array_push($errMsg, __('error.credit.member.nonnegative')); 
            }

            if(!Helper::validAmount($amount))
            {
                array_push($errMsg, __('error.merchant.invalid_credit_length'));
            }

            if($adjType != 1 && $adjType != 2)
            {
                array_push($errMsg, __('error.merchant.invalid_adjustment_type'));
            }




            //adjustment add = deposit
            if($adjType == 1 && $actionType == 3)
            {
                $isAdjustment = 1;
                $actionType = 1;
            }
            //adjustment deduct = withdraw
            else if($adjType == 2 && $actionType == 3)
            {
                $isAdjustment = 1;
                $actionType = 2;
            }

            $db = DB::select("SELECT a.id, b.username, a.admin_id, IFNULL(b.level,0) 'level' 
                                      FROM member a
                                      LEFT JOIN admin b
                                      ON a.admin_id = b.id
                                      WHERE a.id = ?",
                                      [$memberID]); 

            if(sizeof($db) > 0)
            {
                $tier4 = $db[0]->admin_id;
                $uplineCode = $db[0]->username;
                $level = $db[0]->level;
            }


            //check the member's credit avaialble for withdraw
            $select = DB::select('SELECT available 
                                      FROM member_credit 
                                      WHERE member_id = ? FOR UPDATE',
                                      [$memberID]); 

            if(sizeof($select) > 0)
            {
                $availableCredit = $select[0]->available;
            }


            $tier4 = 0;
            $uplineCode = 'COMPANY';

            if($actionType == '1')
            {
                $typeTo = 2;

                $beforeFrom = $availableCredit; //member
                $remarkFrom = 'Member Add Credit, From '.$uplineCode;
            }
            else if($actionType == '2')
            {
                
                $typeTo = 3;

                $beforeFrom = $availableCredit; //member
                $remarkFrom = 'Member Sub Credit, From '.$uplineCode;


                $amount = -$amount;
            }

            if($remarks != '')
            {
                $remarkFrom = $remarks;
                $remarkTo = $remarks;
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            $memberTxnSql = "
                            INSERT INTO member_credit_txn (ref_id,type,member_id,credit_before,amount,credit_by,remark,is_adjustment)
                            VALUES  (:refid,:type,:member,:before,:amount,:by,:remark,:isAdjustment)
                            ";

            $memberTxnParams = [

                    'refid' => $txnId
                    ,'type' => $typeTo
                    ,'member' => $memberID
                    ,'before' => $beforeFrom
                    ,'amount' => $amount
                    ,'by' => $userId
                    ,'remark' => $remarkFrom
                    ,'isAdjustment' => $isAdjustment

                ];

            DB::insert($memberTxnSql,$memberTxnParams);


            DB::UPDATE('UPDATE member_credit 
                                  SET available = available+? 
                                  WHERE member_id = ?', 
                                  [$amount,$memberID]// update the amount for member
                  );


            $response = ['status' => 1];
            DB::commit();
            return json_encode($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            DB::rollback();
            $response = ['status' => 0
                        ,'error' =>  __('error.credit.member.invalid_credit')
                        ];

            return json_encode($response);
        }
    }

    public static function multipleMemberCreditTransfer(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            $check = $request->input('check');
            $credit = $request->input('credit');
            $actionType = $request->input('type'); //1-deposit 2-withdraw
            $credit = str_replace( ',', '', $credit);

            $user = Auth::user();
            $userId = $user->id;

            $errMsg = [];
           
            if(!Helper::checkInputFormat('numeric',$credit))
            {
                array_push($errMsg, __('error.member.credit.is_numeric'));
            }

            if($credit < 0)
            {
               array_push($errMsg, __('error.credit.member.nonnegative')); 
            }

            if(!Helper::validAmount($credit))
            {
                array_push($errMsg, __('error.merchant.invalid_credit_length'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $sql = "
                        SELECT a.member_id, a.available, b.admin_id, b.username 'username', c.username 'upline'
                        FROM member_credit a
                        LEFT JOIN member b
                        ON a.member_id = b.id
                        LEFT JOIN admin c
                        ON b.admin_id = c.id
                        WHERE a.member_id IN (?) FOR UPDATE
                ";

            $params = [$check];

            $pdo = Helper::prepareWhereIn($sql,$params);
            
            $data = DB::select($pdo['sql'],$pdo['params']);

            if(sizeof($data) > 0)
            {
                $paramsCredit = [];
                $paramsCreditTxn = [];
                $paramsAdmCreditTxn = [];
                $paramsAdmCredit = [];
                $admCredit = [];
                $response = [];
                $memberCount = [];
                $memberList = [];
                $memberCreditBefore = [];
                $paramsMember = [];

                foreach($data as $d)
                {
                    $memberId = $d->member_id;
                    $memberName = $d->username;
                    $creditBefore = $d->available;
                    $tier4Id = $d->admin_id;
                    $uplineCode = $d->upline;

                    if($actionType == 1)
                    {
                        //determine member count by agent
                        if(!isset($memberCount[$tier4Id]['total']))
                        {
                            $memberCount[$tier4Id]['total'] = 0;
                        }

                        if(!isset($memberCount[$tier4Id]['upline']))
                        {
                            $memberCount[$tier4Id]['upline'] = $uplineCode;   
                        }

                        $memberCount[$tier4Id]['total'] += 1;
                        $memberList[$tier4Id]['id'][] = $memberId;
                        $memberList[$tier4Id]['username'][] = $memberName;
                        $memberCreditBefore[$memberId] = $creditBefore;
                    }
                    else
                    {
                        $remarkFrom = 'Member Sub Credit, From '.$uplineCode;

                        if($credit > $creditBefore)
                        {
                            $message = ['status' => 0,
                                    'message' => __('error.credit.member.exceed_limit'),
                                    'member' => $memberId];
                        }
                        else
                        {
                            //determine mmeber count by agent
                            if(!isset($memberCount[$tier4Id]['total']))
                            {
                                $memberCount[$tier4Id]['total'] = 0;
                            }

                            $memberCount[$tier4Id]['total'] += 1;    
                            $memberList[$tier4Id]['id'][] = $memberId;
                            $memberList[$tier4Id]['username'][] = $memberName;

                            $message = ['status' => 1,
                                        'message' => __('common.modal.success'),
                                        'member' => $memberId];

                            array_push($paramsCredit,[$memberId,-$credit]);
                            array_push($paramsCreditTxn,['3',$memberId,$creditBefore,-$credit,$userId,$remarkFrom]);
                        }

                        array_push($response, $message);
                       
                    }
                }

                if($actionType == 1)
                {
                    foreach(array_keys($memberCount) as $tier4Id)
                    {
                        $ttlDepositAmt = $memberCount[$tier4Id]['total'] * $credit;
                        $uplineCode = $memberCount[$tier4Id]['upline'];

                        $db = DB::select('SELECT available FROM admin_credit WHERE admin_id=? FOR UPDATE',[$tier4Id]);

                        $tier4Credit = $db[0]->available;

                        $members = $memberList[$tier4Id]['id'];

                        if($ttlDepositAmt > $tier4Credit)
                        {
                            foreach ($members as $memberId) 
                            {
                                $message = ['status' => 0,
                                        'message' => __('error.credit.member.insufficient_credit'),
                                        'member' => $memberId];

                                array_push($response, $message);
                            } 
                        }
                        else
                        {
                                
                            $memberName = implode(",",$memberList[$tier4Id]['username']);
                            $remarkTo = 'To '.$memberName;

                            array_push($paramsAdmCredit,[$tier4Id,-$ttlDepositAmt]);
                            array_push($paramsAdmCreditTxn,['4',$tier4Id,$tier4Credit,-$ttlDepositAmt,$userId,$remarkTo]);

                            foreach ($members as $memberId) 
                            { 
                                $remarkFrom = 'Member Add Credit, From '.$uplineCode;

                                $beforeFrom = $memberCreditBefore[$memberId];

                                $message = ['status' => 1,
                                        'message' => __('common.modal.success'),
                                        'member' => $memberId];

                                array_push($paramsCredit,[$memberId,$credit]);
                                array_push($paramsCreditTxn,['2',$memberId,$beforeFrom,$credit,$userId,$remarkFrom]);

                                array_push($paramsMember,$memberId);

                                array_push($response, $message);
                            }
                        }
         
                    }
                }
                else
                {
                    foreach(array_keys($memberCount) as $tier4Id)
                    {
                        $ttlWithdrawAmt = $memberCount[$tier4Id]['total'] * $credit;

                        $db = DB::select('SELECT available FROM admin_credit WHERE admin_id=? FOR UPDATE',[$tier4Id]);

                        $tier4Credit = $db[0]->available;

                        $memberName = implode(",",$memberList[$tier4Id]['username']);

                        $remarkTo = 'From '.$memberName;

                        array_push($paramsAdmCredit,[$tier4Id,$ttlWithdrawAmt]);
                        array_push($paramsAdmCreditTxn,['3',$tier4Id,$tier4Credit,$ttlWithdrawAmt,$userId,$remarkTo]);
         
                    }
                }

                if($paramsCredit)
                {
                    //update product_credit
                    $sql = "
                        INSERT INTO member_credit
                        (member_id,available)
                        VALUES :(?,?):
                        ON DUPLICATE KEY UPDATE 
                        available = available + VALUES(available)
                        ";

                    $pdo = Helper::prepareBulkInsert($sql,$paramsCredit);

                    DB::insert($pdo['sql'],$pdo['params']);
                 
                    $sql = " 
                            INSERT INTO member_credit_txn (type,member_id,credit_before,amount,credit_by,remark)
                                VALUES :(?,?,?,?,?,?):
                            ";

                    $pdo = Helper::prepareBulkInsert($sql,$paramsCreditTxn);

                    DB::insert($pdo['sql'],$pdo['params']);



                    if($actionType == 1)
                    {
                        
                        foreach($paramsMember as $m)
                        {
                            $db = DB::select('SELECT sum(amount) "ttl_deposit" FROM member_credit_txn WHERE member_id = ? AND type != 3',[$m]);

                            if(sizeOf($db) > 0)
                            {
                                $ttlDepositAmt = $db[0]->ttl_deposit;
                            }


                            $select = DB::select('SELECT max(id) "level_id" FROM member_lvl WHERE min_deposit_amt <= ?',[$ttlDepositAmt]);

                            if(sizeof($select) > 0)
                            {
                                $level = $select[0]->level_id;
                            }


                            DB::update("
                                UPDATE member
                                SET level_id = ?
                                WHERE id = ?"
                                ,[$level
                                  ,$m]
                                );

                        }
                    }


                    //update product_credit
                    $sql = "
                        INSERT INTO admin_credit
                        (admin_id,available)
                        VALUES :(?,?):
                        ON DUPLICATE KEY UPDATE 
                        available = available + VALUES(available)
                        ";

                    $pdo = Helper::prepareBulkInsert($sql,$paramsAdmCredit);

                    DB::insert($pdo['sql'],$pdo['params']);
                 
                    $sql = " 
                            INSERT INTO admin_credit_txn (type,admin_id,credit_before,amount,credit_by,remark)
                                VALUES :(?,?,?,?,?,?):
                            ";

                    $pdo = Helper::prepareBulkInsert($sql,$paramsAdmCreditTxn);

                    DB::insert($pdo['sql'],$pdo['params']);
                }   
            }            

            DB::commit();

            return json_encode($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            DB::rollback();
            $response = ['status' => 0
                        ,'error' =>  __('error.credit.member.invalid_credit')
                        ];

            return json_encode($response);
        }
    }

}