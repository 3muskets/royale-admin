<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JobController;
use Log;

class GetNOEReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetNOEReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get NOE Game';

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
        Log::info('Cron : GetNOEReport START');

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
                    WHERE type = 'GetNOEReport'
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
                        WHERE type = 'GetNOEReport'
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

            Log::info('Cron : GetNOEReport ERROR - '.$e);
        }

        // check allow cron to proceed
        if (!$allowCron)
        {          
            Log::info('Cron : GetNOEReport TERMINATED');
            return;
        }

        try 
        {
            $db = DB::select("SELECT login_id, member_id
                            FROM noe_users");

            foreach ($db as $users) 
            {
                $username = $users->login_id;
                $memberId = $users->member_id;

                $url = env('918KISS_API_URL_2').'/ashx/GameLog.ashx';
                $time = floor(microtime(true) * 1000);
                $authCode = env('918KISS_AUTH');
                $secretKey = env('918KISS_SECRET_KEY');
                $sign = strtoupper(md5(strtolower($authCode.$username.$time.$secretKey)));
                $endDate = date('Y-m-d H:i:s', time() + 86400);
                $startDate = date("Y-m-d", strtotime('+8 hours'))."%2000:00:00";
                $endDate = date("Y-m-d", strtotime('+8 hours'))."%2023:59:59";
                $pageSize = 1000;

                $pageIndex = 1;

                for ($pageIndex=1; $pageIndex < 10000; $pageIndex++) 
                { 
                    if ($pageIndex!=1) 
                    {
                        sleep(1);
                    }
                    $url = env('918KISS_API_URL_2').'/ashx/GameLog.ashx'.'?userName='.$username.'&sDate='.$startDate.'&eDate='.$endDate.'&time='.$time.'&authcode='.$authCode.'&sign='.$sign.'&pageSize='.$pageSize.'&pageIndex='.$pageIndex;

                    $response = Helper::getData($url);
                    $response = json_decode($response,true);

                    if ($response['code'] != 0) 
                    {
                        log::debug('Insert 918 Kiss Game Log Error: '.$response['code'].' '.$response['msg']);
                        break;
                    }

                    if (sizeof($response['results']) == 0) 
                    {
                        break;
                    }

                    foreach ($response['results'] as $value) 
                    {
                        $beginBalance = $value['BeginBlance'];
                        $classId = $value['ClassID'];
                        $createTime = $value['CreateTime'];
                        $endBlance = $value['EndBlance'];
                        $gameID = $value['GameID'];
                        $win = $value['Win'];
                        $bet = $value['bet'];
                        $uuid = $value['uuid'];

                        if ($gameID == -1) 
                        {
                            continue;
                        }

                        DB::insert("INSERT INTO noe_debit(txn_id, member_id, prd_id, category, class_id, begin_bal, end_bal, game_id, bet, created_time, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                                ON DUPLICATE KEY UPDATE
                                     begin_bal = VALUES(begin_bal)
                                        ,end_bal = VALUES(end_bal)
                                        ,member_id = VALUES(member_id)
                                        ,bet = VALUES(bet)
                                        ,created_time = VALUES(created_time)
                                        ,updated_at = NOW()
                                        "
                                ,
                                [
                                    $uuid,
                                    $memberId,
                                    Providers::NOE,
                                    3,
                                    $classId,
                                    $beginBalance,
                                    $endBlance,
                                    $gameID,
                                    $bet,
                                    $createTime,
                                ]);

                        $debitAmt = $bet;
                        $amount = $win;

                        $wlAmt = $debitAmt - $amount;
                        $type = 'c';

                        //insert transaction
                        $db = DB::insert('
                                INSERT INTO noe_credit
                                (prd_id,txn_id,type,amount
                                ,created_at)
                                VALUES
                                (?,?,?,?
                                ,NOW())
                                ON DUPLICATE KEY UPDATE
                                type = VALUES(type)'
                                ,[  Providers::NOE,$uuid,$type,$win]);
                    }
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
                        WHERE type = 'GetNOEReport'
                ");
            }
            else
            {
                DB::update("
                        UPDATE cron_status 
                        SET is_running = 0
                        WHERE type = 'GetNOEReport'
                ");
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            Log::info('Cron : GetNOEReport ERROR - '.$e);
        }

        Log::info('Cron : GetNOEReport END');
    }
}