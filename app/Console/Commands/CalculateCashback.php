<?php

namespace App\Console\Commands;
use App\Http\Controllers\Helper;
use Illuminate\Console\Command;
use DB;
use Log;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\MemberMessageController;
use App\Http\Controllers\AdminCreditController;


class CalculateCashback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:Cashback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Member Cashback';

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

    //TODO - rebate txnhistory
    public function handle()
    {
       Log::debug('Cron run : Rebate - UpdateMemberCashback');
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
                    WHERE type = 'UpdateMemberCashback'
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
                        WHERE type = 'UpdateMemberCashback'
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

            Log::info('Cron : UpdateMemberCashback ERROR - '.$e);
        }

        // check allow cron to proceed
        if (!$allowCron)
        {          
            Log::info('Cron : UpdateMemberCashback TERMINATED');
            return;
        }

        // start cron
        DB::beginTransaction(); 
        
        try
        {

            $todayDate = NOW();
           
            $prevDate = date('Y-m-d',strtotime($todayDate. '-1days +8 hours'));

            $prevStartDate = date('Y-m-d 00:00:00',strtotime($todayDate. '-1days +8 hours'));
            $prevEndDate = date('Y-m-d 23:59:59',strtotime($todayDate. '-1days +8 hours'));


            $db = DB::select("
                    SELECT rate,amount
                    FROM cashback_setting
                    WHERE id = 1 AND status = 'a'
                    AND start_date < ? AND end_date > ?
                ",[$todayDate,$todayDate]
            );


            if(sizeof($db) != 0)
            {
                $targetLoseAmt = $db[0]->amount;
                $rate = $db[0]->rate;


                $data = DB::select("
                                    SELECT a.member_id,sum(turnover) 'turnover',sum(win_loss) 'win_loss'
                                    FROM
                                    (
                                        SELECT a.txn_id,a.member_id, '1' as 'provider_id',a.created_at,
                                        1 AS 'category',
                                        (a.amount) 'turnover',
                                        (b.amount -a.amount) 'win_loss'
                                        FROM evo_debit a 
                                        INNER JOIN evo_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE b.type = 'c'
                                        AND ((a.created_at + INTERVAL 8 HOUR) >= ? AND (a.created_at + INTERVAL 8 HOUR) <= ?)

                                        UNION ALL 

                                        SELECT a.txn_id,a.member_id,'2' as 'provider_id',a.created_at,
                                        1 AS 'category',
                                        (a.bet) 'turnover',
                                        (b.amount -a.bet) 'win_loss'
                                        FROM ab_debit a 
                                        INNER JOIN ab_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE b.type = 'c'
                                        AND ((a.created_at + INTERVAL 8 HOUR) >= ? AND (a.created_at + INTERVAL 8 HOUR) <= ?)

                                        UNION ALL 

                                        SELECT a.txn_id,a.member_id,'3' as 'provider_id',a.created_at,
                                        1 AS 'category',
                                        (a.amount) 'turnover',
                                        (b.amount -a.amount) 'win_loss'
                                        FROM sa_debit a 
                                        INNER JOIN sa_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE b.type = 'c'
                                        AND ((b.created_at + INTERVAL 8 HOUR) >= ? AND (b.created_at + INTERVAL 8 HOUR) <= ?)

                                        UNION ALL 

                                        SELECT a.txn_id,a.member_id,'5' as 'provider_id',a.created_at,
                                        1 AS 'category',
                                        (a.amount) 'turnover',
                                        (b.amount -a.amount) 'win_loss'
                                        FROM pt_debit a 
                                        INNER JOIN pt_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE ((b.created_at + INTERVAL 8 HOUR) >= ? AND (b.created_at + INTERVAL 8 HOUR) <= ?)

                                        UNION ALL 

                                        SELECT a.txn_id,a.member_id,'6' as 'provider_id',a.created_at,
                                        2 AS 'category',
                                        (a.amount) 'turnover',
                                        (b.winloss -a.amount) 'win_loss'
                                        FROM sbo_debit a 
                                        INNER JOIN sbo_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE b.status = 'c'
                                        AND ((b.created_at + INTERVAL 8 HOUR) >= ? AND (b.created_at + INTERVAL 8 HOUR) <= ?)

                                        UNION ALL 

                                        SELECT a.txn_id,a.member_id,'7' as 'provider_id',a.created_at,
                                        2 AS 'category',
                                        (a.amount) 'turnover',
                                        (b.amount -a.amount) 'win_loss'
                                        FROM ibc_debit a 
                                        INNER JOIN ibc_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE b.type = 'c'
                                        AND ((b.created_at + INTERVAL 8 HOUR) >= ? AND (b.created_at + INTERVAL 8 HOUR) <= ?)

                                       UNION ALL 

                                       SELECT a.txn_id,a.member_id,'8' as 'provider_id',a.created_at,
                                        3 AS 'category',
                                        (a.amount) 'turnover',
                                        (b.amount -a.amount) 'win_loss'
                                        FROM joker_debit a 
                                        INNER JOIN joker_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE b.type = 'c'
                                        AND ((b.created_at + INTERVAL 8 HOUR) >= ? AND (b.created_at + INTERVAL 8 HOUR) <= ?)

                                       UNION ALL 

                                       SELECT a.txn_id,a.member_id,'9' as 'provider_id',a.created_at,
                                        3 AS 'category',
                                        (a.bet) 'turnover',
                                        (b.amount -a.bet) 'win_loss'
                                        FROM noe_debit a 
                                        INNER JOIN noe_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE b.type = 'c'
                                        AND ((b.created_at + INTERVAL 8 HOUR) >= ? AND (b.created_at + INTERVAL 8 HOUR) <= ?)

                                        UNION ALL

                                        SELECT a.txn_id,a.member_id,'10' as 'provider_id',a.created_at,
                                        3 AS 'category',
                                        (a.bet) 'turnover',
                                        (b.amount -a.bet) 'win_loss'
                                        FROM scr_debit a 
                                        INNER JOIN scr_credit b
                                        ON a.txn_id = b.txn_id 
                                        WHERE b.type = 'c'
                                        AND ((b.created_at + INTERVAL 8 HOUR) >= ? AND (b.created_at + INTERVAL 8 HOUR) <= ?)

                                        UNION ALL

                                        SELECT a.txn_id,a.member_id,'11' as 'provider_id',a.created_at,
                                        1 AS 'category',
                                        (a.amount) 'turnover',
                                        (b.amount -a.amount) 'win_loss'
                                        FROM xe88_debit a 
                                        INNER JOIN xe88_credit b
                                            ON a.txn_id = b.txn_id 
                                        WHERE ((b.created_at + INTERVAL 8 HOUR) >= ? AND (b.created_at + INTERVAL 8 HOUR) <= ?)


                                    ) AS a
                                    GROUP BY a.member_id


                    ",[$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                       ,$prevStartDate,$prevEndDate
                   ]);


                    foreach($data as $d)
                    {
                        $memberId = $d->member_id;
                        $ttlWinLose = $d->win_loss;
                        $refId = Helper::prepareRefId(2);

                        if($ttlWinLose < 0)
                        {
                            if(-$ttlWinLose >= $targetLoseAmt)
                            {
                                $cashBackAmt = ($rate/100)*(-$ttlWinLose);
     
                                //insert daily txn
                                DB::insert('
                                        INSERT INTO member_cashback_txn
                                        (member_id,amount,date,credit_txn_id,created_at)
                                        VALUES
                                        (?,?,?,?,NOW())
                                        '
                                        ,[$memberId
                                        ,$cashBackAmt
                                        ,$prevDate
                                        ,$refId
                                        ]);  


                                // get and lock balance
                                $db = DB::select('
                                        SELECT available
                                        FROM member_credit
                                        WHERE member_id = ? 
                                        FOR UPDATE'
                                        ,[$memberId]);

                                $balance = $db[0]->available;


                                $remarkFrom = 'Member Add Credit, From Cashback';

                                //insert member credit txn
                                DB::insert('
                                        INSERT INTO member_credit_txn
                                        (ref_id,type,member_id,credit_before,amount,credit_by,txn_type,remark)
                                        VALUES
                                        (?,?,?,?,?,?,?,?)
                                        '
                                        ,[$refId
                                        ,2
                                        ,$memberId
                                        ,$balance
                                        ,$cashBackAmt
                                        ,1
                                        ,7
                                        ,$remarkFrom
                                        ]); 


                                //update balance and admin response deposit date
                                $db = DB::update('
                                    UPDATE member_credit
                                    SET available = available + ?, admin_deposit_response = NOW() 
                                    WHERE member_id = ?'
                                    ,[  $cashBackAmt
                                        ,$memberId]);

                            
                                $subject = "Member Cashback";
                                $message = "Dear Player, You received member cashback!";

                                DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                                                VALUES(?,0,"a",?,?,NOW())',[$memberId,$message,$subject]);
                                
                                //notification
                                MemberMessageController::sendWS($memberId);



                            }
                        }

                    }

            }




            DB::commit();

        }
        catch(\Exception $e)
        {
            DB::rollback();
            Log::info('Cron : UpdateMemberCashback ERROR - '.$e);

            $gotCronErr = true;

        }

        // end cron

        // update cron status at the end
        try
        {
            // no error
            if (!$gotCronErr)
            {
                DB::update("
                        UPDATE cron_status 
                        SET last_finish_time = NOW()
                            ,is_running = 0
                        WHERE type = 'UpdateMemberCashback'
                ");
            }
            else
            {
                DB::update("
                        UPDATE cron_status 
                        SET is_running = 0
                        WHERE type = 'UpdateMemberCashback'
                ");
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            Log::info('Cron : UpdateMemberCashback ERROR - '.$e);
        }
        Log::info('Cron : UpdateMemberCashback END');

    }

}
