<?php

namespace App\Http\Controllers\ViewControllers\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Reports\WinlossDetailsController;
use App\Http\Controllers\Helper;
use App\Http\Controllers\DownlineController;
use Auth;
use Log;

class WinlossDetailsViewController extends Controller
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
  
    public function index(Request $request)
    {
        $tier = $request->input('id');
        $breadcrumbs = [];

        if($tier != null)
        {
            $breadcrumbs = Helper::getBreadcrumbs($tier);
        }

        return view('reports.winloss')->with(['data' => $breadcrumbs]);
    }

    public function agent(Request $request)
    {
        $tier = $request->input('id');

        $startDate = $request->input('s_date');
        $endDate = $request->input('e_date');
        $breadcrumbs = [];

        if($tier != null)
        {
            $breadcrumbs = Helper::getBreadcrumbs($tier);
        }

        return view('reports.winloss-agent')->with(['data' => $breadcrumbs,'start_date' => $startDate,'end_date' => $endDate]);
    }

    public function member(Request $request)
    {
        $tier = $request->input('id');
        $breadcrumbs = [];

        // if($tier != null)
        // {
        //     $breadcrumbs = Helper::getBreadcrumbs($tier);
        // }

        return view('reports.winloss-member')->with(['data' => $breadcrumbs]);
    }

    public function products(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');

        $tier = $request->input('tier');
        $memberId = $request->input('member_id');
        $userLevel = Auth::user()->level;

        return view('reports.winloss-product')->with([
                                            ]);
    }

    public function details(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');

        $tier = "";
        $memberId = $request->input('member_id');
        $userLevel = Auth::user()->level;

        $upTierUsername1 = '';
        $upTierUsername2 = ''; 
        $upTierUsername3 = ''; 
        $upTier1 = '';
        $upTier2 = '';
        $upTier3 = '';
        $levelByTier = '';

        $adminId = Helper::getAdminIdByUserId($memberId);

        $tier = $adminId;

        $checkOwnDownTier = DownlineController::checkIsOwnDownLine($memberId,'m');

        
        if($tier != null && $userLevel != '3' && $checkOwnDownTier == true)
        {
            $levelByTier = Helper::getLevelByTier($tier);

            if($levelByTier != '')
            {
                if($levelByTier - $userLevel == 2)
                {

                $upTierUsername1 = Helper::getUsernameByTier($tier);
                $upTier1 = $tier;
                $getUpperTier = Helper::getUpperTierUsername($tier);

                    foreach($getUpperTier as $d)
                   {
                        if($d->level == 1 + $userLevel)
                        {
                            $upTierUsername2 = $d->username;
                            $upTier2  = $d->id;
                        }

                   }
                }
                else if ($levelByTier - $userLevel == 3)
                {
                   $upTierUsername1 = Helper::getUsernameByTier($tier); 
                   $upTier1 = $tier;
                   $getUpperTier = Helper::getUpperTierUsername($tier);
                   
                   foreach($getUpperTier as $d)
                   {
                        if($d->level == 1)
                        {
                            $upTierUsername3 = $d->username;
                            $upTier3  = $d->id;
                        }
                        else if($d->level == 2)
                        {
                            $upTierUsername2 = $d->username;
                            $upTier2  = $d->id;
                        }
                   }
                }
                else 
                {
                   $upTierUsername1 = Helper::getUsernameByTier($tier); 
                   $upTier1 = $tier;

                }

            }
        }


        return view('reports.winloss-details')->with(['tier' => $tier
                                                ,'userLevel' => $userLevel 
                                                ,'levelByTier' => $levelByTier
                                                ,'upTier1' => $upTier1
                                                ,'upTier2' => $upTier2
                                                ,'upTier3' => $upTier3
                                                ,'upTierUsername1' => $upTierUsername1
                                                ,'upTierUsername2' => $upTierUsername2
                                                ,'upTierUsername3' => $upTierUsername3
                                            ]);
    }

    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');

        $data = WinlossDetailsController::getList($request);

        return $data;
    }

    public function getAgentList(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');

        $data = WinlossDetailsController::getAgentList($request);

        return $data;
    }

    public function getAgentSummary(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');

        $data = WinlossDetailsController::getAgentSummary($request);

        return $data;
    }

    public function getMemberList(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');

        $data = WinlossDetailsController::getMemberList($request);

        return $data;
    }

    public function getProduct(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');

        $data = WinlossDetailsController::getProduct($request);

        return $data;
    }


    public function getDetails(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');

        $data = WinlossDetailsController::getDetails($request);

        return $data;
    }

    public function getResultsBet(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_report');
        
        $data = WinlossDetailsController::getResultsBet($request);

        return $data;
    }
}