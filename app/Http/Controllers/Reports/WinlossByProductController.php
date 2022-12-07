<?php

namespace App\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\DownlineController;

use Auth;
use Log;

class WinlossByProductController extends Controller
{

    public static function getList(Request $request)
    {
        try
        {

            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            
            $prdId = $request->input('prd_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $timeZone = $request->input('timezone',8);
            $timeZone *= 0;

            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = ['NULL'];

            if($userLevel == '')
            {
                $db = DB::select("
                    SELECT admin_id
                    FROM tiers
                    ");

                array_push($adminArr,0);
                array_push($adminArr,1);

                if(sizeof($db) != 0)
                {
                    foreach($db as $d)
                    {
                        array_push($adminArr,$d->admin_id);
                    }
                    
                }
            }
            else
            {
                if($userLevel == 1)
                {
                    $db = DB::select("
                        SELECT admin_id
                        FROM tiers
                        WHERE up1_tier = ?
                        OR up2_tier = ?
                        OR admin_id = ?
                        ",[$user->admin_id,$user->admin_id,$user->admin_id]);                                            
                }
                else if($userLevel == 2)
                {
                    $db = DB::select("
                        SELECT admin_id
                        FROM tiers
                        WHERE up1_tier = ?
                        OR admin_id = ?
                        ",[$user->admin_id,$user->admin_id]);                      
                }
                else if($userLevel == 3)
                {
                    $db = DB::select("
                        SELECT admin_id
                        FROM tiers
                        WHERE admin_id = ?
                        ",[$user->admin_id]);                     
                }


                if(sizeof($db) != 0)
                {
                    foreach($db as $d)
                    {
                        array_push($adminArr,$d->admin_id);
                    }
                    
                }

            }

            if($prdId == null)
                $prdId = '';

            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));


            $sql = "
                SELECT  a.prd_id,
                        COUNT(a.amount) 'total_wager',
                        SUM(a.amount) 'turnover',
                        SUM(b.amount - a.amount) 'win_loss', 
                        SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                        SUM(b.tier2_pt_amt)'tier2_pt_amt',
                        SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                        SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                        SUM(b.tier4_comm_amt) as tier4_comm_amt
                FROM aas_debit a
                INNER JOIN aas_credit b 
                    ON a.txn_id = b.txn_id
                    AND a.prd_id = b.prd_id
                INNER JOIN member c 
                    ON a.member_id = c.id
                LEFT JOIN tiers d 
                    ON c.admin_id = d.admin_id
                INNER JOIN aas_games e 
                    ON a.game_id = e.id
                WHERE c.admin_id IN (?)
                    AND (a.prd_id = :prdId OR  '' =  :prdId1)
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date OR '' = :start_date1)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date OR '' = :end_date1)
                GROUP BY a.prd_id

                UNION ALL

                SELECT  a.prd_id,
                        COUNT(a.bet) 'total_wager',
                        SUM(a.bet) 'turnover',
                        SUM(b.amount - a.bet) 'win_loss', 
                        SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                        SUM(b.tier2_pt_amt)'tier2_pt_amt',
                        SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                        SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                        SUM(b.tier4_comm_amt) as tier4_comm_amt
                FROM gs_debit a
                INNER JOIN gs_credit b 
                    ON a.txn_id = b.txn_id
                    AND a.prd_id = b.prd_id
                INNER JOIN member c 
                    ON a.member_id = c.id
                LEFT JOIN tiers d 
                    ON c.admin_id = d.admin_id
                WHERE c.admin_id IN (?)
                    AND (a.prd_id = :prdId2 OR  '' =  :prdId3)
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date2 OR '' = :start_date3)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date2 OR '' = :end_date3)
                GROUP BY a.prd_id

                UNION ALL

                SELECT a.*
                FROM
                (
                SELECT  '5' prd_id,
                        COUNT(a.txn_id) 'total_wager',
                        SUM(a.amount) 'turnover',
                        SUM(b.amount - a.amount) 'win_loss', 
                        SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                        SUM(b.tier2_pt_amt)'tier2_pt_amt',
                        SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                        SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                        SUM(b.tier4_comm_amt) as tier4_comm_amt
                FROM fg_debit a
                INNER JOIN fg_credit b 
                    ON a.txn_id = b.txn_id
                INNER JOIN member c 
                    ON a.member_id = c.id
                LEFT JOIN tiers d 
                    ON c.admin_id = d.admin_id
                WHERE c.admin_id IN (?)
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date4 OR '' = :start_date5)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date4 OR '' = :end_date5)
                GROUP BY prd_id
                ) a
                WHERE (a.prd_id = :prdId4 OR  '' =  :prdId5)

                UNION ALL

                SELECT  a.prd_id,
                        COUNT(a.bet) 'total_wager',
                        SUM(a.bet) 'turnover',
                        SUM(b.amount - a.bet) 'win_loss', 
                        SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                        SUM(b.tier2_pt_amt)'tier2_pt_amt',
                        SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                        SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                        SUM(b.tier4_comm_amt) as tier4_comm_amt
                FROM cp_debit a
                INNER JOIN cp_credit b 
                    ON a.txn_id = b.txn_id
                    AND a.prd_id = b.prd_id
                INNER JOIN member c 
                    ON a.member_id = c.id
                LEFT JOIN tiers d 
                    ON c.admin_id = d.admin_id
                WHERE c.admin_id IN (?)
                    AND (a.prd_id = :prdId6 OR  '' =  :prdId7)
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date6 OR '' = :start_date7)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date6 OR '' = :end_date7)
                GROUP BY a.prd_id
        
                UNION ALL

                SELECT  a.prd_id,
                        COUNT(a.bet) 'total_wager',
                        SUM(a.bet) 'turnover',
                        SUM(b.amount - a.bet) 'win_loss', 
                        SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                        SUM(b.tier2_pt_amt)'tier2_pt_amt',
                        SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                        SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                        SUM(b.tier4_comm_amt) as tier4_comm_amt
                FROM noe_debit a
                INNER JOIN noe_credit b 
                    ON a.txn_id = b.txn_id
                    AND a.prd_id = b.prd_id
                INNER JOIN member c 
                    ON a.member_id = c.id
                LEFT JOIN tiers d 
                    ON c.admin_id = d.admin_id
                WHERE c.admin_id IN (?)
                    AND (a.prd_id = :prdId8 OR  '' =  :prdId9)
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date8 OR '' = :start_date9)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date8 OR '' = :end_date9)
                GROUP BY a.prd_id

                UNION ALL

                SELECT  a.prd_id,
                        COUNT(a.bet) 'total_wager',
                        SUM(a.bet) 'turnover',
                        SUM(b.amount - a.bet) 'win_loss', 
                        SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                        SUM(b.tier2_pt_amt)'tier2_pt_amt',
                        SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                        SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                        SUM(b.tier4_comm_amt) as tier4_comm_amt
                FROM pussy_debit a
                INNER JOIN pussy_credit b 
                    ON a.txn_id = b.txn_id
                    AND a.prd_id = b.prd_id
                INNER JOIN member c 
                    ON a.member_id = c.id
                LEFT JOIN tiers d 
                    ON c.admin_id = d.admin_id
                WHERE c.admin_id IN (?)
                    AND (a.prd_id = :prdId10 OR  '' =  :prdId11)
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date10 OR '' = :start_date11)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date10 OR '' = :end_date11)
                GROUP BY a.prd_id
            ";

            $params = [$adminArr,$adminArr,$adminArr,$adminArr,$adminArr,$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;
            $params['prdId'] = $prdId;
            $params['prdId1'] = $prdId;


            $params['start_date2'] = $startDate;
            $params['start_date3'] = $startDate;
            $params['end_date2'] = $endDate;
            $params['end_date3'] = $endDate;
            $params['prdId2'] = $prdId;
            $params['prdId3'] = $prdId;

           
            $params['start_date4'] = $startDate;
            $params['start_date5'] = $startDate;
            $params['end_date4'] = $endDate;
            $params['end_date5'] = $endDate;
            $params['prdId4'] = $prdId;
            $params['prdId5'] = $prdId;

            $params['start_date6'] = $startDate;
            $params['start_date7'] = $startDate;
            $params['end_date6'] = $endDate;
            $params['end_date7'] = $endDate;
            $params['prdId6'] = $prdId;
            $params['prdId7'] = $prdId;

            $params['start_date8'] = $startDate;
            $params['start_date9'] = $startDate;
            $params['end_date8'] = $endDate;
            $params['end_date9'] = $endDate;
            $params['prdId8'] = $prdId;
            $params['prdId9'] = $prdId;

            $params['start_date10'] = $startDate;
            $params['start_date11'] = $startDate;
            $params['end_date10'] = $endDate;
            $params['end_date11'] = $endDate;
            $params['prdId10'] = $prdId;
            $params['prdId11'] = $prdId;


            $orderByAllow = ["prd_id","win_loss","total_wager","turnover","tier1_pt_amt","tier2_pt_amt","tier3_pt_amt","tier4_pt_amt","tier4_comm_amt"];
            $orderByDefault = 'prd_id,total_wager desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
           
            $data = Helper::paginateData($sql,$params,$page);

            $aryProduct = self::getOptionsProduct();

            foreach($data['results'] as $d)
            {
                $d->prd_name = Helper::getOptionsValue($aryProduct, $d->prd_id);

                if($userLevel == 0)
                {
                    $d->tier4_pt_amt = '';
                    $d->tier3_pt_amt = '';
                    $d->tier2_pt_amt = '';
                }
                else if($userLevel == 1)
                {   
                    $d->tier3_pt_amt = '';
                    $d->tier1_pt_amt = '';
                    $d->tier4_pt_amt = '';
                }
                else if($userLevel == 2)
                {   
                    $d->tier2_pt_amt = '';
                    $d->tier1_pt_amt = '';
                    $d->tier4_pt_amt = '';
                }
                else if($userLevel == 3)
                {   
                    $d->tier2_pt_amt = '';
                    $d->tier1_pt_amt = '';
                    $d->tier3_pt_amt = '';
                }
            }
            
            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return [];
        }
    }

    public static function getDetails(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $gameId = $request->input('game_id');
            $prdId = $request->input('prd_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $timeZone = $request->input('timezone',8);
            $timeZone *= 0;

            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = ['NULL'];

            if($userLevel == '')
            {
                $db = DB::select("
                    SELECT admin_id
                    FROM tiers
                    ");

                array_push($adminArr,0);
                array_push($adminArr,1);

                if(sizeof($db) != 0)
                {
                    foreach($db as $d)
                    {
                        array_push($adminArr,$d->admin_id);
                    }
                    
                }
            }
            else
            {
                if($userLevel == 1)
                {
                    $db = DB::select("
                        SELECT admin_id
                        FROM tiers
                        WHERE up1_tier = ?
                        OR up2_tier = ?
                        OR admin_id = ?
                        ",[$user->admin_id,$user->admin_id,$user->admin_id]);                                            
                }
                else if($userLevel == 2)
                {
                    $db = DB::select("
                        SELECT admin_id
                        FROM tiers
                        WHERE up1_tier = ?
                        OR admin_id = ?
                        ",[$user->admin_id,$user->admin_id]);                      
                }
                else if($userLevel == 3)
                {
                    $db = DB::select("
                        SELECT admin_id
                        FROM tiers
                        WHERE admin_id = ?
                        ",[$user->admin_id]);                     
                }


                if(sizeof($db) != 0)
                {
                    foreach($db as $d)
                    {
                        array_push($adminArr,$d->admin_id);
                    }
                    
                }

            }


            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));



            $sql = "SELECT a.prd_id,a.member_id,a.txn_id,c.username,a.amount 'stake',(b.amount - a.amount) 'win_loss',
                           (a.created_at + INTERVAL 8 HOUR) 'debit_date', (b.created_at + INTERVAL 8 HOUR) 'credit_date',
                           b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,
                           b.tier1_pt_amt,b.tier2_pt_amt,b.tier3_pt_amt,b.tier4_pt_amt,b.tier4_comm,b.tier4_comm_amt,
                           a.game_id,e.name'game_name',
                        CASE
                            WHEN (b.amount - a.amount) > 0  THEN 'w'
                            WHEN (b.amount - a.amount) < 0  THEN 'l'
                            WHEN (b.amount - a.amount) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                    FROM aas_debit a
                    INNER JOIN aas_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c 
                        ON a.member_id = c.id
                    LEFT JOIN admin d 
                        ON c.admin_id = d.admin_id
                    LEFT JOIN aas_games e
                        ON a.game_id = e.id
                    WHERE  c.admin_id IN (?)
                        AND a.prd_id = :prdId
                        AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date OR '' = :start_date1)
                        AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date OR '' = :end_date1)
                        AND b.type != 'x'

                    UNION ALL

                    SELECT a.*
                    FROM 
                    (
                        SELECT 5 as prd_id,a.member_id,a.txn_id,c.username,a.amount 'stake',(b.amount - a.amount) 'win_loss',
                               (a.created_at + INTERVAL 8 HOUR) 'debit_date', (b.created_at + INTERVAL 8 HOUR) 'credit_date',
                               b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,
                               b.tier1_pt_amt,b.tier2_pt_amt,b.tier3_pt_amt,b.tier4_pt_amt,b.tier4_comm,b.tier4_comm_amt,
                               a.game_id,'' game_name,
                            CASE
                                WHEN (b.amount - a.amount) > 0  THEN 'w'
                                WHEN (b.amount - a.amount) < 0  THEN 'l'
                                WHEN (b.amount - a.amount) = 0  THEN 't'
                                ELSE 0
                                END AS bet_status
                        FROM fg_debit a
                        INNER JOIN fg_credit b
                            ON a.txn_id = b.txn_id
                        INNER JOIN member c 
                            ON a.member_id = c.id
                        LEFT JOIN admin d 
                            ON c.admin_id = d.admin_id
                        WHERE  c.admin_id IN (?)
                            AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date2 OR '' = :start_date3)
                            AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date2 OR '' = :end_date3)
                            AND b.type != 'x'
                    ) as a
                    WHERE a.prd_id = :prdId1

                    UNION ALL

                    SELECT a.prd_id,a.member_id,a.txn_id,c.username,a.bet 'stake',(b.amount - a.bet) 'win_loss',
                           (a.created_at + INTERVAL 8 HOUR) 'debit_date', (b.created_at + INTERVAL 8 HOUR) 'credit_date',
                           b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,
                           b.tier1_pt_amt,b.tier2_pt_amt,b.tier3_pt_amt,b.tier4_pt_amt,b.tier4_comm,b.tier4_comm_amt,
                           a.game_id,'' game_name,
                        CASE
                            WHEN (b.amount - a.bet) > 0  THEN 'w'
                            WHEN (b.amount - a.bet) < 0  THEN 'l'
                            WHEN (b.amount - a.bet) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                    FROM gs_debit a
                    INNER JOIN gs_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c 
                        ON a.member_id = c.id
                    LEFT JOIN admin d 
                        ON c.admin_id = d.admin_id
                    WHERE  c.admin_id IN (?)
                        AND a.prd_id = :prdId2
                        AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date4 OR '' = :start_date5)
                        AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date4 OR '' = :end_date5)
                        AND b.type != 'x'

                    UNION ALL

                    SELECT a.prd_id,a.member_id,a.txn_id,c.username,a.bet 'stake',(b.amount - a.bet) 'win_loss',
                           (a.created_at + INTERVAL 8 HOUR) 'debit_date', (b.created_at + INTERVAL 8 HOUR) 'credit_date',
                           b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,
                           b.tier1_pt_amt,b.tier2_pt_amt,b.tier3_pt_amt,b.tier4_pt_amt,b.tier4_comm,b.tier4_comm_amt,
                           '' game_id,'' game_name,
                        CASE
                            WHEN (b.amount - a.bet) > 0  THEN 'w'
                            WHEN (b.amount - a.bet) < 0  THEN 'l'
                            WHEN (b.amount - a.bet) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                    FROM cp_debit a
                    INNER JOIN cp_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c 
                        ON a.member_id = c.id
                    LEFT JOIN admin d 
                        ON c.admin_id = d.admin_id
                    WHERE  c.admin_id IN (?)
                        AND a.prd_id = :prdId3
                        AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date6 OR '' = :start_date7)
                        AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date6 OR '' = :end_date7)
                        AND b.type != 'x'

                    UNION ALL

                    SELECT a.prd_id,a.member_id,a.txn_id,c.username,a.bet 'stake',(b.amount - a.bet) 'win_loss',
                           (a.created_at + INTERVAL 8 HOUR) 'debit_date', (b.created_at + INTERVAL 8 HOUR) 'credit_date',
                           b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,
                           b.tier1_pt_amt,b.tier2_pt_amt,b.tier3_pt_amt,b.tier4_pt_amt,b.tier4_comm,b.tier4_comm_amt,
                           a.game_id,'' game_name,
                        CASE
                            WHEN (b.amount - a.bet) > 0  THEN 'w'
                            WHEN (b.amount - a.bet) < 0  THEN 'l'
                            WHEN (b.amount - a.bet) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                    FROM noe_debit a
                    INNER JOIN noe_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c 
                        ON a.member_id = c.id
                    LEFT JOIN admin d 
                        ON c.admin_id = d.admin_id
                    WHERE  c.admin_id IN (?)
                        AND a.prd_id = :prdId4
                        AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date8 OR '' = :start_date9)
                        AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date8 OR '' = :end_date9)
                        AND b.type != 'x'


                    UNION ALL

                    SELECT a.prd_id,a.member_id,a.txn_id,c.username,a.bet 'stake',(b.amount - a.bet) 'win_loss',
                           (a.created_at + INTERVAL 8 HOUR) 'debit_date', (b.created_at + INTERVAL 8 HOUR) 'credit_date',
                           b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,
                           b.tier1_pt_amt,b.tier2_pt_amt,b.tier3_pt_amt,b.tier4_pt_amt,b.tier4_comm,b.tier4_comm_amt,
                           a.game_id,'' game_name,
                        CASE
                            WHEN (b.amount - a.bet) > 0  THEN 'w'
                            WHEN (b.amount - a.bet) < 0  THEN 'l'
                            WHEN (b.amount - a.bet) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                    FROM pussy_debit a
                    INNER JOIN pussy_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c 
                        ON a.member_id = c.id
                    LEFT JOIN admin d 
                        ON c.admin_id = d.admin_id
                    WHERE  c.admin_id IN (?)
                        AND a.prd_id = :prdId5
                        AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date10 OR '' = :start_date11)
                        AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date10 OR '' = :end_date11)
                        AND b.type != 'x'

                    ";

            $params = [$adminArr,$adminArr,$adminArr,$adminArr,$adminArr,$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;
            $params['prdId'] = $prdId;


            $params['start_date2'] = $startDate;
            $params['start_date3'] = $startDate;
            $params['end_date2'] = $endDate;
            $params['end_date3'] = $endDate;
            $params['prdId1'] = $prdId;

           
            $params['start_date4'] = $startDate;
            $params['start_date5'] = $startDate;
            $params['end_date4'] = $endDate;
            $params['end_date5'] = $endDate;
            $params['prdId2'] = $prdId;


            $params['start_date6'] = $startDate;
            $params['start_date7'] = $startDate;
            $params['end_date6'] = $endDate;
            $params['end_date7'] = $endDate;
            $params['prdId3'] = $prdId;


            $params['start_date8'] = $startDate;
            $params['start_date9'] = $startDate;
            $params['end_date8'] = $endDate;
            $params['end_date9'] = $endDate;
            $params['prdId4'] = $prdId;


            $params['start_date10'] = $startDate;
            $params['start_date11'] = $startDate;
            $params['end_date10'] = $endDate;
            $params['end_date11'] = $endDate;
            $params['prdId5'] = $prdId;

            $orderByAllow = ['username','stake','win_loss','tier1_pt_amt','tier2_pt_amt','tier3_pt_amt','tier4_pt_amt','bet_status','debit_date','credit_date'];
            $orderByDefault = 'debit_date desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
           
            $data = Helper::paginateData($sql,$params,$page);

            $aryBetStatus= self::getOptionsBetStatus();
            
            foreach($data['results'] as $d)
            {

                $d->bet_status_desc = Helper::getOptionsValue($aryBetStatus, $d->bet_status);
                
                if($d->game_id == 2 && $prdId == 5)
                    $d->game_name = 'Monkey King';

                if($userLevel == 0)
                {
                    $d->tier4_pt_amt = '';
                    $d->tier3_pt_amt = '';
                    $d->tier2_pt_amt = '';
                }
                else if($userLevel == 1)
                {   
                    $d->tier3_pt_amt = '';
                    $d->tier1_pt_amt = '';
                    $d->tier4_pt_amt = '';
                }
                else if($userLevel == 2)
                {   
                    $d->tier2_pt_amt = '';
                    $d->tier1_pt_amt = '';
                    $d->tier4_pt_amt = '';
                }
                else if($userLevel == 3)
                {   
                    $d->tier2_pt_amt = '';
                    $d->tier1_pt_amt = '';
                    $d->tier3_pt_amt = '';
                }

            }

            $dataTotal = [];

            //only need query to total again if multiple page
            if($data['count'] > $data['page_size'])
            {
                $sql = "
                    SELECT sum(a.amount) 'stake',sum((b.amount - a.amount)) 'win_loss',
                       sum(b.tier1_pt_amt) 'tier1_pt_amt',
                       sum(b.tier2_pt_amt) 'tier2_pt_amt',
                       sum(b.tier3_pt_amt) 'tier3_pt_amt',
                       sum(b.tier4_pt_amt) 'tier4_pt_amt',
                       sum(b.tier4_comm_amt) 'tier4_comm_amt'
                    FROM aas_debit a
                    INNER JOIN aas_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c ON a.member_id = c.id
                    LEFT JOIN admin d ON c.admin_id = d.admin_id
                    WHERE c.admin_id IN (?) 
                    AND a.prd_id = :prdId
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date OR '' = :start_date1)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date OR '' = :end_date1)
                    AND b.type != 'x'

                    UNION ALL
                    
                    SELECT a.stake,a.win_loss,a.tier1_pt_amt,a.tier2_pt_amt,a.tier3_pt_amt
                    ,a.tier4_pt_amt,a.tier4_comm_amt
                    FROM
                    (
                       SELECT 5 AS prd_id,
                       sum(a.amount) 'stake',sum((b.amount - a.amount)) 'win_loss',
                       sum(b.tier1_pt_amt) 'tier1_pt_amt',
                       sum(b.tier2_pt_amt) 'tier2_pt_amt',
                       sum(b.tier3_pt_amt) 'tier3_pt_amt',
                       sum(b.tier4_pt_amt) 'tier4_pt_amt',
                       sum(b.tier4_comm_amt) 'tier4_comm_amt'
                        FROM aas_debit a
                        INNER JOIN aas_credit b
                            ON a.txn_id = b.txn_id
                            AND a.prd_id = b.prd_id
                        INNER JOIN member c ON a.member_id = c.id
                        LEFT JOIN admin d ON c.admin_id = d.admin_id
                        WHERE c.admin_id IN (?) 
                        AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date2 OR '' = :start_date3)
                        AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date2 OR '' = :end_date3)
                        AND b.type != 'x'
                    ) AS a
                    WHERE a.prd_id = :prdId1

                    UNION ALL

                    SELECT sum(a.bet) 'stake',sum((b.amount - a.bet)) 'win_loss',
                       sum(b.tier1_pt_amt) 'tier1_pt_amt',
                       sum(b.tier2_pt_amt) 'tier2_pt_amt',
                       sum(b.tier3_pt_amt) 'tier3_pt_amt',
                       sum(b.tier4_pt_amt) 'tier4_pt_amt',
                       sum(b.tier4_comm_amt) 'tier4_comm_amt'
                    FROM gs_debit a
                    INNER JOIN gs_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c ON a.member_id = c.id
                    LEFT JOIN admin d ON c.admin_id = d.admin_id
                    WHERE c.admin_id IN (?) 
                    AND a.prd_id = :prdId2
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date4 OR '' = :start_date5)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date4 OR '' = :end_date5)
                    AND b.type != 'x'

                    UNION ALL

                    SELECT sum(a.bet) 'stake',sum((b.amount - a.bet)) 'win_loss',
                       sum(b.tier1_pt_amt) 'tier1_pt_amt',
                       sum(b.tier2_pt_amt) 'tier2_pt_amt',
                       sum(b.tier3_pt_amt) 'tier3_pt_amt',
                       sum(b.tier4_pt_amt) 'tier4_pt_amt',
                       sum(b.tier4_comm_amt) 'tier4_comm_amt'
                    FROM cp_debit a
                    INNER JOIN cp_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c ON a.member_id = c.id
                    LEFT JOIN admin d ON c.admin_id = d.admin_id
                    WHERE c.admin_id IN (?) 
                    AND a.prd_id = :prdId3
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date6 OR '' = :start_date7)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date6 OR '' = :end_date7)
                    AND b.type != 'x'
      
                    UNION ALL

                    SELECT sum(a.bet) 'stake',sum((b.amount - a.bet)) 'win_loss',
                       sum(b.tier1_pt_amt) 'tier1_pt_amt',
                       sum(b.tier2_pt_amt) 'tier2_pt_amt',
                       sum(b.tier3_pt_amt) 'tier3_pt_amt',
                       sum(b.tier4_pt_amt) 'tier4_pt_amt',
                       sum(b.tier4_comm_amt) 'tier4_comm_amt'
                    FROM noe_debit a
                    INNER JOIN noe_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c ON a.member_id = c.id
                    LEFT JOIN admin d ON c.admin_id = d.admin_id
                    WHERE c.admin_id IN (?) 
                    AND a.prd_id = :prdId4
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date8 OR '' = :start_date9)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date8 OR '' = :end_date9)
                    AND b.type != 'x'

                    UNION ALL

                    SELECT sum(a.bet) 'stake',sum((b.amount - a.bet)) 'win_loss',
                       sum(b.tier1_pt_amt) 'tier1_pt_amt',
                       sum(b.tier2_pt_amt) 'tier2_pt_amt',
                       sum(b.tier3_pt_amt) 'tier3_pt_amt',
                       sum(b.tier4_pt_amt) 'tier4_pt_amt',
                       sum(b.tier4_comm_amt) 'tier4_comm_amt'
                    FROM pussy_debit a
                    INNER JOIN pussy_credit b
                        ON a.txn_id = b.txn_id
                        AND a.prd_id = b.prd_id
                    INNER JOIN member c ON a.member_id = c.id
                    LEFT JOIN admin d ON c.admin_id = d.admin_id
                    WHERE c.admin_id IN (?) 
                    AND a.prd_id = :prdId5
                    AND ((a.created_at + INTERVAL 8 HOUR) >= :start_date10 OR '' = :start_date11)
                    AND ((a.created_at + INTERVAL 8 HOUR) <= :end_date10 OR '' = :end_date11)
                    AND b.type != 'x'

                    ";
      

                $params = [$adminArr,$adminArr,$adminArr,$adminArr,$adminArr,$adminArr];

                $preparedPDO = Helper::prepareWhereIn($sql,$params);
                
                $sql = $preparedPDO['sql'];
                $params = $preparedPDO['params'];

                $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
              
                $sql = $preparedPDO['sql'];
                $params = $preparedPDO['params'];

                $params['start_date'] = $startDate;
                $params['start_date1'] = $startDate;
                $params['end_date'] = $endDate;
                $params['end_date1'] = $endDate;
                $params['prdId'] = $prdId;


                $params['start_date2'] = $startDate;
                $params['start_date3'] = $startDate;
                $params['end_date2'] = $endDate;
                $params['end_date3'] = $endDate;
                $params['prdId1'] = $prdId;

               
                $params['start_date4'] = $startDate;
                $params['start_date5'] = $startDate;
                $params['end_date4'] = $endDate;
                $params['end_date5'] = $endDate;
                $params['prdId2'] = $prdId;


                $params['start_date6'] = $startDate;
                $params['start_date7'] = $startDate;
                $params['end_date6'] = $endDate;
                $params['end_date7'] = $endDate;
                $params['prdId3'] = $prdId;


                $params['start_date8'] = $startDate;
                $params['start_date9'] = $startDate;
                $params['end_date8'] = $endDate;
                $params['end_date9'] = $endDate;
                $params['prdId4'] = $prdId;


                $params['start_date10'] = $startDate;
                $params['start_date11'] = $startDate;
                $params['end_date10'] = $endDate;
                $params['end_date11'] = $endDate;
                $params['prdId5'] = $prdId;



                $dataTotal = DB::select($sql,$params);
            }

            return Response::make(json_encode([$data,$dataTotal]), 200);

        }
        catch(\Exception $e)
        {
            log::Debug($e);
        }
    }

    public static function getOptionsBetStatus()
    {
        return  [
                ['w', __('option.winloss.win')]
                ,['l', __('option.winloss.lose')]
                ,['t', __('option.winloss.tie')]
            ];
    }

    public static function getOptionsProduct()
    {
        return  [
                 ['1', __('Gameplay')]
                ,['2', __('BBIN')]
                ,['3', __('IBC')]
                ,['4', __('ALLBET')]
                ,['5', __('FastGame')]
                ,['6', __('CQ9')]
                ,['7', __('WM')]
                ,['8', __('Joker')]
                ,['9', __('PSB4D')]
                ,['10', __('Spade')]
                ,['11', __('QQKeno')]
                ,['12', __('CMD')]
                ,['13', __('M8BET')]
                ,['14', __('DIGMAAN')]
                ,['15', __('EBET')]
                ,['16', __('IA')]
                ,['17', __('NLIVE22')]
                ,['101', __('Ps9EVO')]
                ,['102', __('Ps9AG')]
                ,['103', __('Ps9PP')]
                ,['104', __('Ps9OT')]
                ,['105', __('Ps9PPSlot')]
                ,['106', __('Ps9Haba')]
                ,['107', __('Ps9Ely')]
                ,['108', __('Ps9QS')]
                ,['109', __('Ps9SG')]
                ,['110', __('Ps9AWS')]
                ,['111', __('Ps9PnG')]
                ,['112', __('Ps9WM')]
                ,['113', __('Ps9Micro')]
                ,['114', __('Ps9Joker')]
                ,['115', __('Ps9OTSlot')]
                ,['116', __('Ps9EvoRtg')]
                ,['117', __('Ps9Netent')]
                ,['118', __('Ps9Booon')]
                ,['119', __('Ps9Playson')]
                ,['120', __('Ps9PS')]
                ,['121', __('Ps9IA')]
                ,['200', __('MEGA')]
                ,['201', __('NOE')]
                ,['202', __('PUSSY')]
                ,['300', __('CP')]

        ];
    }


}
