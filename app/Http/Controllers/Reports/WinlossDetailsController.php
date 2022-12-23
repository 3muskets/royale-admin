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

class WinLossDetailsController extends Controller
{
    public static function getList(Request $request) 
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $user = Auth::user();
            $userLevel = $user->level;
            $adminId = $user->admin_id;



            $agentId = $request->input('agent_id');

            if($agentId == null)
                $agentId = '';


            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d H:i:s',strtotime($startDate.'-8 hours'));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d H:i:s',strtotime($endDate.'23:59:59'.'-8 hours'));

            if($adminId == 1)
                $adminId = '';


            $sql ="
                    SELECT a.member_id 'id',a.username,b.username 'agent'
                        , SUM(a.total_wager) 'total_wager'
                        , SUM(a.total_turnover)'total_turnover'
                        , SUM(a.total_winloss) 'member_winloss'
                    FROM 
                    (
                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM evo_debit a 
                        INNER JOIN evo_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date OR '' = :start_date1)
                            AND (a.created_at <= :end_date OR '' = :end_date1)
                            AND (c.admin_id = :admin_id OR :admin_id1 = '')
                            AND (c.admin_id = :agent_id OR :agent_id1 = '')
                        GROUP BY a.member_id,c.username,c.admin_id 

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM ibc_debit a 
                        INNER JOIN ibc_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date2 OR '' = :start_date3)
                            AND (a.created_at <= :end_date2 OR '' = :end_date3)
                            AND (c.admin_id = :admin_id2 OR :admin_id3 = '')
                            AND (c.admin_id = :agent_id2 OR :agent_id3 = '')
                        GROUP BY a.member_id,c.username,c.admin_id 

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM joker_debit a 
                        INNER JOIN joker_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date4 OR '' = :start_date5)
                            AND (a.created_at <= :end_date4 OR '' = :end_date5)
                            AND (c.admin_id = :admin_id4 OR :admin_id5 = '')
                            AND (c.admin_id = :agent_id4 OR :agent_id5 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.bet) 'total_turnover', SUM(b.amount-a.bet) 'total_winloss' 
                        ,c.admin_id
                        FROM noe_debit a 
                        INNER JOIN noe_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date6 OR '' = :start_date7)
                            AND (a.created_at <= :end_date6 OR '' = :end_date7)
                            AND (c.admin_id = :admin_id6 OR :admin_id7 = '')
                            AND (c.admin_id = :agent_id6 OR :agent_id7 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM sa_debit a 
                        INNER JOIN sa_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date8 OR '' = :start_date9)
                            AND (a.created_at <= :end_date8 OR '' = :end_date9)
                            AND (c.admin_id = :admin_id8 OR :admin_id9 = '')
                            AND (c.admin_id = :agent_id8 OR :agent_id9 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.winloss) 'total_winloss' 
                        ,c.admin_id
                        FROM sbo_debit a 
                        INNER JOIN sbo_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date10 OR '' = :start_date11)
                            AND (a.created_at <= :end_date10 OR '' = :end_date11)
                            AND (c.admin_id = :admin_id10 OR :admin_id11 = '')
                            AND (c.admin_id = :agent_id10 OR :agent_id11 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.bet) 'total_turnover', SUM(b.amount-a.bet) 'total_winloss' 
                        ,c.admin_id
                        FROM scr_debit a 
                        INNER JOIN scr_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date12 OR '' = :start_date13)
                            AND (a.created_at <= :end_date12 OR '' = :end_date13)
                            AND (c.admin_id = :admin_id12 OR :admin_id13 = '')
                            AND (c.admin_id = :agent_id12 OR :agent_id13 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.bet) 'total_turnover', SUM(b.amount-a.bet) 'total_winloss' 
                        ,c.admin_id
                        FROM ab_debit a 
                        INNER JOIN ab_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date14 OR '' = :start_date15)
                            AND (a.created_at <= :end_date14 OR '' = :end_date15)
                            AND (c.admin_id = :admin_id14 OR :admin_id15 = '')
                            AND (c.admin_id = :agent_id14 OR :agent_id15 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM pt_debit a 
                        INNER JOIN pt_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date16 OR '' = :start_date17)
                            AND (a.created_at <= :end_date16 OR '' = :end_date17)
                            AND (c.admin_id = :admin_id16 OR :admin_id17 = '')
                            AND (c.admin_id = :agent_id16 OR :agent_id17 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  


                        UNION ALL

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM xe88_debit a 
                        INNER JOIN xe88_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date18 OR '' = :start_date19)
                            AND (a.created_at <= :end_date18 OR '' = :end_date19)
                            AND (c.admin_id = :admin_id18 OR :admin_id19 = '')
                            AND (c.admin_id = :agent_id18 OR :agent_id19 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  


                    ) a
                    LEFT JOIN admin b ON a.admin_id = b.id
                    GROUP BY a.member_id,a.username,b.username

                ";

                $params = 
                [
                    'start_date' => $startDate
                    ,'start_date1' => $startDate
                    ,'start_date2' => $startDate
                    ,'start_date3' => $startDate
                    ,'start_date4' => $startDate
                    ,'start_date5' => $startDate
                    ,'start_date6' => $startDate
                    ,'start_date7' => $startDate
                    ,'start_date8' => $startDate
                    ,'start_date9' => $startDate
                    ,'start_date10' => $startDate
                    ,'start_date11' => $startDate
                    ,'start_date12' => $startDate
                    ,'start_date13' => $startDate
                    ,'start_date14' => $startDate
                    ,'start_date15' => $startDate
                    ,'start_date16' => $startDate
                    ,'start_date17' => $startDate
                    ,'start_date18' => $startDate
                    ,'start_date19' => $startDate

                    ,'end_date' => $endDate
                    ,'end_date1' => $endDate
                    ,'end_date2' => $endDate
                    ,'end_date3' => $endDate
                    ,'end_date4' => $endDate
                    ,'end_date5' => $endDate
                    ,'end_date6' => $endDate
                    ,'end_date7' => $endDate
                    ,'end_date8' => $endDate
                    ,'end_date9' => $endDate
                    ,'end_date10' => $endDate
                    ,'end_date11' => $endDate
                    ,'end_date12' => $endDate
                    ,'end_date13' => $endDate
                    ,'end_date14' => $endDate
                    ,'end_date15' => $endDate
                    ,'end_date16' => $endDate
                    ,'end_date17' => $endDate
                    ,'end_date18' => $endDate
                    ,'end_date19' => $endDate

                    ,'admin_id' => $adminId
                    ,'admin_id1' => $adminId
                    ,'admin_id2' => $adminId
                    ,'admin_id3' => $adminId
                    ,'admin_id4' => $adminId
                    ,'admin_id5' => $adminId
                    ,'admin_id6' => $adminId
                    ,'admin_id7' => $adminId
                    ,'admin_id8' => $adminId
                    ,'admin_id9' => $adminId
                    ,'admin_id10' => $adminId
                    ,'admin_id11' => $adminId
                    ,'admin_id12' => $adminId
                    ,'admin_id13' => $adminId              
                    ,'admin_id14' => $adminId
                    ,'admin_id15' => $adminId
                    ,'admin_id16' => $adminId
                    ,'admin_id17' => $adminId
                    ,'admin_id18' => $adminId
                    ,'admin_id19' => $adminId

                    ,'agent_id' => $agentId
                    ,'agent_id1' => $agentId
                    ,'agent_id2' => $agentId
                    ,'agent_id3' => $agentId
                    ,'agent_id4' => $agentId
                    ,'agent_id5' => $agentId
                    ,'agent_id6' => $agentId
                    ,'agent_id7' => $agentId
                    ,'agent_id8' => $agentId
                    ,'agent_id9' => $agentId
                    ,'agent_id10' => $agentId
                    ,'agent_id11' => $agentId
                    ,'agent_id12' => $agentId
                    ,'agent_id13' => $agentId              
                    ,'agent_id14' => $agentId
                    ,'agent_id15' => $agentId
                    ,'agent_id16' => $agentId
                    ,'agent_id17' => $agentId
                    ,'agent_id18' => $agentId
                    ,'agent_id19' => $agentId

                ];

            $orderByAllow = ['member_id', 'total_wager', 'total_turnover', 'total_winloss'];
            $orderByDefault = 'member_id asc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);

            $data = Helper::paginateData($sql, $params, $page,200);

            foreach($data['results'] as $d)
            {
            }

            $dataTotal = [];


            //only need query to total again if multiple page
            if($data['count'] > $data['page_size'])
            {
            $sql ="
                    SELECT  SUM(a.total_wager) 'total_wager'
                        , SUM(a.total_turnover)'total_turnover'
                        , SUM(a.total_winloss) 'member_winloss'
                    FROM 
                    (
                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM evo_debit a 
                        INNER JOIN evo_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date OR '' = :start_date1)
                            AND (a.created_at <= :end_date OR '' = :end_date1)
                            AND (c.admin_id = :admin_id OR :admin_id1 = '')
                            AND (c.admin_id = :agent_id OR :agent_id1 = '')
                        GROUP BY a.member_id,c.username,c.admin_id 

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM ibc_debit a 
                        INNER JOIN ibc_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date2 OR '' = :start_date3)
                            AND (a.created_at <= :end_date2 OR '' = :end_date3)
                            AND (c.admin_id = :admin_id2 OR :admin_id3 = '')
                            AND (c.admin_id = :agent_id2 OR :agent_id3 = '')
                        GROUP BY a.member_id,c.username,c.admin_id 

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM joker_debit a 
                        INNER JOIN joker_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date4 OR '' = :start_date5)
                            AND (a.created_at <= :end_date4 OR '' = :end_date5)
                            AND (c.admin_id = :admin_id4 OR :admin_id5 = '')
                            AND (c.admin_id = :agent_id4 OR :agent_id5 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.bet) 'total_turnover', SUM(b.amount-a.bet) 'total_winloss' 
                        ,c.admin_id
                        FROM noe_debit a 
                        INNER JOIN noe_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date6 OR '' = :start_date7)
                            AND (a.created_at <= :end_date6 OR '' = :end_date7)
                            AND (c.admin_id = :admin_id6 OR :admin_id7 = '')
                            AND (c.admin_id = :agent_id6 OR :agent_id7 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM sa_debit a 
                        INNER JOIN sa_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date8 OR '' = :start_date9)
                            AND (a.created_at <= :end_date8 OR '' = :end_date9)
                            AND (c.admin_id = :admin_id8 OR :admin_id9 = '')
                            AND (c.admin_id = :agent_id8 OR :agent_id9 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.winloss) 'total_winloss' 
                        ,c.admin_id
                        FROM sbo_debit a 
                        INNER JOIN sbo_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date10 OR '' = :start_date11)
                            AND (a.created_at <= :end_date10 OR '' = :end_date11)
                            AND (c.admin_id = :admin_id10 OR :admin_id11 = '')
                            AND (c.admin_id = :agent_id10 OR :agent_id11 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.bet) 'total_turnover', SUM(b.amount-a.bet) 'total_winloss' 
                        ,c.admin_id
                        FROM scr_debit a 
                        INNER JOIN scr_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date12 OR '' = :start_date13)
                            AND (a.created_at <= :end_date12 OR '' = :end_date13)
                            AND (c.admin_id = :admin_id12 OR :admin_id13 = '')
                            AND (c.admin_id = :agent_id12 OR :agent_id13 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL   

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.bet) 'total_turnover', SUM(b.amount-a.bet) 'total_winloss' 
                        ,c.admin_id
                        FROM ab_debit a 
                        INNER JOIN ab_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date14 OR '' = :start_date15)
                            AND (a.created_at <= :end_date14 OR '' = :end_date15)
                            AND (c.admin_id = :admin_id14 OR :admin_id15 = '')
                            AND (c.admin_id = :agent_id14 OR :agent_id15 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  

                        UNION ALL

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM pt_debit a 
                        INNER JOIN pt_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date16 OR '' = :start_date17)
                            AND (a.created_at <= :end_date16 OR '' = :end_date17)
                            AND (c.admin_id = :admin_id16 OR :admin_id17 = '')
                            AND (c.admin_id = :agent_id16 OR :agent_id17 = '')
                        GROUP BY a.member_id,c.username,c.admin_id  
                        
                        UNION ALL

                        SELECT 
                        a.member_id,c.username,COUNT(a.txn_id) 'total_wager', SUM(a.amount) 'total_turnover', SUM(b.amount-a.amount) 'total_winloss' 
                        ,c.admin_id
                        FROM xe88_debit a 
                        INNER JOIN xe88_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN member c
                            ON c.id = a.member_id
                        WHERE (a.created_at >= :start_date18 OR '' = :start_date19)
                            AND (a.created_at <= :end_date18 OR '' = :end_date19)
                            AND (c.admin_id = :admin_id18 OR :admin_id19 = '')
                            AND (c.admin_id = :agent_id18 OR :agent_id19 = '')
                        GROUP BY a.member_id,c.username,c.admin_id 

                    ) a
                    LEFT JOIN admin b ON a.admin_id = b.id

                ";

                $params = 
                [
                    'start_date' => $startDate
                    ,'start_date1' => $startDate
                    ,'start_date2' => $startDate
                    ,'start_date3' => $startDate
                    ,'start_date4' => $startDate
                    ,'start_date5' => $startDate
                    ,'start_date6' => $startDate
                    ,'start_date7' => $startDate
                    ,'start_date8' => $startDate
                    ,'start_date9' => $startDate
                    ,'start_date10' => $startDate
                    ,'start_date11' => $startDate
                    ,'start_date12' => $startDate
                    ,'start_date13' => $startDate
                    ,'start_date14' => $startDate
                    ,'start_date15' => $startDate
                    ,'start_date16' => $startDate
                    ,'start_date17' => $startDate
                    ,'start_date18' => $startDate
                    ,'start_date19' => $startDate

                    ,'end_date' => $endDate
                    ,'end_date1' => $endDate
                    ,'end_date2' => $endDate
                    ,'end_date3' => $endDate
                    ,'end_date4' => $endDate
                    ,'end_date5' => $endDate
                    ,'end_date6' => $endDate
                    ,'end_date7' => $endDate
                    ,'end_date8' => $endDate
                    ,'end_date9' => $endDate
                    ,'end_date10' => $endDate
                    ,'end_date11' => $endDate
                    ,'end_date12' => $endDate
                    ,'end_date13' => $endDate
                    ,'end_date14' => $endDate
                    ,'end_date15' => $endDate
                    ,'end_date16' => $endDate
                    ,'end_date17' => $endDate
                    ,'end_date18' => $endDate
                    ,'end_date19' => $endDate

                    ,'admin_id' => $adminId
                    ,'admin_id1' => $adminId
                    ,'admin_id2' => $adminId
                    ,'admin_id3' => $adminId
                    ,'admin_id4' => $adminId
                    ,'admin_id5' => $adminId
                    ,'admin_id6' => $adminId
                    ,'admin_id7' => $adminId
                    ,'admin_id8' => $adminId
                    ,'admin_id9' => $adminId
                    ,'admin_id10' => $adminId
                    ,'admin_id11' => $adminId
                    ,'admin_id12' => $adminId
                    ,'admin_id13' => $adminId              
                    ,'admin_id14' => $adminId
                    ,'admin_id15' => $adminId
                    ,'admin_id16' => $adminId
                    ,'admin_id17' => $adminId
                    ,'admin_id18' => $adminId
                    ,'admin_id19' => $adminId

                    ,'agent_id' => $agentId
                    ,'agent_id1' => $agentId
                    ,'agent_id2' => $agentId
                    ,'agent_id3' => $agentId
                    ,'agent_id4' => $agentId
                    ,'agent_id5' => $agentId
                    ,'agent_id6' => $agentId
                    ,'agent_id7' => $agentId
                    ,'agent_id8' => $agentId
                    ,'agent_id9' => $agentId
                    ,'agent_id10' => $agentId
                    ,'agent_id11' => $agentId
                    ,'agent_id12' => $agentId
                    ,'agent_id13' => $agentId              
                    ,'agent_id14' => $agentId
                    ,'agent_id15' => $agentId
                    ,'agent_id16' => $agentId
                    ,'agent_id17' => $agentId
                    ,'agent_id18' => $agentId
                    ,'agent_id19' => $agentId
                ];


                $dataTotal = DB::select($sql,$params);


            }
            
            return Response::make(json_encode([$data,$dataTotal]), 200);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }


    public static function getProduct(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $id = $request->input('id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $userLevel =  Auth::user()->level;

            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d H:i:s',strtotime($startDate.'-8 hours'));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d H:i:s',strtotime($endDate.'23:59:59'.'-8 hours'));

            $sql = "
                    SELECT '1' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.amount) 'total_wager',
                    SUM(a.amount) 'turnover',
                    SUM(b.amount - a.amount) 'win_loss'
                    FROM evo_debit a
                    INNER JOIN evo_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id
                        AND (a.created_at >= :start_date OR '' = :start_date1)
                        AND (a.created_at <= :end_date OR '' = :end_date1)
                    GROUP BY a.member_id, c.username

                    UNION ALL

                    SELECT '7' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.amount) 'total_wager',
                    SUM(a.amount) 'turnover',
                    SUM(b.amount - a.amount) 'win_loss'
                    FROM ibc_debit a
                    INNER JOIN ibc_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id1
                        AND (a.created_at >= :start_date2 OR '' = :start_date3)
                        AND (a.created_at <= :end_date2 OR '' = :end_date3)
                    GROUP BY a.member_id, c.username
        
                    UNION ALL

                    SELECT '8' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.amount) 'total_wager',
                    SUM(a.amount) 'turnover',
                    SUM(b.amount - a.amount) 'win_loss'
                    FROM joker_debit a
                    INNER JOIN joker_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id2
                        AND (a.created_at >= :start_date4 OR '' = :start_date5)
                        AND (a.created_at <= :end_date4 OR '' = :end_date5)
                    GROUP BY a.member_id, c.username    

                    UNION ALL

                    SELECT '9' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.bet) 'total_wager',
                    SUM(a.bet) 'turnover',
                    SUM(b.amount - a.bet) 'win_loss'
                    FROM noe_debit a
                    INNER JOIN noe_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id3
                        AND (a.created_at >= :start_date6 OR '' = :start_date7)
                        AND (a.created_at <= :end_date6 OR '' = :end_date7)
                    GROUP BY a.member_id, c.username    


                    UNION ALL

                    SELECT '3' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.amount) 'total_wager',
                    SUM(a.amount) 'turnover',
                    SUM(b.amount - a.amount) 'win_loss'
                    FROM sa_debit a
                    INNER JOIN sa_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id4
                        AND (a.created_at >= :start_date8 OR '' = :start_date9)
                        AND (a.created_at <= :end_date8 OR '' = :end_date9)
                    GROUP BY a.member_id, c.username    

                    UNION ALL

                    SELECT '6' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.amount) 'total_wager',
                    SUM(a.amount) 'turnover',
                    SUM(b.winloss) 'win_loss'
                    FROM sbo_debit a
                    INNER JOIN sbo_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id5
                        AND (a.created_at >= :start_date10 OR '' = :start_date11)
                        AND (a.created_at <= :end_date10 OR '' = :end_date11)
                    GROUP BY a.member_id, c.username    

                    UNION ALL

                    SELECT '10' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.bet) 'total_wager',
                    SUM(a.bet) 'turnover',
                    SUM(b.amount - a.bet) 'win_loss'
                    FROM scr_debit a
                    INNER JOIN scr_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id6
                        AND (a.created_at >= :start_date12 OR '' = :start_date13)
                        AND (a.created_at <= :end_date12 OR '' = :end_date13)
                    GROUP BY a.member_id, c.username    

                    UNION ALL

                    SELECT '2' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.bet) 'total_wager',
                    SUM(a.bet) 'turnover',
                    SUM(b.amount - a.bet) 'win_loss'
                    FROM ab_debit a
                    INNER JOIN ab_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id7
                        AND (a.created_at >= :start_date14 OR '' = :start_date15)
                        AND (a.created_at <= :end_date14 OR '' = :end_date15)
                    GROUP BY a.member_id, c.username    

                    UNION ALL

                    SELECT '5' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.amount) 'total_wager',
                    SUM(a.amount) 'turnover',
                    SUM(b.amount - a.amount) 'win_loss'
                    FROM pt_debit a
                    INNER JOIN pt_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id8
                        AND (a.created_at >= :start_date16 OR '' = :start_date17)
                        AND (a.created_at <= :end_date16 OR '' = :end_date17)
                    GROUP BY a.member_id, c.username   

                    UNION ALL

                    SELECT '12' AS 'prd_id',
                    a.member_id 'member_id',
                    c.username 'username',
                    COUNT(a.amount) 'total_wager',
                    SUM(a.amount) 'turnover',
                    SUM(b.amount - a.amount) 'win_loss'
                    FROM xe88_debit a
                    INNER JOIN xe88_credit b ON a.txn_id = b.txn_id 
                    INNER JOIN member c ON a.member_id = c.id
                    WHERE a.member_id= :id9
                        AND (a.created_at >= :start_date18 OR '' = :start_date19)
                        AND (a.created_at <= :end_date18 OR '' = :end_date19)
                    GROUP BY a.member_id, c.username   


                ";
    
                $params = [
                        'id' => $id
                        ,'id1' => $id
                        ,'id2' => $id
                        ,'id3' => $id
                        ,'id4' => $id
                        ,'id5' => $id
                        ,'id6' => $id
                        ,'id7' => $id
                        ,'id8' => $id
                        ,'id9' => $id
                        
                        ,'start_date' => $startDate
                        ,'start_date1' => $startDate
                        ,'start_date2' => $startDate
                        ,'start_date3' => $startDate
                        ,'start_date4' => $startDate
                        ,'start_date5' => $startDate
                        ,'start_date6' => $startDate
                        ,'start_date7' => $startDate
                        ,'start_date8' => $startDate
                        ,'start_date9' => $startDate
                        ,'start_date10' => $startDate
                        ,'start_date11' => $startDate
                        ,'start_date12' => $startDate
                        ,'start_date13' => $startDate
                        ,'start_date14' => $startDate
                        ,'start_date15' => $startDate
                        ,'start_date16' => $startDate
                        ,'start_date17' => $startDate 
                        ,'start_date18' => $startDate
                        ,'start_date19' => $startDate                        

                        ,'end_date' => $endDate
                        ,'end_date1' => $endDate
                        ,'end_date2' => $endDate
                        ,'end_date3' => $endDate
                        ,'end_date4' => $endDate
                        ,'end_date5' => $endDate
                        ,'end_date6' => $endDate
                        ,'end_date7' => $endDate
                        ,'end_date8' => $endDate
                        ,'end_date9' => $endDate
                        ,'end_date10' => $endDate
                        ,'end_date11' => $endDate
                        ,'end_date12' => $endDate
                        ,'end_date13' => $endDate
                        ,'end_date14' => $endDate
                        ,'end_date15' => $endDate
                        ,'end_date16' => $endDate
                        ,'end_date17' => $endDate
                        ,'end_date18' => $endDate 
                        ,'end_date19' => $endDate 


                    ];

            $orderByAllow = ["prd_id","win_loss","total_wager","turnover"];
            $orderByDefault = 'prd_id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
           
            $data = Helper::paginateData($sql,$params,$page);

            $aryProduct = self::getOptionsProduct();

            foreach($data['results'] as $d)
            {
                $d->prd_name = Helper::getOptionsValue($aryProduct, $d->prd_id);
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
            //member id 
            $id = $request->input('id');
            $prdId = $request->input('prd_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $userLevel =  Auth::user()->level;

            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d H:i:s',strtotime($startDate.'-8 hours'));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d H:i:s',strtotime($endDate.'23:59:59'.'-8 hours'));

                $sql = "
                    SELECT *
                    FROM
                    (
                        SELECT '1' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.amount 'stake',
                        (b.amount - a.amount) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.amount) > 0  THEN 'w'
                            WHEN (b.amount - a.amount) < 0  THEN 'l'
                            WHEN (b.amount - a.amount) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM evo_debit a
                        INNER JOIN evo_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id
                            AND (a.created_at >= :start_date OR '' = :start_date1)
                            AND (a.created_at <= :end_date OR '' = :end_date1)

                        UNION ALL

                        SELECT '7' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.amount 'stake',
                        (b.amount - a.amount) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.amount) > 0  THEN 'w'
                            WHEN (b.amount - a.amount) < 0  THEN 'l'
                            WHEN (b.amount - a.amount) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM ibc_debit a
                        INNER JOIN ibc_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id1
                            AND (a.created_at >= :start_date2 OR '' = :start_date3)
                            AND (a.created_at <= :end_date2 OR '' = :end_date3)

                        UNION ALL

                        SELECT '8' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.amount 'stake',
                        (b.amount - a.amount) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.amount) > 0  THEN 'w'
                            WHEN (b.amount - a.amount) < 0  THEN 'l'
                            WHEN (b.amount - a.amount) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM joker_debit a
                        INNER JOIN joker_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id2
                            AND (a.created_at >= :start_date4 OR '' = :start_date5)
                            AND (a.created_at <= :end_date4 OR '' = :end_date5)

                        UNION ALL

                        SELECT '9' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.bet 'stake',
                        (b.amount - a.bet) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.bet) > 0  THEN 'w'
                            WHEN (b.amount - a.bet) < 0  THEN 'l'
                            WHEN (b.amount - a.bet) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM noe_debit a
                        INNER JOIN noe_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id3
                            AND (a.created_at >= :start_date6 OR '' = :start_date7)
                            AND (a.created_at <= :end_date6 OR '' = :end_date7)

                        UNION ALL

                        SELECT '3' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.amount 'stake',
                        (b.amount - a.amount) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.amount) > 0  THEN 'w'
                            WHEN (b.amount - a.amount) < 0  THEN 'l'
                            WHEN (b.amount - a.amount) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM sa_debit a
                        INNER JOIN sa_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id4
                            AND (a.created_at >= :start_date8 OR '' = :start_date9)
                            AND (a.created_at <= :end_date8 OR '' = :end_date9)

                        UNION ALL

                        SELECT '6' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.amount 'stake',
                        (b.winloss) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.winloss) > 0  THEN 'w'
                            WHEN (b.winloss) < 0  THEN 'l'
                            WHEN (b.winloss) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM sbo_debit a
                        INNER JOIN sbo_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id5
                            AND (a.created_at >= :start_date10 OR '' = :start_date11)
                            AND (a.created_at <= :end_date10 OR '' = :end_date11)

                        UNION ALL

                        SELECT '10' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.bet 'stake',
                        (b.amount - a.bet) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.bet) > 0  THEN 'w'
                            WHEN (b.amount - a.bet) < 0  THEN 'l'
                            WHEN (b.amount - a.bet) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM scr_debit a
                        INNER JOIN scr_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id6
                            AND (a.created_at >= :start_date12 OR '' = :start_date13)
                            AND (a.created_at <= :end_date12 OR '' = :end_date13)


                        UNION ALL

                        SELECT '2' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.bet 'stake',
                        (b.amount - a.bet) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.bet) > 0  THEN 'w'
                            WHEN (b.amount - a.bet) < 0  THEN 'l'
                            WHEN (b.amount - a.bet) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM ab_debit a
                        INNER JOIN ab_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id7
                            AND (a.created_at >= :start_date14 OR '' = :start_date15)
                            AND (a.created_at <= :end_date14 OR '' = :end_date15)

                        UNION ALL

                        SELECT '5' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.amount 'stake',
                        (b.amount - a.amount) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.amount) > 0  THEN 'w'
                            WHEN (b.amount - a.amount) < 0  THEN 'l'
                            WHEN (b.amount - a.amount) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM pt_debit a
                        INNER JOIN pt_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id8
                            AND (a.created_at >= :start_date16 OR '' = :start_date17)
                            AND (a.created_at <= :end_date16 OR '' = :end_date17)
                        
                        UNION ALL


                        SELECT '12' AS 'prd_id',
                        a.txn_id,
                        a.member_id 'member_id',
                        c.username 'username',
                        a.amount 'stake',
                        (b.amount - a.amount) 'win_loss',
                        a.created_at 'debit_date',
                        CASE
                            WHEN (b.amount - a.amount) > 0  THEN 'w'
                            WHEN (b.amount - a.amount) < 0  THEN 'l'
                            WHEN (b.amount - a.amount) = 0  THEN 't'
                            ELSE 0
                            END AS bet_status
                        FROM xe88_debit a
                        INNER JOIN xe88_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE a.member_id= :id9
                            AND (a.created_at >= :start_date18 OR '' = :start_date19)
                            AND (a.created_at <= :end_date18 OR '' = :end_date19)



                    ) AS a
                    WHERE a.prd_id = :prd_id

                            ";
     

                $params = [
                         'prd_id' =>$prdId
                        ,'id' => $id
                        ,'id1' => $id
                        ,'id2' => $id
                        ,'id3' => $id
                        ,'id4' => $id
                        ,'id5' => $id
                        ,'id6' => $id
                        ,'id7' => $id
                        ,'id8' => $id
                        ,'id9' => $id
   
                        
                        ,'start_date' => $startDate
                        ,'start_date1' => $startDate
                        ,'start_date2' => $startDate
                        ,'start_date3' => $startDate
                        ,'start_date4' => $startDate
                        ,'start_date5' => $startDate
                        ,'start_date6' => $startDate
                        ,'start_date7' => $startDate
                        ,'start_date8' => $startDate
                        ,'start_date9' => $startDate
                        ,'start_date10' => $startDate
                        ,'start_date11' => $startDate
                        ,'start_date12' => $startDate
                        ,'start_date13' => $startDate
                        ,'start_date14' => $startDate
                        ,'start_date15' => $startDate
                        ,'start_date16' => $startDate
                        ,'start_date17' => $startDate
                        ,'start_date18' => $startDate
                        ,'start_date19' => $startDate
                    

                        ,'end_date' => $endDate
                        ,'end_date1' => $endDate
                        ,'end_date2' => $endDate
                        ,'end_date3' => $endDate
                        ,'end_date4' => $endDate
                        ,'end_date5' => $endDate
                        ,'end_date6' => $endDate
                        ,'end_date7' => $endDate
                        ,'end_date8' => $endDate
                        ,'end_date9' => $endDate
                        ,'end_date10' => $endDate
                        ,'end_date11' => $endDate
                        ,'end_date12' => $endDate
                        ,'end_date13' => $endDate
                        ,'end_date14' => $endDate
                        ,'end_date15' => $endDate
                        ,'end_date16' => $endDate
                        ,'end_date17' => $endDate
                        ,'end_date18' => $endDate
                        ,'end_date19' => $endDate


                    ];


            $orderByAllow = [];
            $orderByDefault = 'debit_date desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
           
            $data = Helper::paginateData($sql,$params,$page);

            $aryBetStatus= self::getOptionsBetStatus();

            $aryProduct = self::getOptionsProduct();
            
            foreach($data['results'] as $d)
            {
                $d->bet_status_desc = Helper::getOptionsValue($aryBetStatus, $d->bet_status);
                $d->prd_name = Helper::getOptionsValue($aryProduct, $d->prd_id);
            }


            $dataTotal = [];

            //only need query to total again if multiple page
            if($data['count'] > $data['page_size'])
            {
                if($prdId == 1)
                {
                    $sql = "SELECT sum(a.amount) 'stake',sum((b.amount - a.amount)) 'win_loss',
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
                            WHERE a.member_id= :id
                                AND (a.created_at >= :start_date OR '' = :start_date1)
                                AND (a.created_at <= :end_date OR '' = :end_date1)
                            -- AND b.type != 'x'
                            ";
                }

                $params = [
                        'id' => $id
                        ,'start_date' => $startDate
                        ,'start_date1' => $startDate
                        ,'end_date' => $endDate
                        ,'end_date1' => $endDate
                    ];

                $dataTotal = DB::select($sql,$params);


            }

            return Response::make(json_encode([$data,$dataTotal]), 200);

        }
        catch(\Exception $e)
        {
            log::Debug($e);
        }
    }

    public static function getResultsBet(Request $request)
    {
        $prdId = $request->input('prd_id');
        $txnId = $request->input('txn_id');
        $memberId = $request->input('member_id');
        $roundId = $request->input('round_id');

        if($prdId == '1')
        {
            $data = ProviderController::getEvoBetDetail($request);
        }
        else if($prdId == '2')
        {
            $data = ProviderController::getHabaBetDetail($request);
        } 
        else if($prdId == '3' )
        {
            $data = ProviderController::getPPBetDetail($request);
        }
        else if($prdId == '4')
        {
            $data = ProviderController::getWmBetDetail($request);
        }

        return $data;
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
                ['1', __('EVO')]
                ,['2', __('All Bet')]
                ,['3', __('SA Gaming')]
                ,['4', __('Sexy Gaming')]
                ,['5', __('Playtech')]
                ,['6', __('SBO')]
                ,['7', __('IBC')]
                ,['8', __('JOKER')]
                ,['9', __('918KISS')]
                ,['10', __('SCR888')]
                ,['11', __('MEGA888')]
                ,['12', __('XE88')]

            ];
    }

}
