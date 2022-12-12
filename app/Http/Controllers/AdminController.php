<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

use Auth;
use Log;

class AdminController extends Controller
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

    public static function getAdmin(Request $request)
    {
        try
        {
            $id = $request->input('id');
            
            $sql = "
                        SELECT a.id,a.username,(a.created_at + INTERVAL 8 HOUR) 'created_at',a.status, b.role_id
                        FROM admin a
                        LEFT JOIN admin_role b ON a.id = b.admin_id 
                        WHERE id = :id
                        AND level = 0
                    ";

            $params = [
                    'id' => $id
                ];

            $data = DB::select($sql,$params);



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

            $userId = Auth::user()->admin_id;

            $sql = "
                SELECT a.id,a.username,(a.created_at + INTERVAL 8 HOUR) 'created_at' ,a.status,a.admin_id
                ,c.type,c.is_deleted,a.is_sub,b.role_id
                FROM admin a
                LEFT JOIN admin_role b ON a.id = b.admin_id 
                LEFT JOIN role c ON c.id = b.role_id    
                WHERE level = 0 AND a.id > 1
                    AND a.admin_id = :userId
                    AND a.username LIKE :username
                    AND a.id != :userId1

                ";



            $params = [
                    'username' => '%'.$username.'%'
                    ,'userId' => $userId
                    ,'userId1' => $userId
                ];

            $orderByAllow = ['username','created_at'];
            $orderByDefault = 'username asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            $aryStatus = self::getOptionsStatus();

            foreach($data['results'] as $d)
            {
                $d->status_desc = Helper::getOptionsValue($aryStatus, $d->status);

                if($d->is_sub == 0)
                    $d->user_type = 'Main Account';
                else if($d->is_sub == 1)
                    $d->user_type = 'Sub Account';
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

            $id = $request->input('id');
            $status = $request->input('status');
            $adminUsername = $request->input('username');
            $roleId = $request->input('role');
            
            //validation
            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                $response = ['status' => 0
                            ,'error' => __('error.admin.invalid_status')
                            ];

                return json_encode($response);
            }

            $sql = "
                UPDATE admin
                SET status = :status
                WHERE id = :id
                ";

            $params = [
                    'id' => $id
                    ,'status' => $status
                ];

            $data = DB::update($sql,$params);

            $data = DB::update($sql,$params);

            $sql3 = "
                        INSERT INTO admin_role
                        (admin_id,role_id)
                        VALUES
                        (:admin_id, :role_id)
                        ON DUPLICATE KEY UPDATE 
                        role_id = :role_id1
                        ";
            $params3 = [
                    'admin_id' => $id
                    ,'role_id' => $roleId
                    ,'role_id1' => $roleId
                ];

            DB::insert($sql3, $params3);


            //logging
            $request["username"] = $adminUsername;
            $request["action_details"] = 10;

            /*Helper::log($request,'update');*/
            
            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.admin.internal_error')
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
                $error = __('error.admin.input.duplicate_username');
                return json_encode($error);
            }
            else
            {
                return json_encode($error);
            }
        }
        elseif(preg_match('/[^a-zA-Z0-9]/', $username))
        {
            $error = __('error.admin.input.special_character');
            return json_encode($error);
        }
        elseif(!Helper::checkInputLength($username, 4, 20))
        {
            $error = __('error.admin.input.invalid_username_length');
            return json_encode($error);
        }
    }

    public static function create(Request $request)
    {

        try
        {
            $username = $request->input('username');
            $password = $request->input('password');
            $status = $request->input('status');
            $roleId = $request->input('role');

            $userId = Auth::user()->admin_id;

            //validation
            $errMsg = [];

            $db = DB::select('SELECT username FROM admin 
                WHERE username = ?',[$username]);

            if(sizeof($db) > 0)
            {
                array_push($errMsg, __('error.admin.input.duplicate_username'));
            }

            if(!Helper::checkInputLength($username, 4, 20))
            {
                array_push($errMsg, __('error.admin.input.invalid_username_length'));
            }

            //Validate username - must be alphanumeric or dot only
            if(!Helper::checkInputFormat('alphanumeric', $username))
            {
                array_push($errMsg, __('error.admin.username.alphanumericWithDot'));
            }

            if(!$password)
            {
                array_push($errMsg, __('error.admin.invalid_password'));
            }

            //Validate password length - must between 8 to 15
            if(!Helper::checkInputLength($password, 8, 15))
            {
                array_push($errMsg, __('error.admin.invalid_password_length'));
            }

            if(!Helper::checkInputFormat('alphanumericwithSymbol', $password))
            {
                array_push($errMsg, __('error.admin.password.input'));
            }

            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                array_push($errMsg, __('error.admin.invalid_status'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $sql = "
                INSERT INTO admin(username,password,status,ws_channel,created_at,level,is_sub,admin_id)
                VALUES(:username,:password,:status,'s',NOW(),0, 0,:userId)
                ";

            $params = [
                    'username' => $username
                    ,'password' => Hash::make($password)
                    ,'status' => $status
                    ,'userId' => $userId
                ];

            $data = DB::insert($sql,$params);
            $id = DB::getPdo()->lastInsertId();
            
            $sql = "
                INSERT INTO admin_role(admin_id,role_id)
                VALUES(:id,:role_id)
                ";

            $params = [
                    'id' => $id
                    ,'role_id' => $roleId
                ];


            $data = DB::insert($sql,$params);

           

            $sql = "
                SELECT super_admin FROM admin 
                WHERE id = :id
                ";

            $params = [
                    'id' => $userId
                ];

            $db = DB::select($sql, $params);


            //logging
            $request["id"] = $id;
            $request["password"] = "*";
            
            /*Helper::log($request,'create');*/

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            $errMsg = '';

            if($e instanceof \PDOException) 
            {
                if($e->errorInfo[1] == 1062)
                    $errMsg = __('error.admin.duplicate_username');
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

    public static function createRoles(Request $request)
    {
        try
        {
            $name = $request->input('name');
            $check = $request->input('check');
          
            //validation
            $errMsg = [];

            //check exist type
            $db = DB::select("SELECT type
                                FROM role
                                WHERE type = ? AND is_deleted = 0"
                                ,[$name]
                            );

            if(sizeof($db) > 0)
            {
                array_push($errMsg, "Role Type exists");
            }

            //Validate username - must be alphanumeric
            if(!Helper::checkInputFormat('alphanumeric', $name))
            {
                array_push($errMsg, __('error.admin.error_alpha'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            DB::insert("
                INSERT INTO role(type,is_deleted)
                VALUES(?,?)
                ",[$name,0]);

            $id = DB::getPdo()->lastInsertId();

            $paramsPermission = [];

            foreach($check as $c)
            {
                $arr = explode('-',$c);

                array_push($paramsPermission,[$id,$arr[0],$arr[1]]);
            }

            $sql = "
                        INSERT INTO role_permissions
                        (role_id,type,is_deleted)
                        VALUES
                        :(?,?,?):
                        ON DUPLICATE KEY UPDATE 
                        is_deleted = VALUES(is_deleted)
                        ";

            $pdo = Helper::prepareBulkInsert($sql,$paramsPermission);

            DB::insert($pdo['sql'],$pdo['params']);
            
            // logging
            $request["log_old"] = "{}";
            $request["action_details"] = 34;
            
            Helper::log($request,'Create');

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return [];
        }
    }

    public static function getRolesList(Request $request)
    {
        try
        {
            log::Debug($request);
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $name = $request->input('name');

            $sql = "
                SELECT id,type
                FROM role 
                WHERE is_deleted = 0
                   
                ";

            $params = [

                ];

            $orderByAllow = ['id','type'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);


            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return [];
        }
    }

    public static function deleteRole(Request $request)
    {
        try
        {
            $name = $request->input('name');

            $errMsg = [];

            $db = DB::select("SELECT type
                                FROM role
                                WHERE type = ? AND is_deleted = 0"
                                ,[$name]
                            );

            if(sizeof($db) == 0)
            {
                array_push($errMsg, "Invalid Role Type");
            }
            
            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            DB::update("UPDATE role 
                        SET is_deleted = 1 
                        WHERE type = ?"
                        ,[$name]
                    );

            $request['name'] = '';
            $request["action_details"] = 35;
            
            Helper::log($request,'Delete');

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return [];
        }
    }

    public static function getOptionsAdminRoles()
    {
        try
        {
            $data = [];

            $db = DB::select("
                                SELECT id, type
                                FROM role
                                WHERE is_deleted = 0
                    ");
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return [];
        }
       
        foreach($db as $d)
        {
            $data[] = [$d->id, $d->type];
        }
        
        return $data;
    }

     public static function getRolesPermission(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $id = $request->input('id');

            $sql = "
                SELECT a.type, a.is_deleted, b.type'role_name'
                FROM role_permissions a
                LEFT JOIN role b ON a.role_id = b.id
                WHERE role_id = :id
                ";

            $params = [
                    'id' => $id
                ];

            $orderByAllow = ['type','is_deleted'];
            $orderByDefault = '';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return [];
        }
    }

    public static function editRolesPermission(Request $request)
    {
        try
        {
            $name = $request->input('name');
            $check = $request->input('check');
            $id = $request->input('id');

            $errMsg = [];

            //check exist type
            $db = DB::select("SELECT type
                                FROM role
                                WHERE type = ? AND is_deleted = 0"
                                ,[$name]
                            );

            if(sizeof($db) < 0)
            {
                array_push($errMsg, "Role Type not exist");
            }
            
            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $paramsPermission = [];

            foreach($check as $c)
            {
                $arr = explode('-',$c);

                array_push($paramsPermission,[$id,$arr[0],$arr[1]]);
            }

            $sql = "
                        INSERT INTO role_permissions
                        (role_id,type,is_deleted)
                        VALUES
                        :(?,?,?):
                        ON DUPLICATE KEY UPDATE 
                        is_deleted = VALUES(is_deleted)
                        ";

            $pdo = Helper::prepareBulkInsert($sql,$paramsPermission);

            DB::insert($pdo['sql'],$pdo['params']);

            unset($request["id"]);
            $request["action_details"] = 36;
            
            Helper::log($request,'Edit');

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return [];
        }
    }


}
