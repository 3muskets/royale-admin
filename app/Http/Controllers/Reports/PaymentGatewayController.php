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

class PaymentGatewayController extends Controller
{
    public static function getList(Request $request)
    {
        try
        {
            log::debug($request);
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
                        SELECT a.member_id,a.merchant_order_no 'txn_id',a.amount,a.status,(a.created_at + INTERVAL 8 HOUR) 'created_at','f2f' as 'provider',b.username
                        FROM payment_gateway_f2f_order a
                        INNER JOIN member b
                        ON a.member_id = b.id
                        WHERE a.type = 'returnurl'
                        AND b.username LIKE :username1
                        AND (:start_date1 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date2)
                        AND (:end_date1 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date2)
                        UNION ALL
                        SELECT b.id 'member_id',a.id 'txn_id',a.amount,a.status,(a.created_at + INTERVAL 8 HOUR) 'created_at','cw' as 'provider',b.username
                        FROM payment_gateway_cw_order a
                        INNER JOIN member b
                        ON a.wallet_address = b.wallet_address
                        WHERE b.username LIKE :username2 
                        AND (:start_date3 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date4)
                        AND (:end_date3 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date4)
                        UNION ALL
                        SELECT a.member_id,a.txn_id,a.amount,a.status,(a.created_at + INTERVAL 8 HOUR) 'created_at','dn' as 'provider',b.username
                        FROM dn_payment_order a
                        INNER JOIN member b
                        ON a.member_id = b.id
                        WHERE b.username LIKE :username3 
                        AND (:start_date5 = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date6)
                        AND (:end_date5 = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date6)
                ";

            $params = 
            [
                'username1' => '%'.$username.'%'
                ,'username2' => '%'.$username.'%'
                ,'username3' => '%'.$username.'%'
                ,'start_date1' => $startDate
                ,'start_date2' => $startDate
                ,'start_date3' => $startDate
                ,'start_date4' => $startDate
                ,'start_date5' => $startDate
                ,'start_date6' => $startDate
                ,'end_date1' => $endDate
                ,'end_date2' => $endDate  
                ,'end_date3' => $endDate
                ,'end_date4' => $endDate
                ,'end_date5' => $endDate  
                ,'end_date6' => $endDate  
            ];

            
            $orderByAllow = ['txn_id','member_id','username','amount','created_at'];
            $orderByDefault = 'txn_id desc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page);

            foreach($data['results'] as $d)
            {
                $d->provider_text = Helper::getOptionsValue(self::getOptionsProvider(), $d->provider);

                if($d->provider == 'f2f')
                    $d->status_desc =  Helper::getOptionsValue(self::getOptions5PayStatus(), $d->status);
                else if($d->provider == 'cw')
                    $d->status_desc = '';
                else if($d->provider == 'dn')
                    $d->status_desc =  Helper::getOptionsValue(self::getOptionsDnStatus(), $d->status);
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


    public static function getOptionsProvider()
    {
        return  [
                ['f2f', __('5Pay (Fiat2Fiat FPX)')]
                ,['cw', __('5Pay (Crypto Wallet System)')]
                ,['dn', __('Doitnow')]
            ];
    }

    public static function getOptions5PayStatus()
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


    public static function getOptionsDnStatus()
    {

         //todo localization
        return  [
            ['r', __('Reject')]
            ,['a', __('Accept')]
        ];       
    }
}
