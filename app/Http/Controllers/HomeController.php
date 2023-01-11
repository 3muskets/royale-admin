<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Log;

class HomeController extends Controller
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

    //TODO- getProfitProduct
    //TODO - getTotalBetDetail BY PRODUCT
    public static function display(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $prdId = $request->input('prd_id');
        $userLevel = Auth::user()->level;


        if($startDate == null)
            $startDate = '';
        else
            $startDate = date('Y-m-d H:i:s',strtotime($startDate.'-8 hours'));

        if($endDate == null)
            $endDate = '';
        else
            $endDate = date('Y-m-d H:i:s',strtotime($endDate.'23:59:59'.'-8 hours'));

        $registerMember = self::getRegisterMember($startDate,$endDate);

        $totalMember = self::getTotalMember();

/*        $totalDeposit = self::getTotalDeposit($startDate,$endDate);
        $totalWithdraw = self::getTotalWithdraw($startDate,$endDate);
        $totalCryptoDeposit = self::getTotalCryptoDeposit($startDate,$endDate);
        $totalCryptoWithdraw = self::getTotalCryptoWithdraw($startDate,$endDate);
        $totalBetDetail = self::getTotalBetDetail($startDate,$endDate,$prdId);
        $topFiveMember = self::getTopFiveMember($startDate,$endDate);
        $topFiveAgent = self::getTopFiveAgent($startDate,$endDate);
        $profitProduct = self::getProfitProduct($startDate,$endDate);

        $totalAdjustment = self::getTotalAdjustment($startDate,$endDate);

        $totalAdjustmentAdd = $totalAdjustment[0];
        $totalAdjustmentDeduct = $totalAdjustment[1];

        $totalTurnover = $totalBetDetail[0]->turnover;
        $totalWinLoss = $totalBetDetail[0]->win_loss;

        if($userLevel == 0)
            $totalPtAmt = $totalBetDetail[0]->tier1_pt_amt;
        else if($userLevel == 1)
            $totalPtAmt = $totalBetDetail[0]->tier2_pt_amt;
        else if($userLevel == 2)
            $totalPtAmt = $totalBetDetail[0]->tier3_pt_amt;
        else if($userLevel == 3)
            $totalPtAmt = $totalBetDetail[0]->tier4_pt_amt;*/


        return ['totalMember'=>$totalMember
                ,'registerMember'=>$registerMember
/*                ,'totalAdjustmentAdd' =>$totalAdjustmentAdd
                ,'totalAdjustmentDeduct'=>$totalAdjustmentDeduct
                ,'totalDeposit'=>$totalDeposit
                ,'totalWithdraw'=>$totalWithdraw
                ,'totalCryptoDeposit'=>$totalCryptoDeposit
                ,'totalCryptoWithdraw'=>$totalCryptoWithdraw
                ,'totalTurnover'=>$totalTurnover
                ,'totalWinLoss'=>$totalWinLoss
                ,'totalPtAmt'=>$totalPtAmt
                ,'topFiveMember'=>$topFiveMember
                ,'topFiveAgent'=>$topFiveAgent
                ,'profitProduct'=>$profitProduct*/
            ];
    }

    public static function getTotalMember()
    {
        try 
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adminId = $user->admin_id;

            if($adminId == 1)
                $adminId = '';

            $db = DB::select("
                    SELECT COUNT(a.id) 'member' 
                    FROM member a
                    WHERE (a.admin_id = ? OR '' = ?) 
                    ",[$adminId,$adminId]
                );

         
            if(sizeof($db) == 0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->member;
            }                

        } 
        catch (Exception $e) 
        {
            return 0;
        }
    }




    public static function getRegisterMember($startDate, $endDate)
    {
        try 
        {
            $user = Auth::user();
            $userLevel = $user->level;
            $adminId = $user->admin_id;

            if($adminId == 1)
                $adminId = '';

            $sql = " 
                    SELECT COUNT(a.id) 'member' 
                    FROM member a 
                    WHERE (a.created_at >= :start_date OR :start_date1 = '')
                    AND (a.created_at <= :end_date OR :end_date1 = '')
                    AND (a.admin_id = :admin_id OR '' = :admin_id1)
                    ";

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;
            $params['admin_id'] = $adminId;
            $params['admin_id1'] = $adminId;

            $db = DB::select($sql,$params);

            if(sizeof($db) == 0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->member;
            }                
        } 
        catch (Exception $e) 
        {
            return 0;
        }
    }

    public static function getTotalDeposit($startDate,$endDate)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = Helper::getDownlineArrList();

            $sql = " 
                    SELECT SUM(a.amount) 'amount'
                          FROM member_credit_txn a
                          LEFT JOIN member b
                           ON a.member_id = b.id
                    WHERE a.type IN ('1', '2')
                    AND b.admin_id IN (?)
                    AND (a.created_at >= :start_date OR :start_date1 = '')
                    AND (a.created_at <= :end_date OR :end_date1 = '')
                    ";
            $params = [$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;


            $db = DB::select($sql,$params);


            if(sizeof($db)==0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->amount;
            }    
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return '';
        }
    }

    public static function getTotalWithdraw($startDate,$endDate)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = Helper::getDownlineArrList();

            $sql = " 
                    SELECT SUM(a.amount) 'amount'
                          FROM member_credit_txn a
                          LEFT JOIN member b
                           ON a.member_id = b.id
                    WHERE a.type = '3'
                    AND b.admin_id IN (?)
                    AND (a.created_at >= :start_date OR :start_date1 = '')
                    AND (a.created_at <= :end_date OR :end_date1 = '')
                    ";
            
            $params = [$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;


            $db = DB::select($sql,$params);

            if(sizeof($db)==0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->amount;
            }    
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return '';
        }
    }

    public static function getTotalCryptoDeposit($startDate,$endDate)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = Helper::getDownlineArrList();

            $sql = " 
                    SELECT SUM(a.amount) 'amount'
                          FROM member_dw a
                          LEFT JOIN member b
                           ON a.member_id = b.id
                    WHERE a.payment_type = 'x' AND a.type = 'd' AND a.status = 'a'
                    AND b.admin_id IN (?)
                    AND (a.created_at >= :start_date OR :start_date1 = '')
                    AND (a.created_at <= :end_date OR :end_date1 = '')
                    ";
            
            $params = [$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;


            $db = DB::select($sql,$params);


            if(sizeof($db)==0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->amount;
            }    
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return '';
        }
    }

    public static function getTotalCryptoWithdraw($startDate,$endDate)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = Helper::getDownlineArrList();

            $sql = " 
                    SELECT SUM(a.amount) 'amount'
                          FROM member_dw a
                          LEFT JOIN member b
                           ON a.member_id = b.id
                    WHERE a.payment_type = 'x' AND a.type = 'w' AND a.status = 'a'
                    AND b.admin_id IN (?)
                    AND (a.created_at >= :start_date OR :start_date1 = '')
                    AND (a.created_at <= :end_date OR :end_date1 = '')
                    ";
            
            $params = [$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;



            $db = DB::select($sql,$params);


            if(sizeof($db)==0)
            {
                return 0;
            }  
            else
            {
                return -$db[0]->amount;
            }    
        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return '';
        }
    }

    public static function getTotalAdjustment($startDate,$endDate)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adjAdd = 0;
            $adjDeduct = 0;

            $adminArr = Helper::getDownlineArrList();

            $sql = " 
                    SELECT SUM(a.amount) 'amount',type
                        FROM member_credit_txn a
                        LEFT JOIN member b
                        ON a.member_id = b.id
                    WHERE a.is_adjustment = 1
                    AND b.admin_id IN (?)
                    AND (a.created_at >= :start_date OR :start_date1 = '')
                    AND (a.created_at <= :end_date OR :end_date1 = '')
                    ";
            
            $params = [$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;


            $db = DB::select($sql,$params);


            if(sizeof($db)!= 0)
            {
                foreach($db as $d)
                {
                    if($d->type == 2)
                        $adjAdd = $d->amount;
                    else if($d->type == 3)
                        $adjDeduct = -$d->amount;
                }

            }  
            
            return [$adjAdd,$adjDeduct];

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return [0,0];
        }        
    }

    public static function getTotalBetDetail($startDate,$endDate,$prdId)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = Helper::getDownlineArrList();

            $sql = " 
                    SELECT                         
                        SUM(a.turnover) 'turnover'
                        , SUM(a.win_loss) 'win_loss'
                        , SUM(a.tier1_pt_amt) 'tier1_pt_amt'
                        , SUM(a.tier2_pt_amt) 'tier2_pt_amt'
                        , SUM(a.tier3_pt_amt) 'tier3_pt_amt'
                        , SUM(a.tier4_pt_amt) 'tier4_pt_amt'
                    FROM
                    (
                        SELECT SUM(a.amount) 'turnover',
                            SUM(b.amount - a.amount) 'win_loss', 
                            SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                            SUM(b.tier2_pt_amt)'tier2_pt_amt',
                            SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                            SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                            SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM aas_debit a
                            INNER JOIN aas_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date OR :start_date1 = '')
                        AND (a.created_at <= :end_date OR :end_date1 = '')

                        UNION ALL 

                        SELECT SUM(a.amount) 'turnover',
                            SUM(b.amount - a.amount) 'win_loss', 
                            SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                            SUM(b.tier2_pt_amt)'tier2_pt_amt',
                            SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                            SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                            SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM fg_debit a
                            INNER JOIN fg_credit b ON a.txn_id = b.txn_id 
                            INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date2 OR :start_date3 = '')
                        AND (a.created_at <= :end_date2 OR :end_date3 = '')


                        UNION ALL 

                        SELECT SUM(a.bet) 'turnover',
                            SUM(b.amount - a.bet) 'win_loss', 
                            SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                            SUM(b.tier2_pt_amt)'tier2_pt_amt',
                            SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                            SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                            SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM gs_debit a
                            INNER JOIN gs_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date4 OR :start_date5 = '')
                        AND (a.created_at <= :end_date4 OR :end_date5 = '')


                        UNION ALL 

                        SELECT SUM(a.bet) 'turnover',
                            SUM(b.amount - a.bet) 'win_loss', 
                            SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                            SUM(b.tier2_pt_amt)'tier2_pt_amt',
                            SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                            SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                            SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM cp_debit a
                            INNER JOIN cp_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date6 OR :start_date7 = '')
                        AND (a.created_at <= :end_date6 OR :end_date7 = '')


                        UNION ALL 

                        SELECT SUM(a.bet) 'turnover',
                            SUM(b.amount - a.bet) 'win_loss', 
                            SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                            SUM(b.tier2_pt_amt)'tier2_pt_amt',
                            SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                            SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                            SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM noe_debit a
                            INNER JOIN noe_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date8 OR :start_date9 = '')
                        AND (a.created_at <= :end_date8 OR :end_date9 = '')


                        UNION ALL 

                        SELECT SUM(a.bet) 'turnover',
                            SUM(b.amount - a.bet) 'win_loss', 
                            SUM(b.tier1_pt_amt) 'tier1_pt_amt',
                            SUM(b.tier2_pt_amt)'tier2_pt_amt',
                            SUM(b.tier3_pt_amt) 'tier3_pt_amt',
                            SUM(b.tier4_pt_amt) 'tier4_pt_amt',
                            SUM(b.tier4_comm_amt) as tier4_comm_amt
                            FROM pussy_debit a
                            INNER JOIN pussy_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date10 OR :start_date11 = '')
                        AND (a.created_at <= :end_date10 OR :end_date11 = '')


                    ) as a
                    ";
            
            $params = [
                      $adminArr
                      ,$adminArr
                      ,$adminArr
                      ,$adminArr
                      ,$adminArr
                      ,$adminArr
                  ];



            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;
            $params['start_date2'] = $startDate;
            $params['start_date3'] = $startDate;
            $params['end_date2'] = $endDate;
            $params['end_date3'] = $endDate;
            $params['start_date4'] = $startDate;
            $params['start_date5'] = $startDate;
            $params['end_date4'] = $endDate;
            $params['end_date5'] = $endDate;
            $params['start_date6'] = $startDate;
            $params['start_date7'] = $startDate;
            $params['end_date6'] = $endDate;
            $params['end_date7'] = $endDate;
            $params['start_date8'] = $startDate;
            $params['start_date9'] = $startDate;
            $params['end_date8'] = $endDate;
            $params['end_date9'] = $endDate;

            $params['start_date10'] = $startDate;
            $params['start_date11'] = $startDate;
            $params['end_date10'] = $endDate;
            $params['end_date11'] = $endDate;

            $db = DB::select($sql,$params);
        

            return $db;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return '';
        }
    }

    public static function getTopFiveMember($startDate,$endDate)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = Helper::getDownlineArrList();

            $sql = " 
                   SELECT a.username    
                          , SUM(a.win_loss) 'win_loss'
                     FROM(      
                            SELECT c.username
                                , SUM(b.amount - a.amount) 'win_loss'
                            FROM aas_debit a
                            INNER JOIN aas_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                            WHERE c.admin_id IN (?)
                            AND (a.created_at >= :start_date OR :start_date1 = '')
                            AND (a.created_at <= :end_date OR :end_date1 = '')
                            GROUP BY c.username

                            UNION ALL

                            SELECT c.username
                                , SUM(b.amount - a.amount) 'win_loss'
                            FROM fg_debit a
                            INNER JOIN fg_credit b ON a.txn_id = b.txn_id 
                            INNER JOIN member c ON a.member_id = c.id
                            WHERE c.admin_id IN (?)
                            AND (a.created_at >= :start_date2 OR :start_date3 = '')
                            AND (a.created_at <= :end_date2 OR :end_date3 = '')
                            GROUP BY c.username

                            UNION ALL

                            SELECT c.username
                                , SUM(b.amount - a.bet) 'win_loss'
                            FROM gs_debit a
                            INNER JOIN gs_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                            WHERE c.admin_id IN (?)
                            AND (a.created_at >= :start_date4 OR :start_date5 = '')
                            AND (a.created_at <= :end_date4 OR :end_date5 = '')
                            GROUP BY c.username

                            UNION ALL

                            SELECT c.username
                                , SUM(b.amount - a.bet) 'win_loss'
                            FROM cp_debit a
                            INNER JOIN cp_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                            WHERE c.admin_id IN (?)
                            AND (a.created_at >= :start_date6 OR :start_date7 = '')
                            AND (a.created_at <= :end_date6 OR :end_date7 = '')
                            GROUP BY c.username

                            UNION ALL

                            SELECT c.username
                                , SUM(b.amount - a.bet) 'win_loss'
                            FROM noe_debit a
                            INNER JOIN noe_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                            WHERE c.admin_id IN (?)
                            AND (a.created_at >= :start_date8 OR :start_date9 = '')
                            AND (a.created_at <= :end_date8 OR :end_date9 = '')
                            GROUP BY c.username

                            UNION ALL

                            SELECT c.username
                                , SUM(b.amount - a.bet) 'win_loss'
                            FROM pussy_debit a
                            INNER JOIN pussy_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                            INNER JOIN member c ON a.member_id = c.id
                            WHERE c.admin_id IN (?)
                            AND (a.created_at >= :start_date10 OR :start_date11 = '')
                            AND (a.created_at <= :end_date10 OR :end_date11 = '')
                            GROUP BY c.username

                        ) as a
                     GROUP BY a.username ORDER BY SUM(a.win_loss) DESC LIMIT 5
                    ";
            
            $params = [$adminArr,$adminArr,$adminArr,$adminArr,$adminArr,$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;
            $params['start_date2'] = $startDate;
            $params['start_date3'] = $startDate;
            $params['end_date2'] = $endDate;
            $params['end_date3'] = $endDate;
            $params['start_date4'] = $startDate;
            $params['start_date5'] = $startDate;
            $params['end_date4'] = $endDate;
            $params['end_date5'] = $endDate;
            $params['start_date6'] = $startDate;
            $params['start_date7'] = $startDate;
            $params['end_date6'] = $endDate;
            $params['end_date7'] = $endDate;
            $params['start_date8'] = $startDate;
            $params['start_date9'] = $startDate;
            $params['end_date8'] = $endDate;
            $params['end_date9'] = $endDate;

            $params['start_date10'] = $startDate;
            $params['start_date11'] = $startDate;
            $params['end_date10'] = $endDate;
            $params['end_date11'] = $endDate;

            $db = DB::select($sql,$params);


            return $db;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return '';
        }
    }

    public static function getTopFiveAgent($startDate,$endDate)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            $adminArr = Helper::getDownlineArrList();

            $sql = " 
               SELECT b.username    
                      ,a.admin_id
                      , SUM(a.win_loss) 'win_loss'
                 FROM(      
                        SELECT c.admin_id
                            , SUM(b.amount - a.amount) 'win_loss'
                        FROM aas_debit a
                        INNER JOIN aas_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date OR :start_date1 = '')
                        AND (a.created_at <= :end_date OR :end_date1 = '')
                        GROUP BY c.admin_id

                        UNION ALL

                        SELECT c.admin_id
                            , SUM(b.amount - a.amount) 'win_loss'
                        FROM fg_debit a
                        INNER JOIN fg_credit b ON a.txn_id = b.txn_id 
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date2 OR :start_date3 = '')
                        AND (a.created_at <= :end_date2 OR :end_date3 = '')
                        GROUP BY c.admin_id

                        UNION ALL

                        SELECT c.admin_id
                            , SUM(b.amount - a.bet) 'win_loss'
                        FROM gs_debit a
                        INNER JOIN gs_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date4 OR :start_date5 = '')
                        AND (a.created_at <= :end_date4 OR :end_date5 = '')
                        GROUP BY c.admin_id

                        UNION ALL

                        SELECT c.admin_id
                            , SUM(b.amount - a.bet) 'win_loss'
                        FROM cp_debit a
                        INNER JOIN cp_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date6 OR :start_date7 = '')
                        AND (a.created_at <= :end_date6 OR :end_date7 = '')
                        GROUP BY c.admin_id

                        UNION ALL

                        SELECT c.admin_id
                            , SUM(b.amount - a.bet) 'win_loss'
                        FROM noe_debit a
                        INNER JOIN noe_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date8 OR :start_date9 = '')
                        AND (a.created_at <= :end_date8 OR :end_date9 = '')
                        GROUP BY c.admin_id

                        UNION ALL

                        SELECT c.admin_id
                            , SUM(b.amount - a.bet) 'win_loss'
                        FROM pussy_debit a
                        INNER JOIN pussy_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                        INNER JOIN member c ON a.member_id = c.id
                        WHERE c.admin_id IN (?)
                        AND (a.created_at >= :start_date10 OR :start_date11 = '')
                        AND (a.created_at <= :end_date10 OR :end_date11 = '')
                        GROUP BY c.admin_id

                    ) as a
                    LEFT JOIN
                    admin b
                    ON a.admin_id = b.id
                    GROUP BY b.username ORDER BY SUM(a.win_loss) DESC LIMIT 5
                ";
            
            $params = [$adminArr,$adminArr,$adminArr,$adminArr,$adminArr,$adminArr];

            $preparedPDO = Helper::prepareWhereIn($sql,$params);
            
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $preparedPDO = Helper::convertSQLBindingParams($sql,$params);
          
            $sql = $preparedPDO['sql'];
            $params = $preparedPDO['params'];

            $params['start_date'] = $startDate;
            $params['start_date1'] = $startDate;
            $params['end_date'] = $endDate;
            $params['end_date1'] = $endDate;
            $params['start_date2'] = $startDate;
            $params['start_date3'] = $startDate;
            $params['end_date2'] = $endDate;
            $params['end_date3'] = $endDate;
            $params['start_date4'] = $startDate;
            $params['start_date5'] = $startDate;
            $params['end_date4'] = $endDate;
            $params['end_date5'] = $endDate;
            $params['start_date6'] = $startDate;
            $params['start_date7'] = $startDate;
            $params['end_date6'] = $endDate;
            $params['end_date7'] = $endDate;
            $params['start_date8'] = $startDate;
            $params['start_date9'] = $startDate;
            $params['end_date8'] = $endDate;
            $params['end_date9'] = $endDate;

            $params['start_date10'] = $startDate;
            $params['start_date11'] = $startDate;
            $params['end_date10'] = $endDate;
            $params['end_date11'] = $endDate;

            $db = DB::select($sql,$params);
            
            foreach($db as $d)
            {
                if($d->admin_id == 0)
                {
                    $db2 = DB::select("
                        SELECT username
                        FROM admin
                        WHERE id= 1
                        ");

                    $d->username = $db2[0]->username;
                }
            }


            return $db;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return '';
        }
    }


    public static function getProfitProduct($startDate,$endDate)
    {
        try
        {
            $user = Auth::user();
            $userLevel = $user->level;

            if($userLevel == 0)
            {
                $id = '';
            }
            else
            {
                $id = $user->admin_id;
            }

            $db = DB::select(" SELECT '1' AS 'prd_id'
                                    , CASE WHEN ? = 0 THEN SUM(b.tier1_pt_amt)
                                            WHEN ? = 1 THEN SUM(b.tier2_pt_amt)
                                            WHEN ? = 2 THEN SUM(b.tier3_pt_amt)
                                            WHEN ? = 3 THEN SUM(b.tier4_pt_amt)
                                        ELSE '0'
                                    END 'win_loss'
                                FROM aas_debit a
                                INNER JOIN aas_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                                INNER JOIN member c ON a.member_id = c.id
                                LEFT JOIN tiers d ON c.admin_id = d.admin_id
                                WHERE a.prd_id = '9'
                                    AND (a.created_at >= ? OR '' = ?) 
                                    AND (a.created_at <= ? OR '' = ?) 
                                    AND (d.admin_id = ? OR d.up1_tier = ? OR d.up2_tier = ? OR '' = ?)

                                
                                UNION ALL
                            
                                SELECT '2' AS 'prd_id'
                                    , CASE WHEN ? = 0 THEN SUM(b.tier1_pt_amt)
                                            WHEN ? = 1 THEN SUM(b.tier2_pt_amt)
                                            WHEN ? = 2 THEN SUM(b.tier3_pt_amt)
                                            WHEN ? = 3 THEN SUM(b.tier4_pt_amt)
                                            ELSE '0'
                                        END 'win_loss'
                                FROM haba_debit a
                                INNER JOIN haba_credit b ON a.txn_id = b.txn_id
                                INNER JOIN member c ON a.member_id = c.id
                                LEFT JOIN tiers d ON c.admin_id = d.admin_id
                                WHERE (a.created_at >= ? OR '' = ?) 
                                    AND (a.created_at <= ? OR '' = ?) 
                                    AND (d.admin_id = ? OR d.up1_tier = ? OR d.up2_tier = ? OR '' = ?)

                                UNION ALL
                            
                                SELECT '3' AS 'prd_id'
                                    , CASE WHEN ? = 0 THEN SUM(b.tier1_pt_amt)
                                            WHEN ? = 1 THEN SUM(b.tier2_pt_amt)
                                            WHEN ? = 2 THEN SUM(b.tier3_pt_amt)
                                            WHEN ? = 3 THEN SUM(b.tier4_pt_amt)
                                            ELSE '0'
                                        END 'win_loss'
                                FROM pp_debit a
                                INNER JOIN pp_credit b ON a.txn_id = b.txn_id
                                INNER JOIN member c ON a.member_id = c.id
                                LEFT JOIN tiers d ON c.admin_id = d.admin_id
                                WHERE (a.created_at >= ? OR '' = ?) 
                                    AND (a.created_at <= ? OR '' = ?) 
                                    AND (d.admin_id = ? OR d.up1_tier = ? OR d.up2_tier = ? OR '' = ?)

                                UNION ALL
                            
                                SELECT '4' AS 'prd_id'
                                    , CASE WHEN ? = 0 THEN SUM(b.tier1_pt_amt)
                                            WHEN ? = 1 THEN SUM(b.tier2_pt_amt)
                                            WHEN ? = 2 THEN SUM(b.tier3_pt_amt)
                                            WHEN ? = 3 THEN SUM(b.tier4_pt_amt)
                                            ELSE '0'
                                        END 'win_loss'
                                 FROM aas_debit a
                                INNER JOIN aas_credit b ON a.txn_id = b.txn_id AND a.prd_id = b.prd_id
                                INNER JOIN member c ON a.member_id = c.id
                                LEFT JOIN tiers d ON c.admin_id = d.admin_id
                                 WHERE a.prd_id = '208'
                                    AND (a.created_at >= ? OR '' = ?)
                                    AND (a.created_at <= ? OR '' = ?) 
                                    AND (d.admin_id = ? OR d.up1_tier = ? OR d.up2_tier = ? OR '' = ?)

                        ",[$userLevel,$userLevel,$userLevel,$userLevel,$startDate,$startDate,$endDate,$endDate,$id,$id,$id,$id
                           ,$userLevel,$userLevel,$userLevel,$userLevel,$startDate,$startDate,$endDate,$endDate,$id,$id,$id,$id
                           ,$userLevel,$userLevel,$userLevel,$userLevel,$startDate,$startDate,$endDate,$endDate,$id,$id,$id,$id
                           ,$userLevel,$userLevel,$userLevel,$userLevel,$startDate,$startDate,$endDate,$endDate,$id,$id,$id,$id]
                    );


            $sxg = $db[0]->win_loss;
            $haba = $db[1]->win_loss;
            $prag = $db[2]->win_loss;
            $wm = $db[3]->win_loss;

            return [$sxg,$haba,$prag,$wm];

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return '';
        }
    }
}
