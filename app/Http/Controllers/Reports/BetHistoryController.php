<?php

namespace App\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Controller;
use Auth;
use Log;

class BetHistoryController extends Controller
{

    public static function getList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $memberName = $request->input('member_name');
            $txnId = $request->input('txn_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $result = $request->input('result');

            $prdId = $request->input('prd_id');


            $agentId = $request->input('agent_id');

            if($agentId == null)
                $agentId = '';

            $user = Auth::user();
            $adminId = $user->admin_id;

            if($adminId == 1)
                $adminId = '';
    
            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));




            $sql = "
                    SELECT a.*,b.username 'agent'
                    FROM 
                    (
                        SELECT '1' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.amount 'debit'
                            , b.amount 'credit'
                            , a.game_id
                            , c.admin_id 
                            , b.type 
                             ,CASE WHEN b.type = 'x' THEN 'r'
                                WHEN b.amount = a.amount  THEN 't'
                                WHEN b.amount > a.amount THEN 'w'
                                WHEN b.amount < a.amount THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM evo_debit a
                        LEFT JOIN evo_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id
                            AND c.username LIKE :member_name
                            AND (:start_date = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date1)
                            AND (:end_date = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date1)
                            AND (c.admin_id = :admin_id OR :admin_id1 = '')
                            AND (c.admin_id = :agent_id OR :agent_id1 = '')

                        UNION ALL

                        SELECT '7' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.amount 'debit'
                            , b.amount 'credit'
                            , '' AS 'game_id'
                            , c.admin_id 
                            , b.type 
                             ,CASE WHEN b.type = 'x' THEN 'r'
                                WHEN b.amount = a.amount  THEN 't'
                                WHEN b.amount > a.amount THEN 'w'
                                WHEN b.amount < a.amount THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM ibc_debit a
                        LEFT JOIN ibc_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id1
                            AND c.username LIKE :member_name1
                            AND (:start_date2 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date3)
                            AND (:end_date2 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date3)
                            AND (c.admin_id = :admin_id2 OR :admin_id3 = '')
                            AND (c.admin_id = :agent_id2 OR :agent_id3 = '')



                        UNION ALL

                        SELECT '8' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.amount 'debit'
                            , b.amount 'credit'
                            , a.game_id AS 'game_id'
                            , c.admin_id 
                            , b.type 
                             ,CASE WHEN b.type = 'x' THEN 'r'
                                WHEN b.amount = a.amount  THEN 't'
                                WHEN b.amount > a.amount THEN 'w'
                                WHEN b.amount < a.amount THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM joker_debit a
                        LEFT JOIN joker_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id2
                            AND c.username LIKE :member_name2
                            AND (:start_date4 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date5)
                            AND (:end_date4 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date5)
                            AND (c.admin_id = :admin_id4 OR :admin_id5 = '')
                            AND (c.admin_id = :agent_id4 OR :agent_id5 = '')



                        UNION ALL

                        SELECT '9' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.bet 'debit'
                            , b.amount 'credit'
                            , '' AS 'game_id'
                            , c.admin_id 
                            , b.type 
                             ,CASE WHEN b.type = 'x' THEN 'r'
                                WHEN b.amount = a.bet  THEN 't'
                                WHEN b.amount > a.bet THEN 'w'
                                WHEN b.amount < a.bet THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM noe_debit a
                        LEFT JOIN noe_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id3
                            AND c.username LIKE :member_name3
                            AND (:start_date6 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date7)
                            AND (:end_date6 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date7)
                            AND (c.admin_id = :admin_id6 OR :admin_id7 = '')
                            AND (c.admin_id = :agent_id6 OR :agent_id7 = '')


                        UNION ALL

                        SELECT '3' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.amount 'debit'
                            , b.amount 'credit'
                            , a.game_id
                            , c.admin_id 
                            , b.type 
                             ,CASE WHEN b.type = 'x' THEN 'r'
                                WHEN b.amount = a.amount  THEN 't'
                                WHEN b.amount > a.amount THEN 'w'
                                WHEN b.amount < a.amount THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM sa_debit a
                        LEFT JOIN sa_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id4
                            AND c.username LIKE :member_name4
                            AND (:start_date8 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date9)
                            AND (:end_date8 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date9)
                            AND (c.admin_id = :admin_id8 OR :admin_id9 = '')
                            AND (c.admin_id = :agent_id8 OR :agent_id9 = '')


                        UNION ALL

                        SELECT '6' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.amount 'debit'
                            , b.winloss 'credit'
                            , '' AS 'game_id'
                            , c.admin_id 
                            , '' AS 'type'
                             ,CASE /*WHEN b.type = 'x' THEN 'r'*/
                                WHEN b.winloss = a.amount  THEN 't'
                                WHEN b.winloss > a.amount THEN 'w'
                                WHEN b.winloss < a.amount THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM sbo_debit a
                        LEFT JOIN sbo_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id5
                            AND c.username LIKE :member_name5
                            AND (:start_date10 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date11)
                            AND (:end_date10 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date11)
                            AND (c.admin_id = :admin_id10 OR :admin_id11 = '')
                            AND (c.admin_id = :agent_id10 OR :agent_id11 = '')


                        UNION ALL

                        SELECT '10' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.bet 'debit'
                            , b.amount 'credit'
                            , a.game_id
                            , c.admin_id 
                            , b.type
                             ,CASE WHEN b.type = 'x' THEN 'r'
                                WHEN b.amount = a.bet  THEN 't'
                                WHEN b.amount > a.bet THEN 'w'
                                WHEN b.amount < a.bet THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM pussy_debit a
                        LEFT JOIN pussy_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id6
                            AND c.username LIKE :member_name6
                            AND (:start_date12 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date13)
                            AND (:end_date12 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date13)
                            AND (c.admin_id = :admin_id12 OR :admin_id13 = '')
                            AND (c.admin_id = :agent_id12 OR :agent_id13 = '')

                        UNION ALL

                        SELECT '2' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.bet 'debit'
                            , b.amount 'credit'
                            , '' AS 'game_id'
                            , c.admin_id 
                            , '' AS 'type'
                             ,CASE /*WHEN b.type = 'x' THEN 'r'*/
                                WHEN b.amount = a.bet  THEN 't'
                                WHEN b.amount > a.bet THEN 'w'
                                WHEN b.amount < a.bet THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM ab_debit a
                        LEFT JOIN ab_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id7
                            AND c.username LIKE :member_name7
                            AND (:start_date14 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date15)
                            AND (:end_date14 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date15)
                            AND (c.admin_id = :admin_id14 OR :admin_id15 = '')
                            AND (c.admin_id = :agent_id14 OR :agent_id15 = '')

                        UNION ALL

                        SELECT '5' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.amount 'debit'
                            , b.amount 'credit'
                            , '' AS 'game_id'
                            , c.admin_id 
                            , '' AS 'type'
                             ,CASE /*WHEN b.type = 'x' THEN 'r'*/
                                WHEN b.amount = a.amount  THEN 't'
                                WHEN b.amount > a.amount THEN 'w'
                                WHEN b.amount < a.amount THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM pt_debit a
                        LEFT JOIN pt_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id8
                            AND c.username LIKE :member_name8
                            AND (:start_date16 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date17)
                            AND (:end_date16 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date17)
                            AND (c.admin_id = :admin_id16 OR :admin_id17 = '')
                            AND (c.admin_id = :agent_id16 OR :agent_id17 = '')

                        UNION ALL
                        
                        SELECT '12' AS 'prd_id',a.txn_id,a.member_id, c.username 'username'
                            , (a.created_at + INTERVAL 8 HOUR) 'timestamp'
                            , a.amount 'debit'
                            , b.amount 'credit'
                            , '' AS 'game_id'
                            , c.admin_id 
                            , '' AS 'type'
                             ,CASE /*WHEN b.type = 'x' THEN 'r'*/
                                WHEN b.amount = a.amount  THEN 't'
                                WHEN b.amount > a.amount THEN 'w'
                                WHEN b.amount < a.amount THEN 'l'
                                ELSE 'p'
                            END 'bet_result'
                        FROM xe88_debit a
                        LEFT JOIN xe88_credit b ON a.txn_id = b.txn_id 
                        LEFT JOIN member c ON a.member_id = c.id 
                        WHERE a.txn_id LIKE :txn_id9
                            AND c.username LIKE :member_name9
                            AND (:start_date18 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date19)
                            AND (:end_date18 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date19)
                            AND (c.admin_id = :admin_id18 OR :admin_id19 = '')
                            AND (c.admin_id = :agent_id18 OR :agent_id19 = '')

                    ) a
                    LEFT JOIN admin b ON a.admin_id = b.id
                    
                    WHERE a.bet_result IN (?) AND a.prd_id = ?

                  


                ";

            $params = [$result,$prdId];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['txn_id'] = '%'.$txnId.'%';
            $params['member_name'] = '%'.$memberName.'%';
            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;
            $params['admin_id'] = $adminId;
            $params['admin_id1'] = $adminId;
            $params['agent_id'] = $agentId;
            $params['agent_id1'] = $agentId;

            $params['txn_id1'] = '%'.$txnId.'%';
            $params['member_name1'] = '%'.$memberName.'%';
            $params['start_date2'] = $startDate;
            $params['start_date3'] = $startDate;
            $params['end_date2'] = $endDate;
            $params['end_date3'] = $endDate;
            $params['admin_id2'] = $adminId;
            $params['admin_id3'] = $adminId;
            $params['agent_id2'] = $agentId;
            $params['agent_id3'] = $agentId;

            $params['txn_id2'] = '%'.$txnId.'%';
            $params['member_name2'] = '%'.$memberName.'%';
            $params['start_date4'] = $startDate;
            $params['start_date5'] = $startDate;
            $params['end_date4'] = $endDate;
            $params['end_date5'] = $endDate;
            $params['admin_id4'] = $adminId;
            $params['admin_id5'] = $adminId;
            $params['agent_id4'] = $agentId;
            $params['agent_id5'] = $agentId;


            $params['txn_id3'] = '%'.$txnId.'%';
            $params['member_name3'] = '%'.$memberName.'%';
            $params['start_date6'] = $startDate;
            $params['start_date7'] = $startDate;
            $params['end_date6'] = $endDate;
            $params['end_date7'] = $endDate;
            $params['admin_id6'] = $adminId;
            $params['admin_id7'] = $adminId;
            $params['agent_id6'] = $agentId;
            $params['agent_id7'] = $agentId;

            $params['txn_id4'] = '%'.$txnId.'%';
            $params['member_name4'] = '%'.$memberName.'%';
            $params['start_date8'] = $startDate;
            $params['start_date9'] = $startDate;
            $params['end_date8'] = $endDate;
            $params['end_date9'] = $endDate;
            $params['admin_id8'] = $adminId;
            $params['admin_id9'] = $adminId;
            $params['agent_id8'] = $agentId;
            $params['agent_id9'] = $agentId;


            $params['txn_id5'] = '%'.$txnId.'%';
            $params['member_name5'] = '%'.$memberName.'%';
            $params['start_date10'] = $startDate;
            $params['start_date11'] = $startDate;
            $params['end_date10'] = $endDate;
            $params['end_date11'] = $endDate;
            $params['admin_id10'] = $adminId;
            $params['admin_id11'] = $adminId;
            $params['agent_id10'] = $agentId;
            $params['agent_id11'] = $agentId;

            $params['txn_id6'] = '%'.$txnId.'%';
            $params['member_name6'] = '%'.$memberName.'%';
            $params['start_date12'] = $startDate;
            $params['start_date13'] = $startDate;
            $params['end_date12'] = $endDate;
            $params['end_date13'] = $endDate;
            $params['admin_id12'] = $adminId;
            $params['admin_id13'] = $adminId;
            $params['agent_id12'] = $agentId;
            $params['agent_id13'] = $agentId;

            $params['txn_id7'] = '%'.$txnId.'%';
            $params['member_name7'] = '%'.$memberName.'%';
            $params['start_date14'] = $startDate;
            $params['start_date15'] = $startDate;
            $params['end_date14'] = $endDate;
            $params['end_date15'] = $endDate;
            $params['admin_id14'] = $adminId;
            $params['admin_id15'] = $adminId;
            $params['agent_id14'] = $agentId;
            $params['agent_id15'] = $agentId;

            $params['txn_id8'] = '%'.$txnId.'%';
            $params['member_name8'] = '%'.$memberName.'%';
            $params['start_date16'] = $startDate;
            $params['start_date17'] = $startDate;
            $params['end_date16'] = $endDate;
            $params['end_date17'] = $endDate;
            $params['admin_id16'] = $adminId;
            $params['admin_id17'] = $adminId;
            $params['agent_id16'] = $agentId;
            $params['agent_id17'] = $agentId;


            $params['txn_id9'] = '%'.$txnId.'%';
            $params['member_name9'] = '%'.$memberName.'%';
            $params['start_date18'] = $startDate;
            $params['start_date19'] = $startDate;
            $params['end_date18'] = $endDate;
            $params['end_date19'] = $endDate;
            $params['admin_id18'] = $adminId;
            $params['admin_id19'] = $adminId;
            $params['agent_id18'] = $agentId;
            $params['agent_id19'] = $agentId;

            $orderByAllow = ['txn_id','debit','credit','username','timestamp','bet_result','game_id'];
            $orderByDefault = 'timestamp desc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page,20);

            $aryBetResult = self::getOptionsBetResult();
            
            foreach ($data['results'] as $d) 
            {      
                $d->bet_result_desc = Helper::getOptionsValue($aryBetResult, $d->bet_result);

            }


            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
    }


    public static function getOptionsBetResult()
    {
        return  [
                ['w', __('option.txn.win')]
                ,['l', __('option.txn.lose')]
                ,['t', __('option.txn.tie')]
                ,['r', __('option.txn.refund')]
                ,['p', __('option.txn.pending')]
            ];
    }
}
