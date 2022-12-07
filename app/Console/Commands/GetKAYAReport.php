<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JobController;
use App\Http\Controllers\Providers;
use Log;

class GetKAYAReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetKAYAReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get KAYA Game';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        Log::info('Cron : GetKAYAReport START');

        // only allow 1 cron run at the same time
        $allowCron = false;
        $gotCronErr = false;

        DB::beginTransaction();
        
        try
        {
            // get & lock is_running
            $db = DB::select("
                    SELECT is_running 
                    FROM cron_status  
                    WHERE type = 'GetKAYAReport'
                    FOR UPDATE
            ");

            // can run
            if (sizeOf($db) > 0 && $db[0]->is_running == 0)
            {
                // update cron status
                DB::update("
                        UPDATE cron_status 
                        SET last_start_time = NOW()
                            ,last_finish_time = NULL
                            ,is_running = 1
                        WHERE type = 'GetKAYAReport'
                ");

                DB::commit();

                $allowCron = true;
            }
            else
            {
                DB::rollBack();
            }
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            Log::info('Cron : GetKAYAReport ERROR - '.$e);
        }

        // check allow cron to proceed
        if (!$allowCron)
        {          
            Log::info('Cron : GetKAYAReport TERMINATED');
            return;
        }

        try 
        {
            $url = env('KAYA_API_URL').'/v1/betlist';
            $timestamp = time();
            $startTime = strtotime('-5 minutes')*1000;
            $endTime = strtotime('-10 seconds')*1000;
            $agentId = env('KAYA_USER');

            $data = ['agentID' => $agentId
                    ,'startUpdateTime' => $startTime
                    ,'endUpdateTime' => $endTime
                    ,'timeStamp' => $timestamp
                ];

            $signMsg = self::AESEncode($data);
            $header = array(
                "AES-ENCODE:".$signMsg,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            );

            $response = Helper::postData($url,$data,$header);
            $response = json_decode($response,true);

            if ($response['rtStatus'] == 1) 
            {
                foreach ($response['data'] as $value) 
                {
                    $username = $value['account'];
                    $betAmount = $value['betAmount']/10000;
                    $txnid = $value['transNo'];
                    $payOut = $value['payOut']/10000;
                    $finished = $value['finished'];
                    $jpKind = $value['jpKind'];
                    $jpPayOut = $value['jpPayOut'];
                    $transactionCode = $value['transactionCode'];
                    $validAmount = $value['validAmount'];

                    $member = DB::select("SELECT member_id
                                        FROM kaya_users
                                        WHERE login_id = ?"
                                        ,[$username]);

                    if (sizeof($member) == 0)
                    {
                        continue;
                    }

                    $memberId = $member[0]->member_id;

                    DB::insert("INSERT INTO kaya_debit(txn_id, member_id, prd_id, category, amount, finished, jp_kind, jp_payout, created_at)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                                 ON DUPLICATE KEY UPDATE
                                      amount = VALUES(amount)
                                         ,jp_kind = VALUES(jp_kind)
                                         ,finished = VALUES(finished)
                                         ,jp_payout = VALUES(jp_payout)
                                         ,updated_at = NOW()
                                         "
                                 ,
                                 [
                                     $txnid,
                                     $memberId,
                                     Providers::KAYA,
                                     3,
                                     $betAmount,
                                     $finished,
                                     $jpKind,
                                     $jpPayOut,
                                 ]);

                    $type = 'c';

                    //insert transaction
                    $db = DB::insert('
                            INSERT INTO kaya_credit
                            (prd_id,txn_id,type,amount
                            ,created_at)
                            VALUES
                            (?,?,?,?
                            ,NOW())
                            ON DUPLICATE KEY UPDATE
                            type = VALUES(type)'
                            ,[  Providers::KAYA,$txnid,$type,$payOut]);
                }
                
            }

        } 
        catch (Exception $e) 
        {
            log::debug($e);

            $gotCronErr = true;
        }

        DB::beginTransaction();
        try
        {
            // no error
            if (!$gotCronErr)
            {
                DB::update("
                        UPDATE cron_status 
                        SET last_finish_time = NOW()
                            ,is_running = 0
                        WHERE type = 'GetKAYAReport'
                ");
            }
            else
            {
                DB::update("
                        UPDATE cron_status 
                        SET is_running = 0
                        WHERE type = 'GetKAYAReport'
                ");
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            Log::info('Cron : GetKAYAReport ERROR - '.$e);
        }

        Log::info('Cron : GetKAYAReport END');
    }

    public static function encrypt($input, $key) 
    {
        $cipher = "aes-128-ecb";
        $data = openssl_encrypt($input,$cipher,$key,OPENSSL_PKCS1_PADDING);

        return $data;
    }

    public static function AESEncode($data)
    {
        try 
        {
            $data = json_encode($data,true);

            $aesEncode = md5(base64_encode(self::encrypt((string)$data, env('KAYA_AES'))).env('KAYA_MD5'));
            
            return $aesEncode;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return '';
        }
    }
}