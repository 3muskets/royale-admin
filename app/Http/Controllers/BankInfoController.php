<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;


use Auth;
use Log;

class BankInfoController extends Controller
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

            $user = Auth::user();
            $level = $user->level;

            $sql = "
                    SELECT a.id 'info_id',a.acc_no,a.name,a.min_deposit_amt,a.max_deposit_amt,a.bank_id
                    ,a.status,a.suspended,a.created_at,a.updated_at,b.name 'bank'
                    FROM admin_bank_info a 
                    LEFT JOIN bank b
                    ON a.bank_id = b.id
                    ";

            if($level > 0)
            {
                $sql .= "
                        WHERE a.status = 'a'
                        ";
            }

            $params = [
                    ];

            $orderByAllow = ['min_deposit_amt,max_deposit_amt'];
            $orderByDefault = 'info_id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page, 500);

            foreach($data['results'] as $d)
            {
                $d->status_desc = Helper::getOptionsValue(self::getOptionsStatus(), $d->status);
                $d->suspended_desc = Helper::getOptionsValue(self::getOptionsSuspended(), $d->suspended);
            }

            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }


    public static function getBankList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');


            $sql = "
                    SELECT *
                    FROM bank 
                    ";


            $params = [
                    ];

            $orderByAllow = [];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);

            $data = Helper::paginateData($sql,$params,$page, 500);

            foreach($data['results'] as $d)
            {

            }

            return Response::make(json_encode($data), 200);

        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }


    public static function getOptionBankList()
    {
        try
        {
            $data  = [];

            $db = DB::select("
                SELECT id,name
                FROM bank
                WHERE status = 1
                ORDER BY id 
                ASC");

            foreach($db as $d)
            {
                $data[] = [$d->id,$d->name];
            }
            
            return $data;

        }
        catch(\Exception $e)
        {
            Log::debug($e);

            return [];
        }
    }

    public static function getOptionsStatus()
    {
        return  [
            ['a', __('option.bank.active')]
            ,['i', __('option.bank.inactive')]
        ];
    }

    public static function getOptionsBankStatus()
    {
        return  [
            ['1', __('Online')]
            ,['0', __('Offline')]
        ];
    }


    public static function getOptionsSuspended()
    {
        return  [
            ['0', __('option.bank.suspended.no')]
            ,['1', __('option.bank.suspended.yes')]
        ];
    }

    public static function update(Request $request)
    {
        DB::beginTransaction();
        try 
        {

            $infoId = $request->input('id');
            $bankNameId = $request->input('bank_name_id');
            $accNo = $request->input('acc_no');
            $holderName = $request->input('holder_name');
            $status = $request->input('status');
            $minAmt = $request->input('min_amt');
            $maxAmt = $request->input('max_amt');
            $suspended = $request->input('suspended');

            $minAmt = str_replace( ',', '', $minAmt);
            $maxAmt = str_replace( ',', '', $maxAmt);

            $user = Auth::user();
            $userId = $user->admin_id;

            $errMsg = [];
           
            if(!ctype_digit($accNo))
            {
                array_push($errMsg, __('error.bank.info.accno.numeric'));
            }


            if($holderName == '')
            {
                array_push($errMsg, __('error.bank.info.holdername.empty'));
            }

            if(!Helper::checkInputFormat('alphabetWithSpace',$holderName))
            {
                array_push($errMsg, __('error.bank.info.holdername.alphabet'));
            }

            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                array_push($errMsg, 'Invalid Status');
            }
            if(!Helper::checkValidOptions(self::getOptionsSuspended(),$suspended))
            {
                array_push($errMsg, 'Invalid Suspended');
            }


            if(!is_numeric($minAmt))
            {
                array_push($errMsg,"Invalid Value for Min Deposit Amount");
            }

            if(!is_numeric($maxAmt))
            {
                array_push($errMsg,"Invalid Value for Max Deposit Amount");
            }


            if($maxAmt <= $minAmt)
            {
                array_push($errMsg, "Max Deposit Amount must bigger than Min Deposit Amount");
            }

            $db = DB::select("
                SELECT name
                FROM bank
                WHERE id = ?
                ",[$bankNameId]
            );

            if(sizeof($db) == 0)
            {
                array_push($errMsg,"Invalid Bank");
            }

            //check the bank acc duplicate
            $db = DB::select('SELECT id
                                      FROM admin_bank_info 
                                      WHERE acc_no = ?',
                                      [$accNo]); 

            if(sizeof($db) > 0)
            {
                if($db[0]->id != $infoId || $infoId == null)
                {
                    array_push($errMsg, __('error.bank.info.duplicate'));
                }   
            }


            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            if($infoId == null)
            {
                 DB::insert('INSERT INTO admin_bank_info(bank_id,name,acc_no,min_deposit_amt,max_deposit_amt,status,suspended) 
                                    VALUES (?,?,?,?,?,?,?)', 
                                    [$bankNameId,$holderName,$accNo,$minAmt,$maxAmt,$status,$suspended]
                  );
            }
            else
            {
                 DB::update('UPDATE admin_bank_info 
                                SET bank_id =?,name =?,acc_no =?,min_deposit_amt=?,max_deposit_amt=?
                                ,suspended=?,status=?
                                WHERE id=?', 
                                [$bankNameId,$holderName,$accNo,$minAmt,$maxAmt,$suspended,$status,$infoId]
                  );
            }

            $response = ['status' => 1];
            DB::commit();
            return json_encode($response);
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            DB::rollback();
            $response = ['status' => 0
                        ,'error' => __('error.bank.info.invalid')
                        ];

            return json_encode($response);
        }
    }


    public static function createBank(Request $request)
    {
        DB::beginTransaction();

        try 
        {

            $errMsg = [];

            $name = $request->input('bank_name');
            $siteUrl = $request->input('site_url');
            $status = $request->input('status');


            if(!Helper::checkInputFormat('alphabetWithSpace',$name))
            {
                array_push($errMsg, __('error.bank.info.bankname.alphabet'));
            }


            //check the bank acc duplicate
            $db = DB::select('SELECT id
                              FROM bank 
                              WHERE name = ?',
                              [$name]); 

            if(sizeof($db) > 0)
            {
                array_push($errMsg, __('Duplicate Bank Name'));  
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            DB::insert('INSERT INTO bank(name,site_url,status) 
                        VALUES (?,?,?)', 
                        [$name,$siteUrl,$status]
              );

            $response = ['status' => 1
                        ];


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


    public static function updateBank(Request $request)
    {
        DB::beginTransaction();

        try 
        {

            $errMsg = [];

            $id = $request->input('bank_id');
            $name = $request->input('bank_name');
            $siteUrl = $request->input('site_url');
            $status = $request->input('status');


            if(!Helper::checkInputFormat('alphabetWithSpace',$name))
            {
                array_push($errMsg, __('error.bank.info.bankname.alphabet'));
            }


            //check the bank acc duplicate
            $db = DB::select('SELECT id
                              FROM bank 
                              WHERE name = ?',
                              [$name]); 

            if(sizeof($db) > 0)
            {
                array_push($errMsg, __('Duplicate Bank Name'));  
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            DB::update("
                UPDATE bank
                SET name = ?
                ,site_url = ?
                ,status = ?
                WHERE id = ?
                ",[$name,$siteUrl,$status,$id]
            );


            $response = ['status' => 1
                        ];


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


    public static function bankCreditTransfer(Request $request)
    {
        DB::beginTransaction();
        
        try 
        {
            
            $bankName = $request->input('bank_name');
            $accNo = $request->input('acc_no');
            $toBankAccNo = $request->input('to_bank');
            $holderName = $request->input('holder_name');         
            $actionType = $request->input('type'); //1-debit 2-credit 3-transfer

            $currentAmt = 0;
            $toCurrentAmt = 0;

            $errMsg = [];

            if($actionType != 1 && $actionType != 2 && $actionType != 3)
            {
                array_push($errMsg, __('Invalid Type')); 
            }

            $amount = $request->input('current_threshold');
            

            if($actionType == 1)
                $amount = $request->input('debit_amount');
            else if($actionType == 2)
                $amount = $request->input('credit_amount');
            else if($actionType == 3)
                $amount = $request->input('transfer_amount');

            $amount = str_replace( ',', '', $amount);
           
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



            //get current amount and check account
            //lock table
            $db = DB::select("
                    SELECT current_threshold
                    FROM admin_bank_info
                    WHERE acc_no = ? for UPDATE
                    ",[$accNo]
                );

            if(sizeof($db) == 0)
            {
               array_push($errMsg, __('Invalid Bank Account')); 
            }
            else
            {
                $currentAmt = $db[0]->current_threshold;
            }

            if($actionType == 3)
            {
                //get transfer to bank current amount and check account
                //lock table
                $db = DB::select("
                        SELECT current_threshold
                        FROM admin_bank_info
                        WHERE acc_no = ? for UPDATE
                        ",[$toBankAccNo]
                    );

                if(sizeof($db) == 0)
                {
                   array_push($errMsg, __('Invalid Transfer Bank Account')); 
                }
                else
                {
                    //transfer to bank Acc Amount
                    $toCurrentAmt = $db[0]->current_threshold;
                }
            }


            if($actionType == 1 && ($currentAmt - $amount < 0))
            {
                array_push($errMsg, __('Debit amount is exceeded available Amount'));
            }

            if($actionType == 3 && ($currentAmt - $amount < 0))
            {
                array_push($errMsg, __('Amount is not enough To Transfer'));
            }

            if($errMsg)
            {
                DB::rollback();
                
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }


            if($actionType == 1)
            {
                DB::update("
                    UPDATE admin_bank_info
                    SET current_threshold = current_threshold-?
                    WHERE acc_no = ?
                    ",[$amount,$accNo]);
            }
            else if($actionType == 2)
            {
                DB::update("
                    UPDATE admin_bank_info
                    SET current_threshold = current_threshold+?
                    WHERE acc_no = ?
                    ",[$amount,$accNo]);                
            }
            else if($actionType == 3)
            {
                DB::update("
                    UPDATE admin_bank_info
                    SET current_threshold = current_threshold-?
                    WHERE acc_no = ?
                    ",[$amount,$accNo]);                

                DB::update("
                    UPDATE admin_bank_info
                    SET current_threshold = current_threshold+?
                    WHERE acc_no = ?
                    ",[$amount,$toBankAccNo]);  

            }


            $response = ['status' => 1];
            DB::commit();
            return json_encode($response);



        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            DB::rollback();
            $response = ['status' => 0
                        ,'error' => __('Invalid Bank Transfer')
                        ];

            return json_encode($response);
        }
    }

    public static function countActiveAccount()
    {
        $count = 0;

        $db = DB::select("SELECT COUNT(id) 'count'
                            FROM admin_bank_info 
                            WHERE status = ?",['a']);

        if(sizeof($db) > 0)
        {
           return $db[0]->count;
        }

        return $count;
    }
}

