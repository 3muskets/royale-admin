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

class MemberCreditController extends Controller
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
            $tier4 = $request->input('tier4');

            $user = Auth::user();
            $userLevel = $user->level;
            $adminId = $user->admin_id;
            $agentId = $request->input('agent_id');

            if($agentId == null)
                $agentId = '';


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
                    SELECT a.txn_id, a.type, a.credit_before, a.amount, (a.created_at + INTERVAL 8 HOUR) 'created_at', a.remark , b.username 'member', c.username 'operator',f.payment_type,g.username 'agent',txn_type,a.prd_id
                    FROM member_credit_txn a
                    LEFT JOIN member b 
                    ON a.member_id = b.id
                    LEFT JOIN admin c
                    ON a.credit_by = c.id
                    LEFT JOIN member_dw f
                    ON a.dw_id = f.id
                    LEFT JOIN admin g
                    ON b.admin_id = g.id
                    WHERE  b.username LIKE :username  
                        AND (:start_date = '' OR (a.created_at + INTERVAL 8 HOUR) >= :start_date1)
                        AND (:end_date = '' OR (a.created_at + INTERVAL 8 HOUR) <= :end_date1)
                        AND (b.admin_id = :admin_id OR :admin_id1 = '')
                        AND (b.admin_id = :agent_id OR :agent_id1 = '')
                ";

            $params = 
            [
                'start_date' => $startDate
                ,'start_date1' => $startDate
                ,'end_date' => $endDate
                ,'end_date1' => $endDate 
                ,'username' => '%' . $username . '%'           
                ,'admin_id' => $adminId
                ,'admin_id1' => $adminId 
                ,'agent_id' => $agentId
                ,'agent_id1' => $agentId 

            ];
            
            $orderByAllow = ['txn_id','created_at'];
            $orderByDefault = 'created_at desc,txn_id desc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page);

            $arrType = self::getOptionsType();

            $aryTxnType = self::getOptionsTxnType();

            $aryProduct = self::getOptionsProduct();

            foreach($data['results'] as $d)
            {
                $d->type_details = Helper::getOptionsValue($arrType, $d->type);

                $d->payment_type_text = Helper::getOptionsValue(self::getOptionsPaymentType(), $d->payment_type);

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


                $d->txn_type_desc = Helper::getOptionsValue($aryTxnType, $d->txn_type);


                $d->transfer_detail = '';


                if($d->txn_type == 3)
                {

                    $d->prd_name = Helper::getOptionsValue($aryProduct, $d->prd_id);

                   if($d->type == 2)
                        $d->transfer_detail = $d->prd_name.' transfer to Main Wallet';
                    else
                        $d->transfer_detail ='Main Wallet transfer to '.$d->prd_name;

                }



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

    public static function getOptionsTxnType()
    {
        return  [
                ['1',__('Deposit')]
                ,['2',__('Withdraw')]
                ,['3',__('Transfer')]
                ,['4',__('Promotion')]
                ,['5',__('Referral')]
                ,['6',__('Bonus')]
                ,['7',__('Cashback')]
                ,['8',__('Adjustment')]
            ];
    }


    public static function getOptionsProduct()
    {
        return  [
                ['1000', __('SBO')]
                ,['1001', __('EVO')]
                ,['1002', __('IBC')]
                ,['1003', __('SA Gaming')]
                ,['1004', __('ALL BET')]
                ,['1005', __('Playtech')]
                ,['1006', __('JOKER')]
                ,['1007', __('XE88')]
                ,['1008', __('MEGA888')]
                ,['1009', __('NOE')]
                ,['1010', __('SCR')]
                ,['1011', __('PUSSY')]
                ,['1012', __('SexyGaming')]
                ,['1013', __('KAYA')]
            ];


    }



}
