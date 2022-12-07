<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\DownlineController;
use App\Http\Controllers\Helper;
use Auth;

class CreditViewController extends Controller
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
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.view_agent_credit');

        $tier = $request->input('tier');
        $userLevel = Auth::user()->level;

        $upTierUsername1 = '';
        $upTierUsername2 = ''; 
        $upTierUsername3 = ''; 
        $upTier1 = '';
        $upTier2 = '';
        $upTier3 = '';
        $levelByTier = '';

        $checkOwnDownTier = DownlineController::checkIsOwnDownLine($tier,'a');


        if($tier != null && $checkOwnDownTier == true)
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

        return view('credit.credit-list')->with(['tier' => $tier
                                                    ,'userLevel' => $userLevel 
                                                    ,'levelByTier' => $levelByTier
                                                    ,'upTier1' => $upTier1
                                                    ,'upTier2' => $upTier2
                                                    ,'upTier3' => $upTier3
                                                    ,'upTierUsername1' => $upTierUsername1
                                                    ,'upTierUsername2' => $upTierUsername2
                                                    ,'upTierUsername3' => $upTierUsername3
                                                ]);;
    }

    public function getCreditList(Request $request)
    {
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.view_agent_credit');
        
        $data = CreditController::getCreditList($request);

        return $data;
    }

    public function creditTransfer(Request $request)
    {
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.edit_agent_credit');

        $data = CreditController::creditTransfer($request);

        return $data;
    }

    public function memberCredit()
    {
        Helper::checkUAC('permissions.member_credit');
        return view('credit.member-list');
    }

    public function getMemberCreditList(Request $request)
    {
        Helper::checkUAC('permissions.member_credit');

        // Helper::checkUAC('system.accounts.subaccount');

        $data = CreditController::getMemberCreditList($request);

        return $data;
    }

    public function memberCreditTransfer(Request $request)
    {
        // Helper::checkUAC('system.accounts.subaccount');
        
        $data = CreditController::memberCreditTransfer($request);

        return $data;
    }

    // public function multipleMemberCreditTransfer(Request $request)
    // {
        
    //     $data = CreditController::multipleMemberCreditTransfer($request);

    //     return $data;
    // }
}
