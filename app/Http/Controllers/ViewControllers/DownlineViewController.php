<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\DownlineController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\AdminCreditController;
use App\Http\Controllers\DownlineSettingController;
use App\Http\Controllers\Helper;
use Auth;
use Log;

class DownlineViewController extends Controller
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
    public function new()
    {   
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.create_downline');

        $optionsStatus = DownlineController::getOptionsStatus();
        $optionsCurrency = DownlineController::getOptionsCurrency();

        $user = Auth::user();
        $level = $user->level;
        $adminId = $user->admin_id;


        return view('downline.downline-new')->with([
                                                    'optionsStatus' => $optionsStatus
                                                    ,'optionsCurrency' => $optionsCurrency
                                                ]);
    }

    public function index(Request $request)
    {   
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.view_downline_list');
        
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

        $optionsStatus = DownlineController::getOptionsStatus();
        $optionsSuspended = DownlineController::getOptionsSuspended();

        return view('downline.downline-list')->with(['tier' => $tier
                                                    ,'userLevel' => $userLevel 
                                                    ,'levelByTier' => $levelByTier
                                                    ,'upTier1' => $upTier1
                                                    ,'upTier2' => $upTier2
                                                    ,'upTier3' => $upTier3
                                                    ,'upTierUsername1' => $upTierUsername1
                                                    ,'upTierUsername2' => $upTierUsername2
                                                    ,'upTierUsername3' => $upTierUsername3
                                                    ,'optionsStatus' => $optionsStatus
                                                    ,'optionsSuspended' => $optionsSuspended
                                                ]);
    }                           


    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.view_downline_list');

        $data = DownlineController::getList($request);

        return $data;
    }


    public function changePassword(Request $request)
    {
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.edit_downline_list');

        $data = DownlineController::changePassword($request);

        return $data;
    }

    public function create(Request $request)
    {
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.create_downline');

        $data = DownlineController::create($request);

        return $data;
    }

    public function update(Request $request)
    {
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.edit_downline_list');

        $data = DownlineController::update($request);

        return $data;
    }

    public function checkUser(Request $request)
    {
        Helper::checkUAC('system.accounts.downline');
        Helper::checkUAC('permissions.create_downline');

        $data = DownlineController::checkUser($request);

        return $data;
    }
}
