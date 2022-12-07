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

class AgentCreditController extends Controller
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
            $username = $request->input('username');

            $user = Auth::user();
            $userLevel = $user->level;

            $adminId = '';

            if($userLevel > 0)
            {
                $adminId = $user->admin_id;
            }

            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));

    
            $sql = "
                    SELECT a.txn_id, a.type, ifnull(c.username,'COMPANY') 'username', a.credit_before, a.amount, (a.created_at + INTERVAL 8 HOUR) 'created_at', a.remark , b.username 'operator'
                    FROM admin_credit_txn a
                    LEFT JOIN admin b 
                    ON a.credit_by = b.id
                    LEFT JOIN admin c 
                    ON a.admin_id = c.id
                    LEFT JOIN tiers d
                    ON a.admin_id = d.admin_id
                    WHERE (a.admin_id = :id
                        OR (d.up1_tier = :id1 OR '' = :id2)
                        OR (d.up2_tier = :id3 OR '' = :id4))
                        AND ifnull(c.username,'COMPANY') LIKE :username 
                        AND (:start_date = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date1)
                        AND (:end_date = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date1)
                ";

            $params = 
            [
                'id' => $adminId
                ,'id1' => $adminId          
                ,'id2' => $adminId
                ,'id3' => $adminId          
                ,'id4' => $adminId 
                ,'username' => '%' . $username . '%'
                ,'start_date' => $startDate
                ,'start_date1' => $startDate
                ,'end_date' => $endDate
                ,'end_date1' => $endDate  
            ];

            
            $orderByAllow = ['txn_id','created_at'];
            $orderByDefault = 'created_at desc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page);

            $arrType = self::getOptionsType();

            foreach($data['results'] as $d)
            {
                $d->type_details = Helper::getOptionsValue($arrType, $d->type);

                $amount = $d->amount;

                if(strpos($amount, '-') !== false) 
                {
                    $d->transfer_in = '';
                    $d->transfer_out = $amount;

                }
                else
                {
                    $d->transfer_in = $amount;
                    $d->transfer_out = '';
                }

                $d->credit_after = $d->credit_before + $d->amount;
            }

            
            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
    }

    public static function getOptionsType()
    {
        return  [
                ['1', __('option.agent.credit.agent.deposit')]
                ,['2', __('option.agent.credit.agent.withdraw')]
                ,['3', __('option.agent.credit.add')]
                ,['4', __('option.agent.credit.reduce')]
                ,['5', __('option.agent.credit.member.deposit')]
            ];
    }
}
