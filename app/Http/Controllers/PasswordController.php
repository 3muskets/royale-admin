<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

use Auth;
use Log;
// use DB;

class PasswordController extends Controller
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

    public static function getuserdetail()
    {
        $data = Auth::user();

        $response = $data;

        $response =  json_decode($response, true);

        return $response;
    }

    public static function changePassword(Request $request)
    {
        try 
        {
            $user_id = Auth::user()->id;
            $current_password = $request->input('current_password');
            $password = $request->input('new_password');
            $confirm_password = $request->input('confirm_password');

            //validation
            $errMsg = [];

            $db = DB::select('
                    SELECT password 
                    FROM admin 
                    WHERE id = ?
                    ',[$user_id]
                );

            $admin_password = $db[0]->password;

            if(!Hash::check($current_password,$admin_password) || !$current_password)
            {
               array_push($errMsg, __('error.admin.invalid_currentpassword')); 
            }

            if(!$password)
            {
                array_push($errMsg, __('error.admin.invalid_newpassword'));
            }

            if(Hash::check($password,$admin_password))
            {
                array_push($errMsg, __('error.admin.passwordscannotsame'));
            }

            if($password != $confirm_password)
            {
                array_push($errMsg, __('error.admin.passwordsnotmatch'));
            }

            if (!Helper::checkInputLength($password,8,15)) 
            {
                array_push($errMsg, __('error.admin.invalid_password_length'));
            }

            if(!Helper::checkInputFormat('alphanumericwithSymbol', $password))
            {
                array_push($errMsg, __('error.admin.password.input'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $new_password = Hash::make($password);

            $db = DB::update('UPDATE admin SET password = ? WHERE id = ?',
                    [$new_password,$user_id]
                  );

            //logging
            // $request["password"] = "*";

            // Helper::log($request,'update');

            $response = ['status' => 1];

            return json_encode($response);
            
        } 
        catch (\Exception $e) 
        {
            $response = ['status' => 0
                        ,'error' => __('error.admin.internal_error')
                        ];

            Log::Debug($e);
            return json_encode($response);
        }
    }
}
