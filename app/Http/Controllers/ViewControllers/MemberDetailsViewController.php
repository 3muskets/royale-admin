<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\MemberDetailsController;

use Auth;
use Log;

class MemberDetailsViewController extends Controller
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
    
    public function index()
    {   
        Helper::checkUAC('system.accounts.sma');
        
        return view('member.member-details-list');
    }


    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.sma');

        $data = MemberDetailsController::getList($request);

        return $data;
    }

    public function memberLevelSetting()
    {   
        
        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_member_levelsetting');


        return view('member.memberlevel-setting');
    }


    public function memberReferList()
    {
        return view('member.member-referral-list');
    }



    public function getLevelSettingList(Request $request)
    { 

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_member_levelsetting');

        $data = MemberDetailsController::getLevelSettingList($request);

        return $data;
    }

    public function getReferList(Request $request)
    { 

        $data = MemberDetailsController::getReferList($request);

        return $data;
    }


    public function updateLevelSetting(Request $request)
    {

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.edit_member_levelsetting');

        $data = MemberDetailsController::updateLevelSetting($request);

        return $data;
    }


}
