<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;


use Auth;
use Log;

class RebateController extends Controller
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
                    SELECT status,start_date,end_date,min,max,new_mem_value,reg_mem_value,bronze_mem_value
                    ,silver_mem_value,gold_mem_value,plat_mem_value,frequency
                    FROM rebate_setting
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
            
            $startDate = $request->input('s_date1');
            $endDate = $request->input('e_date1');
            $minRebateAmt = $request->input('min');
            $maxRebateAmt = $request->input('max');
            $frequency = $request->input('frequency');

            //hack
            $frequency = 'd';
      
            $minRebateAmt = str_replace( ',', '', $minRebateAmt);
            $maxRebateAmt = str_replace( ',', '', $maxRebateAmt);
            

            //6 level
            $newMem =  $request->input('new');
            $regMem =  $request->input('reg');
            $bronzeMem =  $request->input('bronze');
            $slvMem =  $request->input('slv');
            $gldMem =  $request->input('gld');
            $pltMem =  $request->input('plt');


            $user = Auth::user();
            $userId = $user->admin_id;

            $errMsg = [];
           


            if(!Helper::checkValidOptions(self::getOptionsFrequency(),$frequency))
            {
                array_push($errMsg, __('Invalid Frequency'));
            }


            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                array_push($errMsg, __('Invalid Status'));
            }

/*            if (!Helper::checkInputFormat('numeric',$minRebateAmt)) 
            {
                array_push($errMsg, __('Min Amount must in numeric or greater than zero'));

            }
            if(!Helper::validAmount($minRebateAmt))
            {

                array_push($errMsg, __('Min Amount cannot exceed 15 digits'));

            }*/


            if (!Helper::checkInputFormat('numeric',$maxRebateAmt)) 
            {
                array_push($errMsg, __('Max Amount must in numeric or greater than zero'));

            }
            if(!Helper::validAmount($maxRebateAmt))
            {

                array_push($errMsg, __('Max Amount cannot exceed 15 digits'));


            }



        
            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            DB::update('UPDATE rebate_setting 
                            SET status =?,start_date =?,end_date =?,max=?,frequency=?
                            ,new_mem_value =?,reg_mem_value =?,bronze_mem_value =?,silver_mem_value=?,gold_mem_value=?,plat_mem_value=?
                            WHERE id=1', 
                            [$status,$startDate,$endDate,$maxRebateAmt,$frequency
                            ,$newMem,$regMem,$bronzeMem,$slvMem,$gldMem,$pltMem
                        ]
            );



        


            $response = ['status' => 1];
            DB::commit();
            return json_encode($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            DB::rollback();
            $response = ['status' => 0
                        ,'error' => __('Invalid Rebate Setting')
                        ];

            return json_encode($response);
        }
    }

    public static function countActiveAccount($adminId)
    {
        $count = 0;

        $db = DB::select("SELECT COUNT(id) 'count'
                            FROM admin_bank_info 
                            WHERE admin_id = ?  AND status = ?",[$adminId, 'a']);

        if(sizeof($db) > 0)
        {
           return $db[0]->count;
        }

        return $count;
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

