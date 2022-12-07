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

class MemberReferralController extends Controller
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
                    SELECT a.member_id,a.tier,a.date,a.amount, (a.created_at + INTERVAL 8 HOUR) 'created_at',a.is_reject,b.username 'member_name'
                    FROM member_referral_txn a
                    LEFT JOIN member b 
                    ON a.member_id = b.id
                    LEFT JOIN tiers c
                    ON b.admin_id = c.admin_id
                    LEFT JOIN admin e
                    ON b.admin_id = e.admin_id
                    WHERE (b.admin_id = :id OR c.up1_tier = :id1 OR c.up2_tier = :id2 OR  '' = :id3)
                        AND b.username LIKE :username  
                        AND (:start_date = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date1)
                        AND (:end_date = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date1)
                ";


            $params = 
            [
                'id' => $adminId
                ,'id1' => $adminId          
                ,'id2' => $adminId
                ,'id3' => $adminId          
                ,'start_date' => $startDate
                ,'start_date1' => $startDate
                ,'end_date' => $endDate
                ,'end_date1' => $endDate 
                ,'username' => '%' . $username . '%'
            ];
            
            $orderByAllow = ['member_id','member_name','tier','amount','date','created_at'];
            $orderByDefault = 'member_id,date,tier asc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page);

            foreach($data['results'] as $d)
            {

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
                ['1',  __('option.member.credit.deposit')]
                ,['2', __('option.member.credit.add')]
                ,['3', __('option.member.credit.reduce')]
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

}
