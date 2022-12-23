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

class PromotionController extends Controller
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

            if($username == null)
                $username = '';

            if($startDate == null)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == null)
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));

    
            $sql = "
                        SELECT a.member_id,a.id,a.deposit_amount,a.promo_amount,a.status
                        ,a.turnover,a.target_turnover,a.win_loss,a.target_winover
                        ,(a.created_at + INTERVAL 8 HOUR) 'created_at',b.username,c.promo_name
                        FROM member_promo_turnover a
                        INNER JOIN member b
                        ON a.member_id = b.id
                        INNER JOIN promo_setting c
                        ON a.promo_id = c.promo_id
                        WHERE  b.username LIKE :username1
                        AND (:start_date1 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date2)
                        AND (:end_date1 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date2)

                ";

            $params = 
            [
                'username1' => '%'.$username.'%'
                ,'start_date1' => $startDate
                ,'start_date2' => $startDate
                ,'end_date1' => $endDate
                ,'end_date2' => $endDate  
            ];

            
            $orderByAllow = ['id','member_id','username','deposit_amount','promo_amount','turnover','target_turnover','target_winover','win_loss','created_at'];
            $orderByDefault = 'id desc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page);

            foreach($data['results'] as $d)
            {
                $d->status_desc =  Helper::getOptionsValue(self::getOptionsStatus(), $d->status);
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


    public static function getOptionsStatus()
    {
        return  [
                ['s', __('Promotion Finish')]
                ,['p', __('Pending')]
            ];
    }

 
}
