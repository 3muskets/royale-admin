<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;
use App\Events\MessageNotification;

use Auth;
use Log;

class MemberMessageController extends Controller
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
            $username = $request->input('member_name');

            $user = Auth::user();
            $level = $user->level;
            $adminId = $user->admin_id;

            if($adminId == 1)
                $adminId = '';

            $sql = "
                    SELECT sum(CASE WHEN b.is_read = 0 AND b.send_by = 'm' AND b.is_deleted IS NULL THEN 1 else 0 END) 
                    'unread_msg',a.id,a.username
                    FROM member a
                    LEFT JOIN member_msg b
                     ON a.id = b.member_id
                    WHERE a.username LIKE :username
                    AND (a.admin_id = :admin_id OR :admin_id1 = '')
                    GROUP BY a.id
                    ";

            $params = 
            [

                'username' => '%' . $username . '%'  
                ,'admin_id' => $adminId
                ,'admin_id1' =>$adminId 
            ];

            $orderByAllow = ['id','username','created_at'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page);

            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }

    public static function getDetail(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            $memberId = $request->input('id');

            $user = Auth::user();
            $id = $user->admin_id;
            $level = $user->level;

            if($level == 1)
            {
                self::updateUnreadMsg($memberId);
            }

            
            $sql = "
                    SELECT b.username,a.message,(a.created_at + INTERVAL 8 HOUR) 'created_at',a.send_by,a.subject,a.id
                    FROM member_msg a
                    LEFT JOIN member b
                      ON a.member_id = b.id 
                    Where a.member_id = :id AND a.is_deleted is NULL
                    ";

            $params = 
            [
                "id" => $memberId
            ];

            $orderByAllow = ['username','created_at'];
            $orderByDefault = 'created_at desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page,20);

            foreach($data['results'] as $d)
            {
                $d->send_by_desc = Helper::getOptionsValue(self::getOptionsSendBy(), $d->send_by);
            }

            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }

    public static function updateUnreadMsg($memberId)
    {
        try
        {
            $db = DB::update('UPDATE member_msg
                    SET is_read = ?
                    WHERE member_id = ? AND send_by = "m"
                    ',[1,$memberId]
                );
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
        }
    }

    public static function deleteMsg(Request $request)
    {
        DB::beginTransaction();
        
        try
        {
            $msgId = $request->input('msg_id');
            $memberId = $request->input('member_id');
            $userLevel = Auth::user()->level;
            $adminId = Auth::user()->admin_id;

            if($msgId == '' || $userLevel != 1)
            {
                $response = ['status' => 0
                            ,'error' =>  __('error.member.msg.nomsg')
                            ];

                return json_encode($response);   
            }

            $dbMember = DB::select('SELECT a.id FROM member a 
                                    LEFT JOIN tiers b 
                                        ON a.admin_id = b.admin_id 
                                    WHERE a.id = ? AND b.up2_tier = ?'
                                    ,[$memberId,$adminId]);

            if(sizeof($dbMember) == 0)
            {
                DB::rollback();

                $response = ['status' => 0
                            ,'error' => __('error.member.msg.invalidmember')
                            ];

                return json_encode($response);
            }

            foreach($msgId as $id)
            {
                $dbMsg = DB::select('SELECT id FROM member_msg WHERE id = ? AND member_id = ?',[$id,$memberId]);

                if(sizeof($dbMsg) == 0)
                {
                    DB::rollback();

                    $response = ['status' => 0
                                ,'error' => __('error.member.msg.invalidmsg')
                                ];

                    return json_encode($response);
                }

                DB::update('UPDATE member_msg
                        SET is_deleted = ?
                        WHERE id = ?
                        ',[1,$id]
                    );
            }

            DB::commit();

            $response = ['status' => 1];
            return json_encode($response);


        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            
            DB::rollback();

            $response = ['status' => 0
                        ,'error' => __('error.member.msg.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function updateMsg(Request $request)
    {
        DB::beginTransaction();

        try
        {
            $message = $request->input('message');
            $subject = $request->input('subject');
            $memberId = $request->input('id');
            $userLevel = Auth::user()->level;
            $adminId = Auth::user()->admin_id;

            if($memberId == null)
            {

                $response = ['status' => 0
                            ,'error' =>  __('error.member.msg.nomember')
                            ];

                return json_encode($response);   
            }

            if($subject == null)
            {
                $response = ['status' => 0
                            ,'error' =>  __('error.member.msg.insertsubject')
                            ];

                return json_encode($response);
            }

            if($userLevel != 1 || $message == null)
            {
                $response = ['status' => 0
                            ,'error' =>  __('error.member.msg.insertmsg')
                            ];

                return json_encode($response);
            }

            if(is_array($memberId) == 1)
            {
                // check on selected member
                foreach($memberId as $id)
                {
                    $dbMember = DB::select('SELECT a.id FROM member a 
                                    LEFT JOIN tiers b 
                                        ON a.admin_id = b.admin_id 
                                    WHERE a.id = ? AND b.up2_tier = ?',[$id,$adminId]);

                    if(sizeof($dbMember) == 0)
                    {
                        DB::rollback();

                        $response = ['status' => 0
                                    ,'error' => __('error.member.msg.invalidmember')
                                    ];

                        return json_encode($response);
                    }

                    $type = 'a';

                    $insert = DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                                    VALUES(?,0,?,?,?,NOW())',[$id,$type,$message,$subject]);

                    self::sendWs($id);

                }

            }
            else
            {
                // msg detail 
                $dbMember = DB::select('SELECT a.id FROM member a 
                                    LEFT JOIN tiers b 
                                        ON a.admin_id = b.admin_id 
                                    WHERE a.id = ? AND b.up2_tier = ?',[$memberId,$adminId]);

                // check all member
                if($memberId == 'a' && $message != '')
                {
                    $dbMember = DB::select('SELECT a.id FROM member a 
                                    LEFT JOIN tiers b 
                                        ON a.admin_id = b.admin_id 
                                    WHERE b.up2_tier = ?',[$adminId]);
                }

                if(sizeof($dbMember) == 0)
                {
                    DB::rollback();

                    $response = ['status' => 0
                                ,'error' => __('error.member.msg.invalidmember')
                                ];

                    return json_encode($response);
                }

                $type = 'a';

                foreach($dbMember as $d)
                {
                    $insert = DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                                VALUES(?,0,?,?,?,NOW())',[$d->id,$type,$message,$subject]);

                    self::sendWs($d->id);
                }

            }

            //no error
            DB::commit();

            $response = ['status' => 1];
            return json_encode($response);

        }
        catch(\Exception $e)
        {
            DB::rollback();

            $response = ['status' => 0
                        ,'error' => __('error.member.msg.internal_error')
                        ];

            return json_encode($response);
        }

    }

    public static function sendWs($userId)
    {
        try
        {
            //get member unread message 
            $db = DB::select('
                SELECT count(id) "count"
                FROM member_msg
                WHERE member_id = ? 
                 AND send_by = "a"
                 AND is_read =0
                ',[$userId]
            );

            //get member username 
            $db1 = DB::select('
                    SELECT username
                    FROM member
                    WHERE id = ?'
                    ,[$userId]
                );


            //send ws 
            event(new MessageNotification($db1[0]->username,$db[0]->count));

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
        }
    }

    public static function getOptionsSendBy()
    {

        return  [
            ['m', __('option.member.msg.member')]
            ,['a', __('option.member.msg.admin')]
        ];
    }


}

