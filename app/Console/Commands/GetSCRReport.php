<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JobController;
use Log;

class GetSCRReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetSCRReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get SCR Game';

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
        Log::info('Cron : GetSCRReport START');

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
                    WHERE type = 'GetSCRReport'
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
                        WHERE type = 'GetSCRReport'
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

            Log::info('Cron : GetSCRReport ERROR - '.$e);
        }

        // check allow cron to proceed
        if (!$allowCron)
        {          
            Log::info('Cron : GetSCRReport TERMINATED');
            return;
        }

        try 
        {
            $url = env('SCR_API_URL').'/reports';
            $apiPassword = env('SCR_API_PASSWORD');
            $apiUserId =  env('SCR_API_USER');
            $operation = 'gamelog';
            $zip = 0;
            $startDate = date('Y-m-d');
            $startTime = "00:00:00";
            $endDate = "23:59:59";

            $users = DB::select("SELECT login_id, member_id FROM scr_users");

            foreach ($users as $u) 
            {
                $loginId = $u->login_id;
                $memberId = $u->member_id;

                $data = ['apiuserid' => $apiUserId
                    ,'apipassword' => $apiPassword
                    ,'operation' => $operation
                    ,'playerid' => $loginId
                    ,'pagesize' => 20
                    ,'pageindex' => 1
                    ,'date' => $startDate
                    ,'starttime' => $startTime
                    ,'endtime' => $endDate
                ];

                // return $data;

                $response = Helper::postData($url,$data);
                $response = json_decode($response,true);

                if ($response['returncode'] == 0) 
                {
                    $params = [];
                    $params2 = [];

                    $sql = "INSERT INTO scr_debit(txn_id, member_id, category, begin_bal, end_bal, game, bet, date_time, created_at)
                                VALUES :(?,?,?,?,?,?,?,?,NOW()):
                                ON DUPLICATE KEY UPDATE
                                     begin_bal = VALUES(begin_bal)
                                        ,end_bal = VALUES(end_bal)
                                        ,member_id = VALUES(member_id)
                                        ,bet = VALUES(bet)
                                        ,date_time = VALUES(date_time)
                                        ,updated_at = NOW()";

                    $sql2 = "INSERT INTO scr_credit
                                (txn_id,type,amount
                                ,created_at)
                                VALUES
                                :(?,?,?
                                ,NOW()):
                                ON DUPLICATE KEY UPDATE
                                amount = VALUES(amount)";

                    foreach ($response['game_logs'] as $value) 
                    {
                        $beginBalance = $value['begin_balance'];
                        $datetime = $value['date_time'];
                        $endBlance = $value['end_balance'];
                        $game = $value['game'];
                        $win = $value['win'];
                        $bet = $value['bet'];
                        $id = $value['id'];

                        if ($game == 'setscore') 
                        {
                            continue;
                        }

                        array_push($params,
                                        [
                                            $id,
                                            $memberId,
                                            3,
                                            $beginBalance,
                                            $endBlance,
                                            $game,
                                            $bet,
                                            $datetime,
                                        ]
                                    );

                        array_push($params2,[$id,'c',$win]);
                    }

                    if (sizeof($params) != 0) 
                    {
                        $pdo = Helper::prepareBulkInsert($sql,$params);
                        $db = DB::insert($pdo['sql'],$pdo['params']);

                        $pdo = Helper::prepareBulkInsert($sql2,$params2);
                        $db = DB::insert($pdo['sql'],$pdo['params']);
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
                        WHERE type = 'GetSCRReport'
                ");
            }
            else
            {
                DB::update("
                        UPDATE cron_status 
                        SET is_running = 0
                        WHERE type = 'GetSCRReport'
                ");
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            Log::info('Cron : GetSCRReport ERROR - '.$e);
        }

        Log::info('Cron : GetSCRReport END');
    }
}