<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Helper;

use Auth;
use Log;

class DownlineSettingController extends Controller
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

    public static function getPtMax($adminId)
    {
        try 
        {
          $db = DB::select("
                    SELECT admin_id
                      ,MAX(CASE WHEN prd_id = 1 THEN pt END) AS evo_pt
                      ,MAX(CASE WHEN prd_id = 2 THEN pt END) AS haba_pt
                      ,MAX(CASE WHEN prd_id = 3 THEN pt END) AS prag_pt
                      ,MAX(CASE WHEN prd_id = 4 THEN pt END) AS wm_pt
                    FROM pt_preset
                    WHERE admin_id =? 
                    GROUP BY admin_id
                    ",[$adminId]);

          $evo = $db[0]->evo_pt;
          $haba = $db[0]->haba_pt;
          $prag = $db[0]->prag_pt;
          $wm = $db[0]->wm_pt;

          return [$evo,$haba,$prag,$wm];
        } 
        catch (\Exception $e) 
        {
          log::debug($e);
          return '';
        }
    }

    public static function calculatePtEff($adminId,$level,$prdId)
    {
      //adminId (can pass either level 1, level 2, level 3)

      $tier3Pt = NULL;
      $tier4Pt = NULL;

      $sql = "
              SELECT d.level, c.admin_id, c.pt, c.prd_id
              FROM admin a 
              LEFT JOIN tiers b
                ON a.id = b.admin_id
              LEFT JOIN pt_preset c 
                ON (c.admin_id = b.admin_id OR c.admin_id = b.up1_tier OR c.admin_id = b.up2_tier) AND c.prd_id = ?
              LEFT JOIN admin d
                ON d.id = c.admin_id
              WHERE (b.admin_id = ? OR b.up1_tier = ? OR b.up2_tier = ?)
                AND a.level = ?
              ORDER BY d.level ASC;
              ";

      $params = [$prdId,$adminId,$adminId,$adminId,$level];
      $db = DB::select($sql,$params);

      if($level == 1)
      {
        $tier2Pt = $db[0]->pt;
        $tier1Pt = 100 - $tier2Pt;
      }
      else if ($level == 2)
      {
        $tier3Pt = $db[1]->pt;
        $tier2Pt = $db[0]->pt - $tier3Pt;
        $tier1Pt = 100 - $tier2Pt - $tier3Pt;
      }
      else if ($level == 3)
      {
        $tier4Pt = $db[2]->pt;
        $tier3Pt = $db[1]->pt - $tier4Pt;
        $tier2Pt = $db[0]->pt - $tier3Pt - $tier4Pt;
        $tier1Pt = 100 - $tier2Pt - $tier3Pt - $tier4Pt;
      }

      DB::insert("INSERT INTO pt_eff (admin_id, prd_id, tier1_pt, tier2_pt, tier3_pt, tier4_pt)
                VALUES(?,?,?,?,?,?)",
      [$adminId,$prdId, $tier1Pt, $tier2Pt, $tier3Pt, $tier4Pt]);


    }

    public static function updatePtEff($adminId,$level,$prdId)
    {
        $tier3Pt = NULL;
        $tier4Pt = NULL;

        $sql = "
                SELECT d.level, c.admin_id, c.pt, c.prd_id
                FROM admin a 
                LEFT JOIN tiers b
                  ON a.id = b.admin_id
                LEFT JOIN pt_preset c 
                  ON (c.admin_id = b.admin_id OR c.admin_id = b.up1_tier OR c.admin_id = b.up2_tier) AND c.prd_id = ?
                LEFT JOIN admin d
                  ON d.id = c.admin_id
                WHERE (b.admin_id = ? OR b.up1_tier = ? OR b.up2_tier = ?)
                  AND a.level = ?
                ORDER BY d.level ASC;
                ";

          $params = [$prdId,$adminId,$adminId,$adminId,$level];
          $db = DB::select($sql,$params);

      if($level == 1)
      {
        $tier2Pt = $db[0]->pt;
        $tier1Pt = 100 - $tier2Pt;
      }
      else if ($level == 2)
      {
        $tier3Pt = $db[1]->pt;
        $tier2Pt = $db[0]->pt - $tier3Pt;
        $tier1Pt = 100 - $tier2Pt - $tier3Pt; 
      }
      else if ($level == 3)
      {
        $tier4Pt = $db[2]->pt;
        $tier3Pt = $db[1]->pt - $tier4Pt;
        $tier2Pt = $db[0]->pt - $tier3Pt - $tier4Pt;
        $tier1Pt = 100 - $tier2Pt - $tier3Pt - $tier4Pt;
      }

      //get all the downline under the adminId (include its self)
      $sql = "
              SELECT a.level, b.admin_id
              FROM admin a 
              LEFT JOIN tiers b
              ON a.id = b.admin_id
              WHERE (b.admin_id = ? OR b.up1_tier = ? OR b.up2_tier = ?)
              ";

      $params = [$adminId,$adminId,$adminId];
      $db = DB::select($sql,$params);

      $adminL1List = [];
      $adminL2List = [];
      $adminL3List = [];

      foreach($db as $d)
      {
        $adminId = $d->admin_id;
        $adminLevel = $d->level;

        if($adminLevel == 1)
        {
           $adminL1List [] = $adminId;
        }
        else if ($adminLevel == 2)
        {
          $adminL2List [] = $adminId;
        }
        else if ($adminLevel == 3)
        {
          $adminL3List [] = $adminId;
        }
      }

      if(sizeof($adminL1List) > 0)
      {
        $sql = "
                UPDATE pt_eff 
                SET tier1_pt = ?, tier2_pt = ?
                WHERE admin_id IN (?) AND prd_id = ?
            ";

        $params = [$tier1Pt,$tier2Pt,$adminL1List,$prdId];

        $pdo = Helper::prepareWhereIn($sql,$params);

        DB::update($pdo['sql'],$pdo['params']);
      }

      if(sizeof($adminL2List) > 0)
      {
        if($level == 1)
        {
          $sql = "
                  UPDATE pt_eff 
                  SET tier1_pt = ?, tier2_pt = ? - tier3_pt
                  WHERE admin_id IN (?) AND prd_id = ?
              ";

          $params = [$tier1Pt,$tier2Pt,$adminL2List,$prdId];
        }
        else if($level == 2)
        { 
          $sql = "
                  UPDATE pt_eff 
                  SET tier2_pt = ?, tier3_pt = ?
                  WHERE admin_id IN (?) AND prd_id = ?
              ";

          $params = [$tier2Pt,$tier3Pt,$adminL2List,$prdId];
        }

        $pdo = Helper::prepareWhereIn($sql,$params);

        DB::update($pdo['sql'],$pdo['params']);

      }
     
      if(sizeof($adminL3List) > 0)
      {
        if($level == 1)
        {
           $sql = "
              UPDATE pt_eff 
              SET tier1_pt = ?, tier2_pt = ? - tier3_pt - tier4_pt
              WHERE admin_id IN (?) AND prd_id = ?
          ";

          $params = [$tier1Pt,$tier2Pt,$adminL3List,$prdId];
        }
        else if($level == 2)
        { 
          $sql = "
                  UPDATE pt_eff 
                  SET tier2_pt = ?, tier3_pt = ? - tier4_pt
                  WHERE admin_id IN (?) AND prd_id = ?
              ";

          $params = [$tier2Pt,$tier3Pt,$adminL3List,$prdId];
        }
        else if($level == 3)
        {
           $sql = "
                  UPDATE pt_eff 
                  SET tier3_pt = ? , tier4_pt = ? 
                  WHERE admin_id IN (?) AND prd_id = ?
              ";

            $params = [$tier3Pt,$tier4Pt,$adminL3List,$prdId];
        }

        $pdo = Helper::prepareWhereIn($sql,$params);

        DB::update($pdo['sql'],$pdo['params']);
      }
    }

}