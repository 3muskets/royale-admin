<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;


use Auth;
use Log;

class CashBackSettingController extends Controller
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
        try
        {

            $data = DB::select("
                    SELECT status,start_date,end_date,rate,amount
                    FROM cashback_setting
                    ");

            
            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }


    public static function update(Request $request)
    {

        DB::beginTransaction();
        try 
        {


            $status = $request->input('status');
            /*$frequency = $request->input('frequency');
*/

            
            $startDate = $request->input('s_date1');
            $endDate = $request->input('e_date1');

            $amount = $request->input('amount');
            $amount = str_replace( ',', '', $amount);
            
            //array 
            $rate = $request->input('rate');



            $user = Auth::user();
            $userId = $user->admin_id;

            $errMsg = [];
           
            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                array_push($errMsg, __('Invalid Status'));
            }

            if($rate == '')
            {
                array_push($errMsg, __('Rate Cannot Be Null'));
            }


            if (!Helper::checkInputFormat('amount',$rate)) 
            {
                array_push($errMsg, __('Rate Must be Number'));

            }

            if (!Helper::checkInputFormat('amount',$amount)) 
            {
                array_push($errMsg, __('Target Lost Must be Number'));

            }
            else
            {
                if($amount <= 0)
                {
                    array_push($errMsg, __('Target Lost Cannot Be Negaitve'));
                }                
            }


        
            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            DB::update('
                UPDATE cashback_setting 
                SET status =?,start_date =?,end_date =?,amount=?
                ,rate =?
                ', 
                [$status,$startDate,$endDate,$amount
                ,$rate]);



        

            $response = ['status' => 1];
            DB::commit();
            return json_encode($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            DB::rollback();
            $response = ['status' => 0
                        ,'error' => __('Invalid CashBack Setting')
                        ];

            return json_encode($response);
        }
    }


    public static function getOptionsStatus()
    {
        return  [
            ['a', __('Active')]
            ,['i', __('Inactive')]
        ];
    }

    public static function getOptionsFrequency()
    {
        return  [
            ['d', __('Daily')]
            ,['w', __('Weekly')]
            ,['m', __('Monthly')]
        ];
    }
    
}

