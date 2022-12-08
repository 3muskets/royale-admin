<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\Helper;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\AdminCreditController;
use App\Events\DWRequest;
use App\Http\Controllers\Providers;

use Auth;
use Log;

class MemberDWController extends Controller
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
    public static function getPendingCount()
    {
        try
        {
            $user = Auth::user();
            $userId = $user->id;
            $tierId = $user->admin_id;
            $tierLvl = $user->level;


            if($tierId == 1)
                $tierId = '';

            //get tier 1,2,3 pending request count
            $db = DB::select("
                SELECT COUNT(a.*) 'count'
                FROM member_dw a
                LEFT JOIN member b
                ON a.member_id = b.id
                WHERE a.status = 'n'
                AND (b.admin_id = ? OR '' = '' )
                ",[$tierId,$tierId]
            );

            return $db[0]->count;

            
        } 
        catch (\Exception $e) 
        {
            return 0;
        }
    }

    public static function getList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            //filter  
            $tier4Name = $request->input('tier4_name');
            $memberName = $request->input('member_name');
            $status = $request->input('status');
            $type = $request->input('type');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if(!$status)
                $status = '';

            if(!$type)
                $type = '';

            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));

            $user = Auth::user();
            $userId = $user->id;
            $tierId = $user->admin_id;

            if($tierId == 1)
                $tierId = '';


            $sql = "
                    SELECT a.admin_id
                        ,b.member_id,a.username,b.remark
                        ,a.is_duplicate_ip,a.is_duplicate_bank
                        ,b.id,b.type,b.amount,b.status,b.admin_bank_id
                        ,b.bank,b.payment_type,b.payment_gateway_status,b.ref_id,b.member_name,b.member_bank_acc, b.dw_date, b.image
                        ,(b.created_at + INTERVAL 8 HOUR) 'created_at',(b.updated_at + INTERVAL 8 HOUR) 'updated_at'
                        ,d.bank 'admin_bank', d.name 'admin_acc_name' , d.acc_no 'admin_acc_no', f.username 'confirm_by'
                        ,g.promo_name
                    FROM member a
                    INNER JOIN member_dw b ON a.id = b.member_id
                    LEFT JOIN admin_bank_info d ON d.id = b.admin_bank_id
                    LEFT JOIN member_credit_txn e ON b.id = e.dw_id
                    LEFT JOIN admin f ON f.id = e.credit_by
                    LEFT JOIN promo_setting g ON b.promo_id = g.promo_id 
                    WHERE a.username LIKE ?
                        AND (b.status = ? OR ? = '')
                        AND (b.type = ? OR ? = '')
                        AND (? = '' OR (b.created_at + INTERVAL 8 HOUR) >= ?)
                        AND (? = '' OR (b.created_at + INTERVAL 8 HOUR) <= ?)
                        AND b.payment_type != 'c'
                        AND (a.admin_id = ? OR ? = '')
                    ";

            $params = [
                        '%'.$memberName.'%'
                        ,$status
                        ,$status
                        ,$type
                        ,$type
                        ,$startDate
                        ,$startDate
                        ,$endDate
                        ,$endDate  
                        ,$tierId  
                        ,$tierId  
                    ];

            $pdo = Helper::prepareWhereIn($sql,$params);
            $sql = $pdo['sql'];
            $params = $pdo['params'];

            $pdo = Helper::convertSQLBindingParams($sql,$params);
            $sql = $pdo['sql'];
            $params = $pdo['params'];

            $orderByAllow = ['id','admin_username','username','amount','created_at','updated_at'];
            $orderByDefault = 'created_at desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page, 500);


            foreach($data['results'] as $d)
            {
                $d->type_text = Helper::getOptionsValue(self::getOptionsType(), $d->type);
                $d->status_text = Helper::getOptionsValue(self::getOptionsStatus(), $d->status);
                $d->payment_type_text = Helper::getOptionsValue(self::getOptionsPaymentType(), $d->payment_type);


                $d->pymt_gateway_status_text = Helper::getOptionsValue(self::getOptionsPaymentGatewayStatus(), $d->payment_gateway_status);

                $d->duplicate_ip_desc =  Helper::getOptionsValue(self::getOptionDuplicate(), $d->is_duplicate_ip);
                $d->duplicate_bank_desc =  Helper::getOptionsValue(self::getOptionDuplicate(), $d->is_duplicate_bank);

                if($user->level == 3)
                {
                    unset($d->admin_id);
                    unset($d->admin_username);
                }
            }

            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::Debug($e);
            return [];
        }
    }

    public static function approve(Request $request)
    {
        try
        {

            //map request to variable
            $txnId = $request->id;
            $adminBankId = $request->admin_bank_id;

            $user = Auth::user();
            $userId = $user->id;
            $tierId = $user->admin_id;

            $level = $user->level;

            //check is user's txn and get info
            $db = DB::select("
                SELECT b.id,a.type,a.amount,a.promo_id,a.admin_bank_id
                FROM member_dw a
                LEFT JOIN member b ON a.member_id = b.id
                WHERE a.id = ?
                AND a.payment_type != 'c'
                "
                ,[$txnId]);

            if(sizeOf($db) == 0)
            {
                DB::rollback();

                $response = ['status' => 0
                            ,'error' =>  __('error.memberdw.invalid_process')
                            ];

                return json_encode($response);
            }


            $type = $db[0]->type;

            //attach info to request
            $request->member_id = $db[0]->id;
            $request->amount = $db[0]->amount;
            $request->promo_id = $db[0]->promo_id;
            
            if($adminBankId != null)
                $request->admin_bank_id = $db[0]->admin_bank_id;



            if($type == 'd')
                return self::approveDeposit($request);
            else
                return self::approveWithdraw($request);

        }
        catch(\Exception $e)
        {
            log::Debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.memberdw.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function approveDeposit(Request $request)
    {
        DB::beginTransaction();
        

        try
        {

            //map request to variable
            $txnId = $request->id;
            $memberId = $request->member_id;
            $amount = $request->amount;
            $promoId = $request->promo_id;


            $dwRemark = $request->remark;


            $user = Auth::user();
            $userId = $user->id;
            $tierId = $user->admin_id;

            $level = $user->level;

            $refId = Helper::prepareRefId(2);

            $promoAmt = 0;


            // get and lock balance
            $db = DB::select('
                    SELECT available
                    FROM member_credit
                    WHERE member_id = ? 
                    FOR UPDATE'
                    ,[$memberId]);

            $balance = $db[0]->available;


            //member detail
            $beforeFrom = $balance; 

            //check promo is it available
            if($promoId != '')
            {
                $checkPromo = BonusController::checkPromo($memberId,$promoId);

                if($checkPromo['status'] == 0)
                {
                    return json_encode($checkPromo);
                }
                else
                {
                    //promo detail include rate and turnover multiple value
                    $promoDetail = BonusController::getPromoDetail($promoId);
                    
                    $promoAmt = $amount*($promoDetail['rate']/100);

                    $amount = $amount + $promoAmt;
                }
            }



            $typeTo = 2;
            $remarkFrom = 'Member Add Credit, From ADMIN';

            $uplineCode = 'ADM1';

            if($promoId != '')
                $remarkFrom = 'Member Add Credit, From '.$uplineCode.'(With Promotion Bonus)';

            $sql = "
                    INSERT INTO member_credit_txn (ref_id,type,member_id,credit_before,amount,credit_by,dw_id,remark)
                    VALUES  (:refid,:type,:member,:before,:amount,:by,:dwId,:remark)
                    ";

            $params = [

                    'refid' => $refId
                    ,'type' => $typeTo
                    ,'member' => $memberId
                    ,'before' => $beforeFrom
                    ,'amount' => $amount
                    ,'by' => $userId
                    ,'dwId' => $txnId
                    ,'remark' => $remarkFrom

                ];

            DB::insert($sql,$params);

            if($promoAmt != 0)
            {
                $sql = "
                        INSERT INTO member_promo_turnover (promo_id,member_id,deposit_amount,promo_amount,turnover,target_turnover,win_loss,status)
                        VALUES (:promoId,:memberId,:depositAmt,:promoAmt,:turnover,:targetTurnover,:winloss,:status)
                        ";

                $params = [

                        'promoId' => $promoId
                        ,'memberId' => $memberId
                        ,'depositAmt' => $amount-$promoAmt
                        ,'promoAmt' => $promoAmt
                        ,'turnover' => 0
                        ,'targetTurnover' => $amount*$promoDetail['turnover_multiple']
                        ,'winloss' => 0
                        ,'status' => 'p'
                    ];  

                DB::insert($sql,$params);
            }


            $balance = $balance + $amount;


            $subject = "Deposit Approved";
            $message = "Dear Player, Your account Top-up request has been approved! Current account balance is MYR ". $balance;

            DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                            VALUES(?,0,"a",?,?,NOW())',[$memberId,$message,$subject]);

            //update txn status
            $db = DB::update("
                    UPDATE member_dw
                    SET status = 'a'
                        ,remark = ?
                        ,updated_at = NOW()
                    WHERE id = ?
                        AND status = 'n'"
                    ,[$dwRemark,$txnId]);

            $txnUpdated = $db;

            if(!$txnUpdated)
            {
                DB::rollback();

                $response = ['status' => 0
                            ,'error' =>  __('error.memberdw.txnprocess')
                            ];

                return json_encode($response);
            }

            //update balance and admin response deposit date
            $db = DB::update('
                UPDATE member_credit
                SET available = available + ?, admin_deposit_response = NOW() 
                WHERE member_id = ?'
                ,[  $amount
                    ,$memberId]);



            //no error
            DB::commit();

            //notification
            self::sendWS($memberId);
            
            $response = ['status' => 1];
            return json_encode($response);
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            DB::rollback();

            $response = ['status' => 0
                        ,'error' => __('error.memberdw.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function approveWithdraw(Request $request)
    {
        DB::beginTransaction();

        try
        {
            //map request to variable
            $txnId = $request->id;
            $memberId = $request->member_id;
            $amount = $request->amount;

            $user = Auth::user();
            $userId = $user->id;
            $tierId = $user->admin_id;

            $level = $user->level;
            
            $dwRemark = $request->remark;


            //check member have pending promotion
            $db = DB::select("
                    SELECT status
                    FROM member_promo_turnover
                    WHERE status = 'p'
                    AND member_id = ?
                    ",[$memberId]
                );

            if(sizeof($db) != 0)
            {

                $response = ['status' => 0
                            ,'error' =>  __('Withdraw Failed,Please Contact Member to Complete Promotion')
                            ];

                return json_encode($response);

            }


            // get and lock balance
            $db = DB::select('
                    SELECT available
                    FROM member_credit
                    WHERE member_id = ? 
                    FOR UPDATE'
                    ,[$memberId]);

            $balance = $db[0]->available;


            $refId = Helper::prepareRefId(2);


            $typeTo = 3;
            $beforeFrom = $balance + $amount; //
            $remarkFrom = 'Member Sub Credit, From ADMIN';


            $sql = "
                    INSERT INTO member_credit_txn (ref_id,type,member_id,credit_before,amount,credit_by,dw_id,remark)
                    VALUES  (:refid,:type,:member,:before,:amount,:by,:dwId,:remark)
                    ";

            $params = [

                    'refid' => $refId
                    ,'type' => $typeTo
                    ,'member' => $memberId
                    ,'before' => $beforeFrom
                    ,'amount' => -$amount
                    ,'by' => $userId
                    ,'dwId' => $txnId
                    ,'remark' => $remarkFrom

                ];

            DB::insert($sql,$params);

            //update admin response member withdraw date 
            $db = DB::UPDATE('
                UPDATE member_credit 
                          SET admin_withdraw_response = NOW() 
                          WHERE member_id = ?', 
                          [$memberId] 
              );


   
            $subject = "Withdraw Approved";
            $message = "Dear Player, Your funds withdraw request has been approved! The amount of you withdraw is KRW ".$amount.". Current account balance is KRW ".$balance;

            DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                            VALUES(?,0,"a",?,?,NOW())',[$memberId,$message,$subject]);

            //update txn status
            $db = DB::update("
                    UPDATE member_dw
                    SET status = 'a'
                        ,remark = ?
                        ,updated_at = NOW()
                    WHERE id = ?
                        AND status = 'n'"
                    ,[$dwRemark,$txnId]);

            $txnUpdated = $db;

            if(!$txnUpdated)
            {
                DB::rollback();

                $response = ['status' => 0
                            ,'error' =>  __('error.memberdw.txnprocess')
                            ];

                return json_encode($response);
            }

            //no error
            DB::commit();

            //notification
            self::sendWS($memberId);

            $response = ['status' => 1];
            return json_encode($response);
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            DB::rollback();

            $response = ['status' => 0
                        ,'error' => __('error.memberdw.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function reject(Request $request)
    {
        try
        {
            //map request to variable
            $txnId = $request->id;

            $user = Auth::user();
            $userId = $user->id;
            $tierId = $user->admin_id;
            $level = $user->level;

            //for SMA and AG
            if($level != 0)
            {

                //check is user's txn and get info
                $db = DB::select("
                    SELECT b.id,a.type,a.amount
                    FROM member_dw a
                    LEFT JOIN member b ON a.member_id = b.id
                    WHERE a.id = ?
                        AND b.admin_id = ?
                    "
                    ,[$txnId,$tierId]);

                if(sizeOf($db) == 0)
                {
                    //check is it crypto and sma for the txn
                    $db = DB::select("
                    SELECT b.id,a.type,a.amount
                    FROM member_dw a
                    LEFT JOIN member b ON a.member_id = b.id
                    LEFT JOIN tiers c ON b.admin_id = c.admin_id
                    WHERE a.id = ?
                        AND a.payment_type = ?
                        AND c.up2_tier = ?
                    "
                    ,[$txnId,'x',$tierId]);

                    if(sizeOf($db) == 0)
                    {
                        DB::rollback();

                        $response = ['status' => 0
                                    ,'error' =>  __('error.memberdw.invalid_process')
                                    ];

                        return json_encode($response);
                    }
                }
            }
            else
            {
                //check is user's txn and get info
                $db = DB::select("
                    SELECT b.id,a.type,a.amount
                    FROM member_dw a
                    LEFT JOIN member b ON a.member_id = b.id
                    WHERE a.id = ?
                    "
                    ,[$txnId]);

                if(sizeOf($db) == 0)
                {
                    DB::rollback();

                    $response = ['status' => 0
                                ,'error' =>  __('error.memberdw.invalid_process')
                                ];

                    return json_encode($response);
                }
               
            }

            $type = $db[0]->type;

            //attach info to request
            $request->member_id = $db[0]->id;
            $request->amount = $db[0]->amount;

            if($type == 'd')
                return self::rejectDeposit($request);
            else
                return self::rejectWithdraw($request);

        }
        catch(\Exception $e)
        {
            $response = ['status' => 0
                        ,'error' => __('error.memberdw.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function rejectDeposit(Request $request)
    {
        DB::beginTransaction();

        try
        {
            //map request to variable
            $txnId = $request->id;
            $memberId = $request->member_id;
            $amount = $request->amount;

            $user = Auth::user();
            $userId = $user->id;
            $tierId = $user->admin_id;

            $dwRemark = $request->remark;

            //update txn status
            $db = DB::update("
                    UPDATE member_dw
                    SET status = 'r'
                        ,remark = ?
                        ,updated_at = NOW()
                    WHERE id = ?
                        AND status = 'n'"
                    ,[$dwRemark,$txnId]);

            $txnUpdated = $db;

            if(!$txnUpdated)
            {
                DB::rollback();

                $response = ['status' => 0
                            ,'error' =>  __('error.memberdw.txnprocess')
                            ];

                return json_encode($response);
            }

            //update admin response member deposit date 
            $db = DB::UPDATE('
                UPDATE member_credit 
                          SET admin_deposit_response = NOW() 
                          WHERE member_id = ?', 
                          [$memberId] 
              );

            $subject = "Deposit Rejected";
            $message = "Dear Player, Your account Top-up request has been rejected!";

            DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                            VALUES(?,0,"a",?,?,NOW())',[$memberId,$message,$subject]);

            //no error
            DB::commit();

            //notification
            self::sendWS($memberId);

            $response = ['status' => 1];
            return json_encode($response);
        }
        catch(\Exception $e)
        {
            DB::rollback();

            $response = ['status' => 0
                        ,'error' => __('error.memberdw.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function rejectWithdraw(Request $request)
    {
        DB::beginTransaction();

        try
        {
            //map request to variable
            $txnId = $request->id;
            $memberId = $request->member_id;
            $amount = $request->amount;

            $user = Auth::user();
            $userId = $user->id;
            $tierId = $user->admin_id;

            $dwRemark = $request->remark;

            // get and lock balance
            $db = DB::select('
                    SELECT available
                    FROM member_credit
                    WHERE member_id = ? 
                    FOR UPDATE'
                    ,[$memberId]);

            $balance = $db[0]->available;

            //update txn status
            $db = DB::update("
                    UPDATE member_dw
                    SET status = 'r'
                        ,remark = ?
                        ,updated_at = NOW()
                    WHERE id = ?
                        AND status = 'n'"
                    ,[$dwRemark,$txnId]);

            $txnUpdated = $db;

            if(!$txnUpdated)
            {
                DB::rollback();

                $response = ['status' => 0
                            ,'error' =>  __('error.memberdw.txnprocess')
                            ];

                return json_encode($response);
            }

            //update balance and admin response withdraw date
            $db = DB::update('
                UPDATE member_credit
                SET available = available + ?, admin_withdraw_response = NOW() 
                WHERE member_id = ?'
                ,[  $amount
                    ,$memberId]);

            $subject = "Withdraw Rejected";
            $message = "Dear Player, Your funds withdraw request has been rejected! For more details please contact us through Email Support@ditto";

            DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                            VALUES(?,0,"a",?,?,NOW())',[$memberId,$message,$subject]);

            //no error
            DB::commit();

            //notification
            self::sendWS($memberId);

            $response = ['status' => 1];
            return json_encode($response);
        }
        catch(\Exception $e)
        {
            DB::rollback();

            $response = ['status' => 0
                       ,'error' => __('error.memberdw.internal_error')
                        ];

            return json_encode($response);
        }
    }


    //LEFT PRODUCT ID 200
    public static function getWalletBalance(Request $request)
    {
        try
        {
            $prdId = $request->input('prd_id');
            $memberId = $request->input('member_id');
            $balance = 0;

            //MAIN WALLET
            if($prdId == 0)
            {
                $db = DB::select("
                    SELECT available
                    FROM member_credit
                    WHERE member_id = ?
                    ",[$memberId]
                );

                if(sizeof($db) != 0)
                {
                    $balance = $db[0]->available;
                }
            }
            else if($prdId == 1
            ||$prdId == 2
            ||$prdId == 3
            ||$prdId == 4
            ||$prdId == 6
            ||$prdId == 7
            ||$prdId == 8
            ||$prdId == 9
            ||$prdId == 10
            ||$prdId == 11
            ||$prdId == 12
            ||$prdId == 13
            ||$prdId == 14
            ||$prdId == 15
            ||$prdId == 16
            ||$prdId == 17)
            {
                //get member name
                $db = DB::select("
                    SELECT username
                    FROM member
                    WHERE id = ?
                    ",[$memberId]
                );

                if(sizeof($db) != 0)
                {
                    $memberName = $db[0]->username;
                }


                $balance = self::getGSBalance($prdId,$memberName);

            }


            return $balance;

        }
        catch(\Exception $e)
        {
            Log::debug($e);

            return 0;
        }
    }

    public static function getGSBalance($prdId,$username)
    {
        try 
        {

            $operatorCode = env('GS_OPERATOR_CODE');
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_API_URL');
            $password = env('GS_MEMBER_PASSWORD');
            $method = '/getBalance.aspx';
            $urlArray = [];
            $responseArr = [];
            $providerCode = self::mapProduct();

            $providerCode = $providerCode[$prdId];

            $md5 = md5($operatorCode.$password.$providerCode.$username.$secretKey);
                $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&providercode='.$providerCode.'&username='.$username.'&password='.$password.'&signature='.$signature;

            $urlArray[$prdId] = $url;


            foreach ($urlArray as $key => $url) 
            {
                $response = Helper::getData($url);
                $response = json_decode($response,true);

                if ($response['errCode'] != 0) 
                {
                    $responseArr[$key] = null; 
                }
                else
                {
                    $responseArr[$key] = $response['balance']; 
                }
            }


            return $responseArr;
        } 
        catch (Exception $e) 
        {
            log::debug($e);

            return 0;
        }

    }

    public static function mapProduct()
    {
        $product = array(
                    Providers::Gameplay  => env('GS_PROVIDER_GAMEPLAY_CODE')
                    ,Providers::BBIN  => env('GS_PROVIDER_BBIN_CODE')
                    ,Providers::IBC  => env('GS_PROVIDER_IBC_CODE')
                    ,Providers::ALLBET  => env('GS_PROVIDER_ALLBET_CODE')
                    ,Providers::CQ9  => env('GS_PROVIDER_CQ9_CODE')
                    ,Providers::WM  => env('GS_PROVIDER_WM_CODE')
                    ,Providers::Joker  => env('GS_PROVIDER_JOKER_CODE')
                    ,Providers::PSB4D  => env('GS_PROVIDER_PSB4D_CODE')
                    ,Providers::Spade  => env('GS_PROVIDER_SPADE_CODE')
                    ,Providers::QQKeno  => env('GS_PROVIDER_QQKENO_CODE')
                    ,Providers::CMD  => env('GS_PROVIDER_CMD_CODE')
                    ,Providers::M8BET  => env('GS_PROVIDER_M8BET_CODE')
                    ,Providers::DIGMAAN  => env('GS_PROVIDER_DIGMAAN_CODE')
                    ,Providers::EBET  => env('GS_PROVIDER_EBET_CODE')
                    ,Providers::IA  => env('GS_PROVIDER_IA_CODE')
                    ,Providers::NLIVE22  => env('GS_PROVIDER_NLIVE22_CODE')
                );

        return $product;
    }


    public static function sendWS($memberId)
    {
        try
        {
            //get recipient
            $db = DB::select("
                SELECT username
                FROM member
                WHERE id = ?"
                ,[$memberId]);

            $memberName = $db[0]->username;

            //get member pending request count
            $db = DB::select("
                SELECT COUNT(*) 'count'
                FROM member_dw
                WHERE member_id = ?
                    AND status = 'n'
                GROUP BY member_id"
                ,[$memberId]);

            if(sizeOf($db) > 0)
                $pendingCount = $db[0]->count;
            else
                $pendingCount = 0;

            //WS message
            event(new DWRequest($memberName,$pendingCount));

        }
        catch(\Exception $e)
        {
            
        }
    }

    public static function getOptionDuplicate()
    {
        //todo localization
        return  [
            ['0', __('option.member.duplicate.no')]
            ,['1', __('option.member.duplicate.yes')]
        ];        
    }

    public static function getOptionsType()
    {
        //todo localization
        return  [
            ['d', __('option.member.dw.deposit')]
            ,['w', __('option.member.dw.withdraw')]
        ];
    }

    public static function getOptionsPaymentGatewayStatus()
    {

         //todo localization
        return  [
            ['1', __('New order')]
            ,['2', __('Waiting for payment')]
            ,['3', __('Member has paid')]
            ,['4', __('Confirm')]
            ,['7', __('Failed')]
        ];       
    }

    public static function getOptionsPaymentType()
    {
        //todo localization
        return  [
            ['c', __('option.member.dw.cash')]
            ,['b', __('option.member.dw.bank')]
            ,['x', __('option.member.dw.crypto')]
            ,['f', __('option.member.dw.f2f')]
        ];
    }

    public static function getOptionsStatus()
    {
        //todo localization
        return  [
            ['n', __('option.member.dw.new')]
            ,['a', __('option.member.dw.approved')]
            ,['p', __('option.member.dw.processing')]
            ,['r', __('option.member.dw.rejected')]
            ,['c', __('option.member.dw.cancelled')]
        ];
    }

}

