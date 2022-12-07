<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

use Auth;
use Log;

class LogController extends Controller
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
    public static function getList(Request $request)
    {
        Helper::checkUAC('permissions.settings');

        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            
            $username = $request->input('username');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $action = $request->input('action');
            
            if($action == null)
                $action = '';

            if($startDate == NULL)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == '')
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));

            $sql = "
                    SELECT a.id
                        ,b.username as operator_admin
                        ,a.username as username
                        ,a.query
                        ,a.data_old,a.data_new
                        ,a.ip_address, (a.timestamp + INTERVAL 8 HOUR) as timestamp
                        ,a.action_details
                    FROM admin_log a
                    LEFT JOIN admin b ON a.user_id = b.id
                    WHERE b.username LIKE :username
                        AND (a.action_details = :action or :action1 = '')
                        AND ((a.timestamp + INTERVAL 8 HOUR) >= :start_date OR '' = :start_date1)
                        AND ((a.timestamp + INTERVAL 8 HOUR) <= :end_date OR '' = :end_date1)
                    ";

            $params = [
                        'username' => '%'.$username.'%'
                        ,'action' => $action
                        ,'action1' => $action
                        ,'start_date' => $startDate
                        ,'start_date1' => $startDate
                        ,'end_date' => $endDate
                        ,'end_date1' => $endDate
                    ];             
                
            $orderByAllow = ['operator_admin','username','timestamp'];
            $orderByDefault = 'id desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
            $data = Helper::paginateData($sql,$params,$page);

            $aryActionDetails = self::getOptionsActionDetails();

            foreach ($data['results'] as $d) 
            {
                $d->data_old = self::localizeData($d->action_details, $d->data_old);
                $d->data_new = self::localizeData($d->action_details, $d->data_new);
                $d->action_details = Helper::getOptionsValue($aryActionDetails, $d->action_details);
            };

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return [];
        }
    }

    public static function getMappingActionDetails()
    {
        return  [
                    ['1','app.settings.log.create_merchant']
                    ,['2','app.settings.log.update_merchant']
                    ,['3','app.settings.log.merchant_activation']
                    ,['4','app.settings.log.merchant_settings']
                    ,['5','app.settings.log.create_bets']
                    ,['6','app.settings.log.update_bets']
                    ,['7','app.settings.log.create_coins']
                    ,['8','app.settings.log.update_coins']
                    ,['9','app.settings.log.create_admin']
                    ,['10','app.settings.log.update_admin']
                    ,['11','app.settings.log.admin_change_pwd']
                    ,['12','app.settings.log.create_sub']
                    ,['13','app.settings.log.update_sub']
                    ,['14','app.settings.log.sub_change_pwd']
                    ,['15','app.settings.log.change_pwd']
                ];
    }

    public static function getOptionsActionDetails()
    {
        $ary = self::getMappingActionDetails();

        $array = [];

        foreach($ary as $a)
        {
            $a[1] = __($a[1]);
            array_push($array, $a);
        }

        return $array;
    }

    public static function localizeData($actionDetailsId,$data)
    {
        //get action type
        $ary = self::getMappingActionDetails();
        $type = Helper::getOptionsValue($ary, $actionDetailsId);
        $array = [];

        $data = json_decode($data, true);

        foreach($data as $key => $value)
        {
            $data[__($type.'.'.$key)] = $value;
            unset($data[$key]);
        }

        return $data;
    }
}
