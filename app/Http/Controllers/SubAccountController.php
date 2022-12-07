<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;

use Auth;
use Log;

class SubAccountController extends Controller
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
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $username = $request->input('username');

            $user = Auth::User();   

            $mercId = $user->admin_id;

            $sql = "
                    SELECT id, username as username, fullname,(created_at + INTERVAL 8 HOUR) as created_at, status
                    FROM admin                        
                    WHERE type = 'm'                               
                        AND is_sub = 1  
                        AND admin_id = :id
                        AND username LIKE :username
                ";

            $params = [
                     'id' => $mercId
                    ,'username' => '%'.$username.'%'
                ];

            $orderByAllow = ['username','created_at'];
            $orderByDefault = 'username asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return [];
        }
    }

    public static function getSubAccount(Request $request)
    {
        try
        {
            $id = $request->input('id');
            
            $data = DB::select("
                    SELECT id, username as username, fullname,(created_at + INTERVAL 8 HOUR) as created_at, status
                    FROM admin
                    WHERE is_sub = 1
                        AND id = :id
                ",[$id]
            );

            return $data[0];
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function getOptionsStatus()
    {
        return  [
                ['a',__('option.subaccount.active')]
                ,['i',__('option.subaccount.inactive')]
            ];
    }


    public static function update(Request $request)
    {

        try
        {
            $id = $request->input('id');
            $fullname = $request->input('fullname');
            $status = $request->input('status');

            //validation
            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                $response = ['status' => 0
                            ,'error' => __('error.member.invalid_status')
                            ];

                return json_encode($response);
            }
           
            $data = DB::update("
                UPDATE admin
                SET status = ?, fullname=?
                WHERE id = ?",
                [ $status
                  ,$fullname
                  ,$id
                ]);

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            $response = ['status' => 0
                        ,'error' => __('error.member.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function checkUser(Request $request)
    {
        $error = '';

        $username = $request->input('username');

        if(strlen($username) >= 4 && strlen($username) <= 20 && Helper::checkInputFormat('alphanumeric', $username))
        {

            $db = DB::select('SELECT username FROM admin 
                WHERE username = ?',[$username]);

            if(sizeof($db) > 0)
            {
                $error = __('error.subaccount.input.duplicate_username');
                return json_encode($error);
            }
            else
            {
                return json_encode($error);
            }
        }
        elseif(preg_match('/[^a-zA-Z0-9]/', $username))
        {
            $error = __('error.subaccount.input.special_character');
            return json_encode($error);
        }
        elseif(!Helper::checkInputLength($username, 4, 20))
        {
            $error = __('error.subaccount.input.invalid_username_length');
            return json_encode($error);
        }
    }

    public static function create(Request $request)
    {

        try
        {
            $username = $request->input('username');
            $fullname = $request->input('fullname');
            $password = $request->input('password');
                      
            //validation
            $errMsg = [];

            $user = Auth::User();     

            $adminId = $user->admin_id;
            $adminUsername = $user->username;
            $level = $user->level;

            if(!Helper::checkInputLength($username, 4, 20))
            {
                array_push($errMsg, __('error.subaccount.input.invalid_username_length'));
            }

            //Validate username - must be alphanumeric or dot only
            if(!Helper::checkInputFormat('alphanumeric', $username))
            {
                array_push($errMsg, __('error.subaccount.input.special_character'));
            }

            $user = DB::select('SELECT username FROM admin WHERE username = ?', [$username]);

            if(sizeOf($user)!=0)
            {
                array_push($errMsg, __('error.admin.input.duplicate_username'));
            }

            if(!Helper::checkInputLength($fullname, 5, 20))
            {
                array_push($errMsg, 'Full name must be 5 to 20 in length');
            }

            if(!Helper::checkInputFormat('alphabet', $fullname))
            {
                array_push($errMsg, 'The name must in alphabet');
            }

            if(!$password)
            {
                array_push($errMsg, __('error.admin.invalid_password'));
            }

            if(!Helper::checkInputLength($password, 8, 15))
            {
                array_push($errMsg, __('error.admin.invalid_password_length'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $sql = "
                   INSERT INTO admin(username,password,status,suspended,ws_channel,created_at,type,level,fullname,admin_id,is_sub)
                    VALUES(:username,:password,'a',0,:wschannel,NOW(),'m',:level,:fullname,:id,1)
                ";

            $params = [
                    'username' => $username
                    ,'fullname' => $fullname
                    ,'password' => Hash::make($password)
                    ,'id' => $adminId
                    ,'level' => $level
                    ,'wschannel' => $adminUsername
                ];

            $data = DB::insert($sql,$params);
            
            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            log::debug($e);
            $errMsg = '';

            if($e instanceof \PDOException) 
            {
                if($e->errorInfo[1] == 1062)
                    $errMsg = "Duplicate Prefix!";
            }

            if($errMsg == '')
                $errMsg = __('error.admin.internal_error');
            
            $response = ['status' => 0
                        ,'error' => $errMsg
                        ];

            return json_encode($response);
        }
    }

    public static function changePassword(Request $request)
    {
        try 
        {
            $user_id = $request->input('id');
            $password = $request->input('password');

            //validation
            $errMsg = [];

            if(!$password)
            {
                array_push($errMsg, __('error.admin.invalid_password'));
            }

            if(!Helper::checkInputLength($password, 8, 15))
            {
                array_push($errMsg, __('error.admin.invalid_password_length'));
            }

            if(!Helper::checkInputFormat('alphanumeric', $password))
            {
                array_push($errMsg, __('error.admin.passwordalphanumeric'));
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
            $request["password"] = "*";

            Helper::log($request,'update');

            $response = ['status' => 1];

            return json_encode($response);
            
        } 
        catch (Exception $e) 
        {
            $response = ['status' => 0
                        ,'error' => __('error.admin.internal_error')
                        ];

            return json_encode($response);
        }
    }

}
