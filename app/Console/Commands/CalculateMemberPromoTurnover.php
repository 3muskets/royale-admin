<?php

namespace App\Console\Commands;
use App\Http\Controllers\Helper;
use Illuminate\Console\Command;
use DB;
use Log;

class CalculateMemberPromoTurnover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:PromoTurnover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate member Promotion Turnover';

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

    //TODO - Promotion txnhistory
    public function handle()
    {
       Log::debug('Cron run : Promotion - UpdateMemberPromoTurnover');
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
                    WHERE type = 'UpdateMemberPromoTurnover'
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
                        WHERE type = 'UpdateMemberPromoTurnover'
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

            Log::info('Cron : UpdateMemberPromoTurnover ERROR - '.$e);
        }

        // check allow cron to proceed
        if (!$allowCron)
        {          
            Log::info('Cron : UpdateMemberPromoTurnover TERMINATED');
            return;
        }

        // start cron
        DB::beginTransaction(); 
        
        try
        {

            $data = DB::select("
                                SELECT a.txn_id,a.member_id, '1' as 'provider_id',a.created_at,
                                1 AS 'category',
                                (a.amount) 'turnover',
                                (b.amount -a.amount) 'win_loss'
                                FROM evo_debit a 
                                INNER JOIN evo_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.type = 'c' AND b.is_process IS NULL

                                UNION ALL 

                                SELECT a.txn_id,a.member_id,'2' as 'provider_id',a.created_at,
                                1 AS 'category',
                                (a.bet) 'turnover',
                                (b.amount -a.bet) 'win_loss'
                                FROM ab_debit a 
                                INNER JOIN ab_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.type = 'c' AND b.is_process IS NULL

                                UNION ALL 

                                SELECT a.txn_id,a.member_id,'3' as 'provider_id',a.created_at,
                                1 AS 'category',
                                (a.amount) 'turnover',
                                (b.amount -a.amount) 'win_loss'
                                FROM sa_debit a 
                                INNER JOIN sa_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.type = 'c' AND b.is_process IS NULL

                                UNION ALL 

                                SELECT a.txn_id,a.member_id,'5' as 'provider_id',a.created_at,
                                1 AS 'category',
                                (a.amount) 'turnover',
                                (b.amount -a.amount) 'win_loss'
                                FROM pt_debit a 
                                INNER JOIN pt_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.is_process IS NULL

                                UNION ALL 

                                SELECT a.txn_id,a.member_id,'6' as 'provider_id',a.created_at,
                                2 AS 'category',
                                (a.amount) 'turnover',
                                (b.winloss -a.amount) 'win_loss'
                                FROM sbo_debit a 
                                INNER JOIN sbo_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.status = 'c' AND b.is_process IS NULL

                                UNION ALL

                                SELECT a.txn_id,a.member_id,'7' as 'provider_id',a.created_at,
                                2 AS 'category',
                                (a.amount) 'turnover',
                                (b.amount -a.amount) 'win_loss'
                                FROM ibc_debit a 
                                INNER JOIN ibc_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.type = 'c' AND b.is_process IS NULL

                                UNION ALL

                                SELECT a.txn_id,a.member_id,'8' as 'provider_id',a.created_at,
                                3 AS 'category',
                                (a.amount) 'turnover',
                                (b.amount -a.amount) 'win_loss'
                                FROM joker_debit a 
                                INNER JOIN joker_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.type = 'c' AND b.is_process IS NULL

                                UNION ALL

                                SELECT a.txn_id,a.member_id,'9' as 'provider_id',a.created_at,
                                3 AS 'category',
                                (a.bet) 'turnover',
                                (b.amount -a.bet) 'win_loss'
                                FROM noe_debit a 
                                INNER JOIN noe_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.type = 'c' AND b.is_process IS NULL


                                UNION ALL

                                SELECT a.txn_id,a.member_id,'10' as 'provider_id',a.created_at,
                                3 AS 'category',
                                (a.bet) 'turnover',
                                (b.amount -a.bet) 'win_loss'
                                FROM scr_debit a 
                                INNER JOIN scr_credit b
                                    ON a.txn_id = b.txn_id 
                                WHERE b.type = 'c' AND b.is_process IS NULL


                ");


            foreach($data as $d)
            {
                $txnId = $d->txn_id;
                $memberId = $d->member_id;
                $providerId = $d->provider_id;
                $turnover = $d->turnover;
                $winLoss = $d->win_loss;
                $createdAT = $d->created_at;
                $category = $d->category;


                if($providerId == 1)
                {
                    DB::update("
                        UPDATE evo_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );
                }
                else if($providerId == 2)
                {
                    DB::update("
                        UPDATE ab_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );                    
                }
                else if($providerId == 3)
                {
                    DB::update("
                        UPDATE sa_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );                    
                }

                else if($providerId == 5)
                {
                    DB::update("
                        UPDATE pt_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );                    
                }
                else if($providerId == 6)
                {
                    DB::update("
                        UPDATE sbo_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );                    
                }
                else if($providerId == 7)
                {
                    DB::update("
                        UPDATE ibc_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );                    
                }
                else if($providerId == 8)
                {
                    DB::update("
                        UPDATE joker_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );                    
                }
                else if($providerId == 9)
                {
                    DB::update("
                        UPDATE noe_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );                    
                }
                else if($providerId == 10)
                {
                    DB::update("
                        UPDATE scr_credit
                        SET is_process = ?
                        WHERE txn_id = ?
                        ",['1',$txnId]
                    );                    
                }

            
                //process promo turnover 
                $db = DB::select("
                        SELECT a.id,a.deposit_amount,a.turnover,a.target_turnover,a.target_winover,a.win_loss,a.promo_id,a.promo_amount
                        ,b.is_casino,b.is_sportbook,b.is_slot
                        FROM member_promo_turnover a
                        INNER JOIN promo_setting b
                        ON a.promo_id = b.promo_id
                        AND a.status = 'p'
                        AND a.member_id = ?
                        AND a.created_at <= ? 
                        ",[$memberId,$createdAT]
                    );


                if(sizeof($db) != 0)
                {

                    $id = $db[0]->id;
                    $ttlTurnover = $db[0]->turnover;
                    $ttlWinLoss = $db[0]->win_loss;
                    $targetTurnover = $db[0]->target_turnover;
                    $targetWinover = $db[0]->target_winover;
                    $depositAmt = $db[0]->deposit_amount;
                    $promoAmt = $db[0]->promo_amount;
                    $isCasino = $db[0]->is_casino;
                    $isSportbook = $db[0]->is_sportbook;
                    $isSlot = $db[0]->is_slot;
                    $status = 'p';

                    if(($category == 1 && $isCasino == 1) || ($category == 2 && $isSportbook == 1) || ($category == 3 && $isSlot == 1) )
                    {
                        //new total trunover and winloss
                        $ttlTurnover = $ttlWinLoss + $winLoss;
                        $ttlWinLoss = $ttlTurnover + $turnover;                        
                    }
                    else
                    {
                        $winLoss = 0;
                        $turnover = 0;
                    }

                
                    if( ($ttlTurnover >= $targetTurnover) || (-$ttlWinLoss >= ($depositAmt+$promoAmt - 10)) )
                    {
                        $status = 's';
                    }

                    //lock table
                    $db = DB::select("
                        SELECT id
                        FROM member_promo_turnover
                        WHERE id = ? 
                        FOR UPDATE"
                        ,[$id]
                    );


                    //update promo turnover
                    $update = DB::update("
                        UPDATE member_promo_turnover
                        SET turnover = turnover+?,
                        win_loss = win_loss+?,
                        status = ?
                        WHERE id = ?
                        ",[$turnover
                          ,$winLoss
                          ,$status
                          ,$id
                      ]);

                }

            }
            
            DB::commit();

        }
        catch(\Exception $e)
        {
            DB::rollback();
            Log::info('Cron : UpdateMemberPromoTurnover ERROR - '.$e);

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
                        WHERE type = 'UpdateMemberPromoTurnover'
                ");
            }
            else
            {
                DB::update("
                        UPDATE cron_status 
                        SET is_running = 0
                        WHERE type = 'UpdateMemberPromoTurnover'
                ");
            }

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            Log::info('Cron : UpdateMemberPromoTurnover ERROR - '.$e);
        }
        Log::info('Cron : UpdateMemberPromoTurnover END');

    }

}
