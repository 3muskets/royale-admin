<?php

namespace App\Http\Controllers;

use Log;
use DB;

class Providers
{
    //fast game
    const FastGame = 5;

    //GS
    const Gameplay = 1; //casino
    const BBIN = 2;// sportbook/lottery
    // const IBC = 3;//sportbook
    const ALLBET = 4;//sportbook
    const CQ9 = 6;//slot(lobby)
    const WM = 7;//casino
    // const Joker = 8;//fishing game/slot(lobby)
    const PSB4D = 9;//lottery
    const Spade = 10;//fishing game/slot(lobby)
    const QQKeno = 11;//lottery
    const CMD = 12;//sportbook
    const M8BET = 13;//sportbook
    const DIGMAAN = 14;//other
    const EBET = 15;//casino
    const IA = 16;//e-sport
    const NLIVE22 = 17;//casino

    //ps9 casino
    const Ps9EVO = 101;
    const Ps9AG = 102;
    const Ps9PP = 103;
    const Ps9OT = 104;
    const Ps9IA = 105;
    //ps9 slot
    const Ps9PPSlot = 106;
    const Ps9Haba = 107;
    const Ps9Ely = 108;
    const Ps9QS = 109;
    const Ps9SG = 110;
    const Ps9AWS = 111;//not support MYR
    const Ps9PnG = 112;
    const Ps9WM = 113;
    const Ps9Micro = 114;//not support MYR
    const Ps9Joker = 115;//not support MYR
    const Ps9OTSlot = 116;
    const Ps9EvoRtg = 117;
    const Ps9Netent = 118;
    const Ps9Booon = 119;
    const Ps9Playson = 120;
    const Ps9PS = 121;//not support MYR

    //new
    const SBO = 1000;
    const EVO = 1001;
    const IBC = 1002;
    const SA = 1003;
    const AB = 1004;
    const PT = 1005;
    const Joker = 1006;
    const XE88 = 1007;
    const SexyGaming = 1012;

    //external apps
    const MEGA = 1008;
    const NOE = 1009;
    const SCR = 1010;
    const PUSSY = 1011;
    const KAYA = 1013;

    // public static function getProductLogTable($prdId)
    // {   
    //     try
    //     {  
    //         if ($prdId == static::Kiron) 
    //         {
    //             $db = "log_kiron";
    //         }
    //         else if ($prdId == static::SportsBook) 
    //         {
    //             $db = "log_sportsbook";
    //         }
    //         else if ($prdId == static::MiniGame) 
    //         {
    //             $db = "log_minigame";
    //         }

    //         return ['db' => $db];
            
    //     } 
    //     catch(\Exception $e)
    //     {
    //         log::debug($e);
    //         return false;
    //     }
    // }

    public static function isValidProduct($prdId)
    {   
        try
        {  
            //in future need enhancement, including the const defined above.
            $productList = [
                static::Gameplay
                ,static::BBIN
            ];  

           return in_array($prdId, $productList);
        } 
        catch(\Exception $e)
        {
            log::debug($e);
            return false;
        }
    }

    public static function getProductBalance($request)
    {
        $prdId = $request['prd_id'];
        $prdId = $request['member_id'];

        if($prdId == static::Gameplay 
            ||$prdId == static::BBIN
            ||$prdId == static::IBC
            ||$prdId == static::ALLBET
            ||$prdId == static::CQ9
            ||$prdId == static::WM
            ||$prdId == static::Joker
            ||$prdId == static::PSB4D
            ||$prdId == static::Spade
            ||$prdId == static::QQKeno
            ||$prdId == static::CMD
            ||$prdId == static::M8BET
            ||$prdId == static::DIGMAAN
            ||$prdId == static::EBET
            ||$prdId == static::IA
            ||$prdId == static::NLIVE22)
        {
            $balance = GSController::getBalance($prdId,$memberId);

            return $balance;
        }
        else if ($prdId == static::MEGA) 
        {
            $balance = MEGAController::getBalance($memberId);

            if ($balance['success'] == 1) 
            {
                $balance = $balance['balance'];
            }
            else
            {
                $balance = 0;
            }

            return [$prdId => $balance];
        }

    }



    public static function isProductEnable($prdId,$merchantCode)
    {   
        try
        {  
            $db = DB::select('SELECT status
                                FROM product_setting
                                WHERE prd_id = ?
                                    AND merc_cd = ?'
                                    ,[$prdId,$merchantCode]);

           if ($db[0]->status == 1) 
           {
                return true;
           }
           else
           {
                return false;
           }
        } 
        catch(\Exception $e)
        {
            log::debug($e);
            return false;
        }
    }

    //ps9 map to own prdId
    public static function mapPS9Game()
    {
        $game = array(
                    //casino
                    1 => Providers::Ps9EVO   
                    ,5 => Providers::Ps9AG   
                    ,10 => Providers::Ps9PP   
                    ,11 => Providers::Ps9OT   
                    ,100 => Providers::Ps9IA   
                    //slot
                    ,200 => Providers::Ps9PPSlot   
                    ,201 => Providers::Ps9Haba   
                    ,202 => Providers::Ps9Ely   
                    ,204 => Providers::Ps9QS   
                    ,205 => Providers::Ps9SG   
                    ,206 => Providers::Ps9AWS   
                    ,207 => Providers::Ps9PnG   
                    ,208 => Providers::Ps9WM   
                    ,209 => Providers::Ps9Micro   
                    ,210 => Providers::Ps9Joker   
                    ,211 => Providers::Ps9OTSlot   
                    ,213 => Providers::Ps9EvoRtg   
                    ,214 => Providers::Ps9Netent   
                    ,217 => Providers::Ps9Booon   
                    ,218 => Providers::Ps9Playson   
                    ,219 => Providers::Ps9PS   
                );

        return $game;
    }


    // public static function isProductEnableByMember($prdId,$memberId)
    // {   
    //     try
    //     {  
    //         $db = DB::select('SELECT status
    //                             FROM product_setting b
    //                             INNER JOIN member a 
    //                             on a.ag_code = b.tier_code
    //                             WHERE prd_id = ?
    //                                 AND id = ?'
    //                                 ,[$prdId,$memberId]);

    //        if ($db[0]->status == 1) 
    //        {
    //             return true;
    //        }
    //        else
    //        {
    //             return false;
    //        }
    //     } 
    //     catch(\Exception $e)
    //     {
    //         log::debug($e);
    //         return false;
    //     }
    // }
}
