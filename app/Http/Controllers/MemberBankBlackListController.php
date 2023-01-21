<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BonusController;

use Auth;
use Log;

class MemberBankBlackListController extends Controller
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
            $user = Auth::user();
            $userLevel = $user->level;
            $userId = $user->admin_id;

            $memberId = $request->input('member_id');

            $sql = "
                SELECT id,acc_no
                FROM member_blacklist_bank 
                WHERE is_deleted IS NULL
                ";

            $params = [
                ];

            $orderByAllow = ['username','created_at'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page,200);

            $aryStatus = self::getOptionsStatus();

            foreach($data['results'] as $d)
            {

            }

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return [];
        }
    }

    public static function add(Request $request)
    {
        DB::beginTransaction();

        try
        {

            $accNo = $request->input('acc_no');


            $errMsg = [];  

            if(!ctype_digit($accNo))
            {
                array_push($errMsg, __('error.bank.info.accno.numeric'));
            }


            $db = DB::select("
                    SELECT id
                    FROM member_blacklist_bank
                    WHERE acc_no = ?
                    AND is_deleted IS NULL"
                    ,[$accNo]);


            if(sizeof($db) > 0)
            {
                array_push($errMsg, __('Bank Account Already Exist'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            // insert users details
            $sql = DB::insert("
                INSERT INTO member_blacklist_bank(acc_no,created_at)
                VALUES(?,NOW())
                "
                ,[ $accNo
                ]);
           
            $response = ['status' => 1];
            DB::commit();
            return json_encode($response);
        

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            
            DB::rollback();

            $response = ['status' => 0
                        ,'error' => __('INTERNAL_ERROR')
                        ];

            return json_encode($response);
        }
    }


    public static function delete(Request $request)
    {
        DB::beginTransaction();

        try
        {

            $id = $request->input('id');


            $errMsg = [];  


            $db = DB::select("
                    SELECT id
                    FROM member_blacklist_bank
                    WHERE id = ?
                    AND is_deleted IS NULL"
                    ,[$id]);


            if(sizeof($db) == 0)
            {
                array_push($errMsg, __('Invalid Accont'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            $db = DB::update("
                UPDATE member_blacklist_bank
                SET is_deleted = 1
                WHERE id = ?
                ",[$id]);
           
            $response = ['status' => 1];
            DB::commit();
            return json_encode($response);
        

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            
            DB::rollback();

            $response = ['status' => 0
                        ,'error' => __('INTERNAL_ERROR')
                        ];

            return json_encode($response);
        }
    }


    public static function getOptionsStatus()
    {
        return  [
                ['a', __('option.admin.active')]
                ,['i', __('option.admin.inactive')]
            ];
    }


}
