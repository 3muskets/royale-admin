<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

use Auth;
use Log;

class AdminCreditController extends Controller
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
    public static function getCreditBalance()
    {
        $db = DB::select('SELECT available FROM ca_credit');

        return $db[0]->available;
    }

    public static function update(Request $request)
    {
        DB::beginTransaction();
        try 
        {
            $user = Auth::user();
            $username = $user->username;

            $amount = $request->input('amount');
            $amount = str_replace( ',', '', $amount);
            $type = $request->input('type');

            $errMsg = [];
           
            if(!Helper::checkInputFormat('numeric',$amount))
            {
                array_push($errMsg, __('error.merchant.credit.is_numeric'));
            }

            if($amount < 0)
            {
               array_push($errMsg, __('error.credit.merchant.nonnegative')); 
            }

            if(!Helper::validAmount($amount))
            {
                array_push($errMsg, __('error.merchant.invalid_credit_length'));
            }

            //check the merchant's credit avaialble for withdraw
            $db = DB::select('SELECT available 
                                FROM ca_credit'); 

            if(sizeof($db) > 0)
            {
                $availableCredit = $db[0]->available;
            }

            if($type == 'w')
            {
                if($amount > $availableCredit)
                {
                    array_push($errMsg, __('error.credit.merchant.exceed_limit'));
                }

                $amount = -$amount;
                $request["type"] = 'Withdraw';
            }
            else
            {
                $request["type"] = 'Deposit';
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            DB::UPDATE('UPDATE ca_credit 
                            SET available = available + ? ', 
                        [$amount]
                  );

            $request["log_old"] = '{}';
            $request["username"] = $username;
            $request["action_details"] = 37;

            Helper::log($request,'Update');

            $response = ['status' => 1];

            DB::commit();

            return json_encode($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            DB::rollback();
            $response = ['status' => 0
                        ,'error' => __('error.credit.merchant.invalid_credit')
                        ];

            return json_encode($response);
        }
    }

}
