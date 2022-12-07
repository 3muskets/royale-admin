<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProviderController;

use Auth;
use Log;

class MemberDetailsController extends Controller
{
    public static function getList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $username = $request->input('username');
            $tier4 = $request->input('tier4');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $adminId =  Auth::user()->admin_id;

            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d H:i:s',strtotime($startDate.'-4 hours'));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d H:i:s',strtotime($endDate.'23:59:59'.'-4 hours'));

            $sql = " SELECT a.username,a.id, d.username 'agent'
                            ,IFNULL(SUM(b.total_wager),0) 'total_wager'
                            ,IFNULL(SUM(b.turnover),0) 'turnover'
                            ,IFNULL(SUM(b.win_loss),0) 'win_loss' 
                            ,IFNULL(SUM(b.tier2_pt_amt),0) 'tier2_pt_amt'
                            ,IFNULL(SUM(b.tier4_comm_amt),0) 'tier4_comm_amt'
                            ,IFNULL(e.deposit,0) 'total_deposit'
                            ,IFNULL(e.withdraw,0) 'total_withdraw'
                    FROM member a 
                    LEFT JOIN 
                        ( 
                            SELECT a.member_id
                                ,COUNT(a.amount) 'total_wager'
                                ,SUM(a.amount) 'turnover'
                                ,SUM(b.amount - a.amount) 'win_loss' 
                                ,SUM(b.tier2_pt_amt) 'tier2_pt_amt'
                                ,SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM aas_debit a
                            INNER JOIN aas_credit b
                            ON a.txn_id = b.txn_id 
                            AND a.prd_id = b.prd_id
                            WHERE (a.created_at >= :start_date OR '' = :start_date1)
                                AND (a.created_at <= :end_date OR '' = :end_date1)
                            GROUP by a.member_id
                            
                            UNION ALL 

                            SELECT a.member_id
                                ,COUNT(a.amount) 'total_wager'
                                ,SUM(a.amount) 'turnover'
                                ,SUM(b.amount - a.amount) 'win_loss' 
                                ,SUM(b.tier2_pt_amt) 'tier2_pt_amt'
                                ,SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM haba_debit a
                            INNER JOIN haba_credit b
                            ON a.txn_id = b.txn_id
                            WHERE (a.created_at >= :start_date2 OR '' = :start_date3)
                                AND (a.created_at <= :end_date2 OR '' = :end_date3)
                            GROUP by a.member_id
                            
                            UNION ALL   
                            
                            SELECT a.member_id
                                ,COUNT(a.amount) 'total_wager'
                                ,SUM(a.amount) 'turnover'
                                ,SUM(b.amount - a.amount) 'win_loss' 
                                ,SUM(b.tier2_pt_amt) 'tier2_pt_amt'
                                ,SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM pp_debit a
                            INNER JOIN pp_credit b
                            ON a.txn_id = b.txn_id
                            WHERE (a.created_at >= :start_date4 OR '' = :start_date5)
                                AND (a.created_at <= :end_date4 OR '' = :end_date5)
                            GROUP by a.member_id

                            UNION ALL   
                            
                            SELECT a.member_id
                                ,COUNT(a.amount) 'total_wager'
                                ,SUM(a.amount) 'turnover'
                                ,SUM(b.amount - a.amount) 'win_loss' 
                                ,SUM(b.tier2_pt_amt) 'tier2_pt_amt'
                                ,SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM wm_debit a
                            INNER JOIN wm_credit b
                            ON a.txn_id = b.txn_id
                            WHERE (a.created_at >= :start_date6 OR '' = :start_date7)
                                AND (a.created_at <= :end_date6 OR '' = :end_date7)
                            GROUP by a.member_id
                        ) b ON a.id = b.member_id
                    LEFT JOIN tiers c ON a.admin_id = c.admin_id
                    LEFT JOIN admin d ON a.admin_id = d.id
                    LEFT JOIN
                    (
                        SELECT member_id
                            , SUM(CASE WHEN (type = 1 OR type = 2) THEN amount ELSE 0 END) 'deposit'
                            , SUM(CASE WHEN type = 3 THEN amount ELSE 0 END) 'withdraw'
                        FROM member_credit_txn
                        WHERE (created_at >= :start_date8 OR '' = :start_date9)
                            AND (created_at <= :end_date8 OR '' = :end_date9)
                        GROUP BY member_id
                    ) e ON a.id = e.member_id
                    WHERE c.up2_tier = :id
                        AND a.username LIKE :username
                        AND d.username LIKE :tier4
                    GROUP BY a.username,a.id
                    "
                ;

            $params = 
            [ 
                'start_date' => $startDate
                ,'start_date1' => $startDate
                ,'end_date' => $endDate
                ,'end_date1' => $endDate  
                ,'start_date2' => $startDate
                ,'start_date3' => $startDate
                ,'end_date2' => $endDate
                ,'end_date3' => $endDate  
                ,'start_date4' => $startDate
                ,'start_date5' => $startDate
                ,'end_date4' => $endDate
                ,'end_date5' => $endDate  
                ,'start_date6' => $startDate
                ,'start_date7' => $startDate
                ,'end_date6' => $endDate
                ,'end_date7' => $endDate
                ,'start_date8' => $startDate
                ,'start_date9' => $startDate
                ,'end_date8' => $endDate
                ,'end_date9' => $endDate  
                ,'id' => $adminId   
                ,'username' => '%' . $username . '%'
                ,'tier4' => '%' . $tier4 . '%'     
            ];

            $orderByAllow = ['username','total_wager','turnover','win_loss','tier2_pt_amt','tier4_comm_amt','total_deposit','total_withdraw'];
            $orderByDefault = 'total_wager desc, turnover desc, total_deposit desc, total_withdraw desc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page);

            $dataTotal = [];

            if($data['count'] > $data['page_size'])
            {
                $sql = " SELECT IFNULL(SUM(b.total_wager),0) 'total_wager'
                            ,IFNULL(SUM(b.turnover),0) 'turnover'
                            ,IFNULL(SUM(b.win_loss),0) 'win_loss' 
                            ,IFNULL(SUM(b.tier2_pt_amt),0) 'tier2_pt_amt'
                            ,IFNULL(SUM(b.tier4_comm_amt),0) 'tier4_comm_amt'
                            ,IFNULL(e.deposit,0) 'total_deposit'
                            ,IFNULL(e.withdraw,0) 'total_withdraw'
                    FROM member a 
                    LEFT JOIN 
                        ( 
                            SELECT a.member_id
                                ,COUNT(a.amount) 'total_wager'
                                ,SUM(a.amount) 'turnover'
                                ,SUM(b.amount - a.amount) 'win_loss' 
                                ,SUM(b.tier2_pt_amt) 'tier2_pt_amt'
                                ,SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM aas_debit a
                            INNER JOIN aas_credit b
                            ON a.txn_id = b.txn_id
                            AND a.prd_id = b.prd_id
                            WHERE (a.created_at >= :start_date OR '' = :start_date1)
                                AND (a.created_at <= :end_date OR '' = :end_date1)
                            GROUP by a.member_id
                            
                            UNION ALL 

                            SELECT a.member_id
                                ,COUNT(a.amount) 'total_wager'
                                ,SUM(a.amount) 'turnover'
                                ,SUM(b.amount - a.amount) 'win_loss' 
                                ,SUM(b.tier2_pt_amt) 'tier2_pt_amt'
                                ,SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM haba_debit a
                            INNER JOIN haba_credit b
                            ON a.txn_id = b.txn_id
                            WHERE (a.created_at >= :start_date2 OR '' = :start_date3)
                                AND (a.created_at <= :end_date2 OR '' = :end_date3)
                            GROUP by a.member_id
                            
                            UNION ALL   
                            
                            SELECT a.member_id
                                ,COUNT(a.amount) 'total_wager'
                                ,SUM(a.amount) 'turnover'
                                ,SUM(b.amount - a.amount) 'win_loss' 
                                ,SUM(b.tier2_pt_amt) 'tier2_pt_amt'
                                ,SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM pp_debit a
                            INNER JOIN pp_credit b
                            ON a.txn_id = b.txn_id
                            WHERE (a.created_at >= :start_date4 OR '' = :start_date5)
                                AND (a.created_at <= :end_date4 OR '' = :end_date5)
                            GROUP by a.member_id

                            UNION ALL   
                            
                            SELECT a.member_id
                                ,COUNT(a.amount) 'total_wager'
                                ,SUM(a.amount) 'turnover'
                                ,SUM(b.amount - a.amount) 'win_loss' 
                                ,SUM(b.tier2_pt_amt) 'tier2_pt_amt'
                                ,SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM wm_debit a
                            INNER JOIN wm_credit b
                            ON a.txn_id = b.txn_id
                            WHERE (a.created_at >= :start_date6 OR '' = :start_date7)
                                AND (a.created_at <= :end_date6 OR '' = :end_date7)
                            GROUP by a.member_id
                        ) b ON a.id = b.member_id
                    LEFT JOIN tiers c ON a.admin_id = c.admin_id
                    LEFT JOIN admin d ON a.admin_id = d.id
                    LEFT JOIN
                    (
                        SELECT c.up2_tier
                            ,SUM(CASE WHEN (a.type = 1 OR a.type = 2) THEN a.amount ELSE 0 END) 'deposit'
                            ,SUM(CASE WHEN a.type = 3 THEN a.amount ELSE 0 END) 'withdraw'
                        FROM member_credit_txn a
                        LEFT JOIN member b
                            ON a.member_id = b.id
                        LEFT JOIN tiers c
                            ON c.admin_id = b.admin_id
                        WHERE (a.created_at >= :start_date8 OR '' = :start_date9)
                            AND (a.created_at <= :end_date8 OR '' = :end_date9)
                        GROUP BY c.up2_tier
                    ) e ON c.up2_tier = e.up2_tier
                    WHERE c.up2_tier = :id
                        AND a.username LIKE :username
                        AND d.username LIKE :tier4
                        "
                    ;

                $params = 
                [ 
                    'start_date' => $startDate
                    ,'start_date1' => $startDate
                    ,'end_date' => $endDate
                    ,'end_date1' => $endDate  
                    ,'start_date2' => $startDate
                    ,'start_date3' => $startDate
                    ,'end_date2' => $endDate
                    ,'end_date3' => $endDate  
                    ,'start_date4' => $startDate
                    ,'start_date5' => $startDate
                    ,'end_date4' => $endDate
                    ,'end_date5' => $endDate  
                    ,'start_date6' => $startDate
                    ,'start_date7' => $startDate
                    ,'end_date6' => $endDate
                    ,'end_date7' => $endDate
                    ,'start_date8' => $startDate
                    ,'start_date9' => $startDate
                    ,'end_date8' => $endDate
                    ,'end_date9' => $endDate  
                    ,'id' => $adminId
                    ,'username' => '%' . $username . '%' 
                    ,'tier4' => '%' . $tier4 . '%'             
                ];

                $dataTotal = DB::select($sql,$params);
            }

            return Response::make(json_encode([$data,$dataTotal]), 200);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
    }


    public static function getLevelSettingList(Request $request)
    {

        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $user = Auth::user();
            $id = $user->admin_id;

            $sql = "
                    SELECT id,level,min_deposit_amt,updated_at
                    FROM member_lvl
                    ";

            $params = 
            [

            ];

            $orderByAllow = ['id','level','min_deposit_amt','updated_at'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page, 500);


            foreach($data['results'] as $d)
            {

            }

            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }

    public static function getReferList(Request $request)
    {

        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            $username = $request->input('username');

            $user = Auth::user();
            $userLevel = $user->level;
            $id = $user->admin_id;

            if($userLevel == 0)
            {
                $id = '';
            }


            $sql = "
                    SELECT a.id,a.username,a.created_at,count(b.id) 'num_downline',c.amount 'referral_bonus'
                    FROM
                    member a
                    LEFT JOIN member b
                    ON a.id = b.referral_id
                    LEFT JOIN
                    (SELECT member_id,SUM(amount) 'amount'
                     FROM member_referral_txn
                     WHERE is_reject != 1
                     GROUP BY member_id
                    ) c ON a.id = c.member_id
                    LEFT JOIN admin d
                        ON a.admin_id = d.id
                    LEFT JOIN tiers e
                        ON a.admin_id = e.admin_id
                    WHERE a.username LIKE :username
                    AND (a.admin_id = :id1 OR e.up1_tier = :id2 OR e.up2_tier = :id3 OR :id4 = '')
                    GROUP BY a.id,a.username,a.created_at

                    ";

            $params = 
            [
                'username' => '%' . $username . '%'
                ,'id1' => $id
                ,'id2' => $id
                ,'id3' => $id
                ,'id4' => $id
            ];

            $orderByAllow = ['id','username','created_at','referral_bonus','num_downline'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page, 500);

            $num = 1;

            foreach($data['results'] as $d)
            {
                $d->no = $num;

                $num++;

                $d->downline = '';

                //get downline
                $db = DB::select("
                        SELECT id,username
                        FROM member
                        WHERE referral_id = ?
                        ",[$d->id]);

                if(sizeof($db) != 0)
                {
                    foreach($db as $d2)
                    {
                        if($d->downline != '')
                            $d->downline .= '<br>';

                        $d->downline .= $d2->username.' ('.$d2->id . ')';

                    } 
                }
                 
            }


            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }

    }


    public static function updateLevelSetting(Request $request)
    {
        DB::beginTransaction();

        try
        {
            $level = $request->input('level');
            $id = $request->input('id');
            $minAmt = $request->input('minAmount');    
            $minAmt = str_replace( ',', '', $minAmt);
            $upperLevelAmt = ''; 
            $lowerLevelAmt = '';

            $errMsg = [];


            $db = DB::select("
                    SELECT min_deposit_amt,id
                    FROM member_lvl
                    WHERE id = ? OR id = ?
                     ",[$id+1,$id-1]
                );

            foreach($db as $d)
            {
                if($d->id == $id+1)
                {
                    $upperLevelAmt = $d->min_deposit_amt;
                }
                else if($d->id == $id-1)
                {
                    $lowerLevelAmt = $d->min_deposit_amt;
                }
                
            }


            if (!Helper::checkInputFormat('numeric',$minAmt)) 
            {

                $response = ['status' => 0
                            ,'error' => __('Amount must in numeric or greater than zero')
                            ];

                return json_encode($response);

            }

            if(!Helper::validAmount($minAmt))
            {

                $response = ['status' => 0
                            ,'error' => __('Amount cannot exceed 15 digits')
                            ];

                return json_encode($response);

            }


            if(($minAmt >= $upperLevelAmt) && $id != 6)
            {
                $response = ['status' => 0
                            ,'error' => __('Your Amount is Larger Than or Equal Upper Level Min Deposit Amount')
                            ];

                return json_encode($response);
            }


            if($minAmt <= $lowerLevelAmt)
            {
                $response = ['status' => 0
                            ,'error' => __('Your Amount is Lower or Equal Your Lower Level Min Deposit Amount')
                            ];

                return json_encode($response);
            }



            DB::update("
                        UPDATE member_lvl
                        SET min_deposit_amt = ?
                        WHERE id= ? 
                        "
                        ,[
                            $minAmt
                            ,$id
                        ]);

                
            $response = ['status' => 1];

            Helper::log($request,'Update MemberLevel Deposit Min Amount');
              
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

}
