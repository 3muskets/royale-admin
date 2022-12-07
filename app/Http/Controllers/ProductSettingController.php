<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

use Auth;
use Log;

class ProductSettingController extends Controller
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



    public static function getOptionsStatus()
    {
        return  [
                ['a', __('option.admin.active')]
                ,['i', __('option.admin.inactive')]
            ];
    }

    public static function getList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $username = $request->input('username');

            $sql = "
                    SELECT prd_id,status,(updated_at+ INTERVAL 8 HOUR) 'updated_at'
                    FROM product_setting 
                ";



            $params = [
                ];

            $orderByAllow = [];
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
            Log::Debug($e);
            return [];
        }
    }

    public static function update(Request $request)
    {
        try
        {

            $id = $request->input('prd_id');
            $status = $request->input('status');
            
            //validation
            if($status != 0 && $status != 1)
            {
                $response = ['status' => 0
                            ,'error' => __('error.admin.invalid_status')
                            ];

                return json_encode($response);
            }

            $sql = "
                UPDATE product_setting
                SET status = :status
                ,updated_at = NOW()
                WHERE prd_id = :id
                ";

            $params = [
                    'id' => $id
                    ,'status' => $status
                ];

            $data = DB::update($sql,$params);

        
            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.admin.internal_error')
                        ];

            return json_encode($response);
        }
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
