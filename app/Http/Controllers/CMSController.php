<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;


use Auth;
use Log;

class CMSController extends Controller
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
    public static function getMainBannerList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            $status = $request->input('status');


            if($status == null)
                $status = '';


            $sql = "
                SELECT id,sequence,status,start_date,end_date,image,created_at,updated_at
                FROM cms
                WHERE (status = :status OR :status1 = '')
                AND type = 1

                ";

            $params = [
                    'status' => $status
                    ,'status1' => $status

                ];

            $orderByAllow = ['id','sequence','status','start_date','end_date','created_at','updated_at'];
            $orderByDefault = 'sequence desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            $aryStatus = self::getOptionStatus();


            foreach($data['results'] as $d)
            {
                $d->status_desc = Helper::getOptionsValue($aryStatus, $d->status);
            }


            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return [];
        }
    }

    public static function getPopUpDetail(Request $request)
    {
        try
        {

            $db = DB::select("
                SELECT status,image
                FROM cms
                WHERE type = 2");

            return Response::make(json_encode($db), 200);
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return [];
        }
    }
    public static function getAnnouncementList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            $status = $request->input('status');


            if($status == null)
                $status = '';


            $sql = "
                SELECT id,sequence,status,start_date,end_date,text,created_at,updated_at
                FROM cms
                WHERE (status = :status OR :status1 = '')
                AND type = 3

                ";

            $params = [
                    'status' => $status
                    ,'status1' => $status

                ];

            $orderByAllow = ['id','sequence','status','start_date','end_date','created_at','updated_at'];
            $orderByDefault = 'sequence desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            $aryStatus = self::getOptionStatus();


            foreach($data['results'] as $d)
            {
                $d->status_desc = Helper::getOptionsValue($aryStatus, $d->status);
            }


            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return [];
        }
    }


    public static function createBanner(Request $request)
    {

        DB::beginTransaction();

        try 
        {
            
            $sequence = $request->input('sequence');
            $startDate = $request->input('s_date1');
            $endDate = $request->input('e_date1');
            $status = $request->input('status');
           
            //banner image
            $img = $request->image;

            $base64Image = null;

            $errMsg = [];

            $db = DB::select("
                    SELECT id
                    FROM cms
                    WHERE sequence = ?
                    AND type = 1
                    ",[$sequence]
                );


            if(sizeof($db) != 0 || $sequence == '')
            {
                array_push($errMsg, __('Invalid Sequence'));
            }


            if($startDate == '' || $endDate == '')
            {
                array_push($errMsg, __('Start Date and End Date Cannot Be Null'));
            }

            if($img == null)
            {
                array_push($errMsg, __('Invalid Image'));
            }


          
            if($img != null)
            {
                $type2 = pathinfo($img, PATHINFO_EXTENSION);
                $data = file_get_contents($img);
                $base64Image = 'data:image/' . $type2 . ';base64,' . base64_encode($data);
                unlink($img);
            }


            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            $sql = "
                INSERT INTO cms(sequence,type,status,start_date,end_date,image,created_at)
                VALUES(:sequence,:type,:status,:startDate,:endDate,:image,:createdAt)
                ";

            $params = [
                    'sequence' => $sequence
                    ,'type' => 1
                    ,'status' => $status 
                    ,'startDate' => $startDate
                    ,'endDate' => $endDate
                    ,'image' => $base64Image
                    ,'createdAt' => NOW()
                ];

            $data = DB::insert($sql,$params);
          

            //logging                
            $response = ['status' => 1];


            DB::commit();

            return json_encode($response);

        } 
        catch (\Exception $e) 
        {
            DB::rollback();
            log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.merchant.internal_error')
                        ];
            return json_encode($response);
        }

    }

    public static function createAnnouncement(Request $request)
    {

        DB::beginTransaction();

        try 
        {
            
            $sequence = $request->input('sequence');
            $startDate = $request->input('s_date1');
            $endDate = $request->input('e_date1');
            $status = $request->input('status');

            $text = $request->input('ann_text');


            $errMsg = [];

            $db = DB::select("
                    SELECT id
                    FROM cms
                    WHERE sequence = ?
                    AND type = 3
                    ",[$sequence]
                );


            if(sizeof($db) != 0 || $sequence == '')
            {
                array_push($errMsg, __('Invalid Sequence'));
            }


            if($startDate == '' || $endDate == '')
            {
                array_push($errMsg, __('Start Date and End Date Cannot Be Null'));
            }

            if($text == '')
            {
                array_push($errMsg, __('No Content'));
            }



            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            $sql = "
                INSERT INTO cms(sequence,type,status,start_date,end_date,text,created_at)
                VALUES(:sequence,:type,:status,:startDate,:endDate,:text,:createdAt)
                ";

            $params = [
                    'sequence' => $sequence
                    ,'type' => 3
                    ,'status' => $status 
                    ,'startDate' => $startDate
                    ,'endDate' => $endDate
                    ,'text' => $text
                    ,'createdAt' => NOW()
                ];

            $data = DB::insert($sql,$params);
          

            //logging                
            $response = ['status' => 1];


            DB::commit();

            return json_encode($response);

        } 
        catch (\Exception $e) 
        {
            DB::rollback();
            log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.merchant.internal_error')
                        ];
            return json_encode($response);
        }

    }


    public static function updateBanner(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            $bannerId = $request->input('banner_id');

            $sequence = $request->input('sequence');
            $startDate = $request->input('edit_s_date1');
            $endDate = $request->input('edit_e_date1');


            $status = $request->input('status');

            //banner image
            $img = $request->image;

            $base64Image = null;

            $errMsg = [];


            $db = DB::select("
                    SELECT id
                    FROM cms
                    WHERE id = ?
                    AND type = 1
                    ",[$bannerId]
                );

            if(sizeof($db) == 0)
            {
                array_push($errMsg, __('Invalid Banner'));
            }

            $db = DB::select("
                    SELECT id
                    FROM cms
                    WHERE id != ?
                    AND sequence = ?
                    AND type = 1
                    ",[$bannerId,$sequence]
                );

            if(sizeof($db) != 0)
            {
                array_push($errMsg, __('Invalid Sequence'));
            }

            if($startDate == '' || $endDate == '')
            {
                array_push($errMsg, __('Start Date and End Date Cannot Be Null'));
            }

            if(!Helper::checkValidOptions(self::getOptionStatus(),$status))
            {
                array_push($errMsg, __('Invalid Status'));
            }


            if($img != null)
            {
                $type2 = pathinfo($img, PATHINFO_EXTENSION);
                $data = file_get_contents($img);
                $base64Image = 'data:image/' . $type2 . ';base64,' . base64_encode($data);
                unlink($img);
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            if($base64Image == null)
            {
                $db = DB::select("
                        SELECT image
                        FROM cms
                        WHERE id = ?
                        ",[$bannerId]
                    );

                if(sizeof($db) != 0)
                {
                    $base64Image = $db[0]->image;
                }
            }


            DB::update('
                    UPDATE cms
                    SET sequence=?,status=?
                    ,start_date=?,end_date=?,image=?
                    ,updated_at = ?
                    WHERE id = ? 
                    AND type = 1
                    '
                    ,[
                        $sequence
                        ,$status
                        ,$startDate
                        ,$endDate
                        ,$base64Image
                        ,NOW()
                        ,$bannerId
                    ]);
          


            //logging                
            $response = ['status' => 1];


            DB::commit();

            return json_encode($response);

        } 
        catch(\Exception $e)
        {
            DB::rollback();
            log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.merchant.internal_error')
                        ];
            return json_encode($response);

        }
    }


    public static function updateAnnouncement(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            $id = $request->input('announcement_id');

            $sequence = $request->input('sequence');
            $startDate = $request->input('edit_s_date1');
            $endDate = $request->input('edit_e_date1');


            $status = $request->input('status');

            $text = $request->input('edit_ann_text');

            log::Debug($request);

            $errMsg = [];


            $db = DB::select("
                    SELECT id
                    FROM cms
                    WHERE id = ?
                    AND type = 3
                    ",[$id]
                );

            if(sizeof($db) == 0)
            {
                array_push($errMsg, __('Invalid Announcement'));
            }

            $db = DB::select("
                    SELECT id
                    FROM cms
                    WHERE id != ?
                    AND sequence = ?
                    AND type = 3
                    ",[$id,$sequence]
                );

            if(sizeof($db) != 0)
            {
                array_push($errMsg, __('Invalid Sequence'));
            }

            if($startDate == '' || $endDate == '')
            {
                array_push($errMsg, __('Start Date and End Date Cannot Be Null'));
            }

            if(!Helper::checkValidOptions(self::getOptionStatus(),$status))
            {
                array_push($errMsg, __('Invalid Status'));
            }



            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            if($text == null)
            {
                $db = DB::select("
                        SELECT text
                        FROM cms
                        WHERE id = ?
                        ",[$id]
                    );

                if(sizeof($db) != 0)
                {
                    $text = $db[0]->text;
                }
            }

            DB::update('
                    UPDATE cms
                    SET sequence=?,status=?
                    ,start_date=?,end_date=?,text=?
                    ,updated_at = ?
                    WHERE id = ? 
                    AND type = 3
                    '
                    ,[
                        $sequence
                        ,$status
                        ,$startDate
                        ,$endDate
                        ,$text
                        ,NOW()
                        ,$id
                    ]);
          


            //logging                
            $response = ['status' => 1];


            DB::commit();

            return json_encode($response);

        } 
        catch(\Exception $e)
        {
            DB::rollback();
            log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.merchant.internal_error')
                        ];
            return json_encode($response);

        }
    }


    public static function updatePopup(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            $type = 2;

            $status = $request->input('status');

            //popup image
            $img = $request->image;

            $base64Image = null;

            $errMsg = [];


            if(!Helper::checkValidOptions(self::getOptionStatus(),$status))
            {
                array_push($errMsg, __('Invalid Status'));
            }


            if($img != null)
            {
                $type2 = pathinfo($img, PATHINFO_EXTENSION);
                $data = file_get_contents($img);
                $base64Image = 'data:image/' . $type2 . ';base64,' . base64_encode($data);
                unlink($img);
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }
            

            if($base64Image == null)
            
            {
                $db = DB::select("
                        SELECT image
                        FROM cms
                        WHERE type = 2"
                    );

                if(sizeof($db) != 0)
                {
                    $base64Image = $db[0]->image;
                }
            }


            $db = DB::select("
                SELECT status
                FROM cms
                WHERE type = 2");

            if(sizeof($db) == 0)
            {

                DB::insert("
                        INSERT INTO cms
                        (type,image,status,created_at)
                        VALUES
                        (?,?,?,NOW())
                        "
                        ,[$type
                        ,$base64Image
                        ,$status
                        ]);                   
            }
            else
            {
                DB::update("
                    UPDATE cms
                    SET status=?,image=?,updated_at=?
                    WHERE type = 2
                    ",[$status,$base64Image,NOW()]
                );
            }

      
            //logging                
            $response = ['status' => 1];


            DB::commit();

            return json_encode($response);

        } 
        catch(\Exception $e)
        {
            DB::rollback();
            log::debug($e);
            $response = ['status' => 0
                        ,'error' => __('error.merchant.internal_error')
                        ];
            return json_encode($response);

        }
    }


    public static function getOptionStatus()
    {
        return  [
            ['a', __('Active')]
            ,['i', __('Inactive')]
        ];
    }



}

