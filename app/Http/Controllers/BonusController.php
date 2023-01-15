<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;


use Auth;
use Log;

class BonusController extends Controller
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

    public static function getPromoTypeList()
    {
 
        try
        {
            $data = [];

            $db = DB::select("
                    SELECT promo_id,promo_name
                    FROM promo_setting
                    GROUP BY promo_id
                    ORDER BY promo_id 
                    ASC
                    ");
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return [];
        }
       
        foreach($db as $d)
        {
            $data[] = [$d->promo_id, $d->promo_name];
        }
        
        return $data;


    }

    public static function getPromoList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');


            $sql = "
                SELECT promo_id,promo_name,is_casino,is_sportbook,is_slot,
                status,start_date,end_date,image,type,
                rate,turnover_multiple,created_at,detail,updated_at
                FROM promo_setting
                ";

            $params = [
                ];

            $orderByAllow = ['promo_id','promo_name'];
            $orderByDefault = 'promo_id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            $aryStatus = self::getOptionPromoStatus();

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


    public static function getBonusList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');
            $category = $request->input('category_id');

            if($category == null)
                $category = '';

            $sql = "
                SELECT level_id,category,turnover,rate,created_at,updated_at
                FROM bonus_setting
                WHERE (category = :category OR '' = :category1)
                ";

            $params = [
                    'category' => $category
                    ,'category1' => $category
                ];

            $orderByAllow = ['level_id','category'];
            $orderByDefault = 'level_id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            $aryStatus = self::getOptionCategory();

            foreach($data['results'] as $d)
            {
                $d->category_name = Helper::getOptionsValue($aryStatus, $d->category);
            }

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return [];
        }
    }

    public static function getReferralList(Request $request)
    {
        try
        {
            $data = [];

            $db = DB::select("
                    SELECT id,tier1_rate,tier2_rate,tier3_rate,created_at,updated_at
                    FROM referral_setting");

            $data['results'] = $db;
            $data['count'] = 1;
            $data['page_size'] = 1;


            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return [];
        }
    }


    public static function createPromo(Request $request)
    {
        DB::beginTransaction();

        try 
        {

            $rate = $request->input('rate');
            $turnoverMultiple = $request->input('turnover_multiple');
            $promoId = $request->input('promo_id');
            $promoName = $request->input('promo_name');
            $promoType = $request->input('promo_type');

            $startDate = $request->input('s_date1');
            $endDate = $request->input('e_date1');

            $isCasino = $request->input('is_casino');
            $isSportbook = $request->input('is_sportbook');
            $isSlot = $request->input('is_slot');
            $promoDetail = $request->input('promo_detail');

            $status = $request->input('status');

            //banner image
            $img = $request->image;

            $base64Image = null;

            $errMsg = [];

            $db = DB::select("
                    SELECT promo_id
                    FROM promo_setting
                    WHERE promo_name = ?
                    ",[$promoName]
                );


            if(sizeof($db) != 0)
            {
                array_push($errMsg, __('The Promotion Title Is Used'));
            }

            if($promoName == '')
            {
                array_push($errMsg, __('Promotion Title Cannot Be Null'));
            }

            if(!Helper::checkValidOptions(self::getOptionsPromoType(),$promoType))
            {
                array_push($errMsg, __('Invalid Promotion Type'));
            }

            if($startDate == '' || $endDate == '')
            {
                array_push($errMsg, __('Start Date and End Date Cannot Be Null'));
            }


            if($turnoverMultiple == '')
            {
                array_push($errMsg, __('Turnover Multiply Cannot Be Null'));
            }

            if($rate == '')
            {
                array_push($errMsg, __('Rate Cannot Be Null'));
            }

            //validation for category casino,sportbook,slot
            if(($isCasino != 0 && $isCasino != 1) || ($isSportbook != 0 && $isSportbook != 1) || ($isSlot != 0 && $isSlot != 1) )
            {
                array_push($errMsg, __('Invalid Category'));
            }
 

            if (!Helper::checkInputFormat('amount',$turnoverMultiple)) 
            {
                array_push($errMsg, __('Turnover Multiply Must be Number'));

            }

            if (!Helper::checkInputFormat('amount',$rate)) 
            {
                array_push($errMsg, __('Rate Must be Number'));

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
                INSERT INTO promo_setting(promo_name,type,is_casino,is_sportbook,is_slot,rate,turnover_multiple,detail,status,start_date,end_date,image)
                VALUES(:promoName,:type,:isCasino,:isSportbook,:isSlot,:rate,:turnoverMultiple,:detail,:status,:startDate,:endDate,:image)
                ";

            $params = [
                    'promoName' => strtoupper($promoName)
                    ,'type' => $promoType
                    ,'isCasino' => $isCasino
                    ,'isSportbook' => $isSportbook
                    ,'isSlot' => $isSlot
                    ,'rate' => $rate
                    ,'turnoverMultiple' => $turnoverMultiple
                    ,'detail' => $promoDetail
                    ,'status' => $status 
                    ,'startDate' => $startDate
                    ,'endDate' => $endDate
                    ,'image' => $base64Image
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


    public static function updatePromo(Request $request)
    {
        DB::beginTransaction();

        try 
        {
            $rate = $request->input('rate');
            $turnoverMultiple = $request->input('turnover_multiple');
            $promoId = $request->input('promo_id');
            $promoName = $request->input('promo_name');
            $promoType = $request->input('promo_type');

            $startDate = $request->input('edit_s_date1');
            $endDate = $request->input('edit_e_date1');

            $isCasino = $request->input('is_casino');
            $isSportbook = $request->input('is_sportbook');
            $isSlot = $request->input('is_slot');
            $promoDetail = $request->input('promo_detail');

            $promoDetail = str_replace(',', '<br>', $promoDetail);

            $status = $request->input('status');

            //banner image
            $img = $request->image;

            $base64Image = null;

            $errMsg = [];


            $db = DB::select("
                    SELECT promo_id
                    FROM promo_setting
                    WHERE promo_id != ?
                    AND promo_name = ?
                    ",[$promoId,$promoName]
                );

            if(sizeof($db) != 0)
            {
                array_push($errMsg, __('The Promotion Title Is Used'));
            }

            if($promoName == '')
            {
                array_push($errMsg, __('Promotion Title Cannot Be Null'));
            }

            if(!Helper::checkValidOptions(self::getOptionsPromoType(),$promoType))
            {
                array_push($errMsg, __('Invalid Promotion Type'));
            }

            if($startDate == '' || $endDate == '')
            {
                array_push($errMsg, __('Start Date and End Date Cannot Be Null'));
            }


            if($turnoverMultiple == '')
            {
                array_push($errMsg, __('Turnover Multiply Cannot Be Null'));
            }

            if($rate == '')
            {
                array_push($errMsg, __('Rate Cannot Be Null'));
            }

            //validation for category casino,sportbook,slot
            if(($isCasino != 0 && $isCasino != 1) || ($isSportbook != 0 && $isSportbook != 1) || ($isSlot != 0 && $isSlot != 1) )
            {
                array_push($errMsg, __('Invalid Category'));
            }
 

            if (!Helper::checkInputFormat('amount',$turnoverMultiple)) 
            {
                array_push($errMsg, __('Turnover Multiply Must be Number'));

            }

            if (!Helper::checkInputFormat('amount',$rate)) 
            {
                array_push($errMsg, __('Rate Must be Number'));

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
                        FROM promo_setting
                        WHERE promo_id = ?
                        ",[$promoId]
                    );

                if(sizeof($db) != 0)
                {
                    $base64Image = $db[0]->image;
                }
            }


            DB::update('
                    UPDATE promo_setting
                    SET promo_name=?,type=?
                    ,turnover_multiple =?,rate =?
                    ,is_casino =?,is_sportbook =?,is_slot =?,detail=?
                    ,status=?,start_date=?,end_date=?,image=?
                    ,updated_at = ?
                    WHERE promo_id = ? 
                    '
                    ,[
                        $promoName
                        ,$promoType
                        ,$turnoverMultiple
                        ,$rate
                        ,$isCasino
                        ,$isSportbook
                        ,$isSlot
                        ,$promoDetail
                        ,$status
                        ,$startDate
                        ,$endDate
                        ,$base64Image
                        ,NOW()
                        ,$promoId
                    ]);
          


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

    public static function updateBonus(Request $request)
    {
        DB::beginTransaction();

        
        try 
        {
            $rate = $request->input('rate');
            $turnover = $request->input('turnover');
            $category = $request->input('category');
            $level = $request->input('level');

            $upperTurnover = ''; 
            $lowerTurnover = '';

            $errMsg = [];

            $db = DB::select("
                SELECT b.turnover 'upline_turnover',c.turnover 'downline_turnover'
                FROM bonus_setting a
                LEFT JOIN bonus_setting b
                ON a.level_id+1 = b.level_id
                AND a.category = b.category
                LEFT JOIN bonus_setting c
                ON a.level_id-1 = c.level_id 
                AND a.category = c.category
                WHERE a.level_id = ? 
                AND a.category = ?
                ",[$level,$category]
            );

            if(sizeof($db) == 0)
            {
                array_push($errMsg, __('Invalid Rate'));
            }
            else
            {
                $uplineTurnover = $db[0]->upline_turnover;
                $donwLineTurnover = $db[0]->downline_turnover;
            }


            if($turnover == '')
            {
                array_push($errMsg, __('Turnover Cannot Be Null'));
            }

            if($rate == '')
            {
                array_push($errMsg, __('Rate Cannot Be Null'));
            }


            if(($level != 5) && $uplineTurnover <= $turnover)
            {
                array_push($errMsg, __('Your Turnover is more than or equal your Upper Level turnover'));
            }

            if(($level != 1) && $donwLineTurnover >= $turnover)
            {
                array_push($errMsg, __('Your Turnover is less than or equal your Lower Level Turnover'));
            }


            //validation for provider type
            if(!Helper::checkValidOptions(self::getOptionCategory(),$category))
            {
                array_push($errMsg, __('error.merchant.invalid_status'));
            }


            if (!Helper::checkInputFormat('alphanumericWithDot',$turnover)) 
            {
                array_push($errMsg, __('Turnover Must be Numeric'));

            }

            if (!Helper::checkInputFormat('alphanumericWithDot',$rate)) 
            {
                array_push($errMsg, __('Rate Must be Numeric'));

            }



            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            $update = DB::update('
                    UPDATE bonus_setting
                    SET turnover =?,rate =?,updated_at = ?
                    WHERE level_id = ? 
                    AND category = ?'
                    ,[
                        $turnover
                        ,$rate
                        ,Now()
                        ,$level
                        ,$category
                    ]);
          

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


    public static function updateReferral(Request $request)
    {
        DB::beginTransaction();
        
        try 
        {
            $tier1Rate = $request->input('tier1_rate');
            $tier2Rate = $request->input('tier2_rate');
            $tier3Rate = $request->input('tier3_rate');

            $errMsg = [];

            if($tier1Rate == '' || $tier2Rate == '' || $tier3Rate == '')
            {
                array_push($errMsg, __('Rate Cannot Be Null'));
            }


            if (!Helper::checkInputFormat('amount',$tier1Rate)) 
            {
                array_push($errMsg, __('Tier 1 Rate Must be Number'));

            }

            if (!Helper::checkInputFormat('amount',$tier2Rate)) 
            {
                array_push($errMsg, __('Tier 2 Rate Must be Number'));

            }

            if (!Helper::checkInputFormat('amount',$tier3Rate)) 
            {
                array_push($errMsg, __('Tier 3 Rate Must be Number'));

            }



            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            DB::update("
                    UPDATE referral_setting
                    SET tier1_rate =?,tier2_rate =?,tier3_rate = ?
                    ,updated_at =? 
                    "
                    ,[
                        $tier1Rate
                        ,$tier2Rate
                        ,$tier3Rate
                        ,Now()
                    ]);
          


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

    public static function getList(Request $request)
    {
        try
        {

            $data = DB::select("
                    SELECT status,mem_value
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


            $rebateValue = $request->input('mem-value');


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


/*            if (!Helper::checkInputFormat('numeric',$maxRebateAmt)) 
            {
                array_push($errMsg, __('Max Amount must in numeric or greater than zero'));

            }
            if(!Helper::validAmount($maxRebateAmt))
            {

                array_push($errMsg, __('Max Amount cannot exceed 15 digits'));


            }*/
        
            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            DB::update('UPDATE rebate_setting 
                            SET status =?,mem_value =?
                            WHERE id=1', 
                            [$status,$rebateValue
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


    public static function checkPromo($userId,$promoId)
    {
        try
        {

            $todayDate = NOW();
            $todayStartDate = date('Y-m-d 00:00:00',strtotime($todayDate. '+8 hours'));
            $todayEndDate = date('Y-m-d 23:59:59',strtotime($todayDate. '+8 hours'));
            $prevWeekDate = date('Y-m-d 00:00:00',strtotime($todayDate. '-7days +8 hours'));
            $prevMonthDate = date('Y-m-d 00:00:00',strtotime($todayDate. '-1months +8 hours'));

            //checking have pending promotion or not
            if($promoId != '')
            {
                $db = DB::select("
                    SELECT id
                    FROM member_promo_turnover
                    WHERE member_id = ? AND
                    status = 'p'
                    ",[$userId]
                );

                if(sizeof($db) != 0)
                {

                    $response = ['status' => 0
                                ,'error' => __('Pending Promotion') 
                                ];

                    return $response;

                }                
            }


            //get promotion type
            $db = DB::select("
                    SELECT type
                    FROM promo_setting
                    WHERE promo_id = ? AND status = 'a'
                    AND start_date <= ?
                    AND end_date >= ?
                    ",[$promoId
                      ,$todayStartDate
                      ,$todayStartDate
                    ]);

            if(sizeof($db) == 0)
            {

                $response = ['status' => 0
                            ,'error' => __('Invalid Promotion') 
                            ];

                return $response;
            }

            $type = $db[0]->type;

            if($type == 'd')
                $startDate = $todayStartDate;
            else if($type == 'w')
                $startDate = $prevWeekDate;
            else if($type == 'm')
                $startDate = $prevMonthDate;

            if($type == 'f')
            {
                //if type for first time 
                $db = DB::select("
                    SELECT id
                    FROM member_dw
                    WHERE member_id = ?
                    AND promo_id = ?
                    AND status = 'a'
                    ",[$userId,$promoId]);

                if(sizeof($db) == 0)
                {
                    $response = ['status' => 1
                                ,'error' => __('Success') 
                                ];   

                    return $response;
                }
            }
            else if($type == 'd' || $type == 'w' || $type == 'm')
            {

                if($type == 'd')
                {
                    $response = ['status' => 1
                                ,'error' => __('Success') 
                                ];   

                    return $response;
                }

                $db = DB::select("
                    SELECT id
                    FROM member_dw
                    WHERE member_id= ? AND promo_id = ?
                    AND status = 'a'
                    AND (created_at + INTERVAL 8 HOUR) >= ?
                    AND (created_at + INTERVAL 8 HOUR) <= ?
                    ",[$userId
                       ,$promoId
                       ,$startDate
                       ,$todayEndDate
                    ]); 


                if(sizeof($db) == 0)
                {
                    $response = ['status' => 1
                                ,'error' => __('Success') 
                                ];   

                    return $response;
                }               
            }


            $response = ['status' => 0
                        ,'error' => __('Invalid Promotion') 
                        ];

            return $response;
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            
            $response = ['status' => 0
                        ,'error' => __('Invalid Promotion') 
                        ];  

            return $response;
        }
    }


    public static function getPromoDetail($promoId)
    {
        try
        {
            $rate = 0;
            $turnoverMultiple = 0;

            $db = DB::select("
                    SELECT rate,turnover_multiple
                    FROM promo_setting
                    WHERE promo_id = ?
                    ",[$promoId]
                );

            if(sizeof($db) != 0)
            {
                $rate = $db[0]->rate;
                $turnoverMultiple = $db[0]->turnover_multiple;
                
            }
            
            $response = ['rate' => $rate
                        ,'turnover_multiple' => $turnoverMultiple
                        ];    


            return $response;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            $response = ['rate' => 0
                        ,'turnover_multiple' => 0
                        ];
                        
            return $response;
                
        }

    }


    public static function getOptionCategory()
    {
        return  [
            ['1', __('Live Casino')]
            ,['2', __('Sportbook')]
            ,['3', __('Slot')]
        ];        
    }


    public static function getOptionPromoStatus()
    {
        return  [
            ['a', __('Active')]
            ,['i', __('Inactive')]
        ];
    }



    public static function getOptionsPromoType()
    {
        return  [
             ['f', __('First Time')]
            ,['d', __('Daily')]
            ,['w', __('Weekly')]
            ,['m', __('Monthly')]
        ];
    }

}

