<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use DB;
use Auth;
use Log;
use App\Http\Controllers\BonusController;



class BonusViewController extends Controller
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
    
    public function promoSetting()
    {   

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_promo');

        $OptionPromoStatus = BonusController::getOptionPromoStatus();

        return view('bonus.promotion-setting')->with(['OptionPromoStatus' => $OptionPromoStatus]);;

    }

    public function bonusSetting()
    {
        $optionsCategory = BonusController::getOptionCategory();

        return view('bonus.bonus-setting')->with(['optionsCategory' => $optionsCategory]);
    }



    public function referralSetting()
    {

        return view('bonus.referral-setting');
    }



    public function getPromoList(Request $request)
    {

        $data = BonusController::getPromoList($request);
        return $data;
    }

    public function getBonusList(Request $request)
    {

        $data = BonusController::getBonusList($request);
        return $data;
    }
    
    public function getReferralList(Request $request)
    {

        $data = BonusController::getReferralList($request);
        return $data;
    }

    public function createPromo(Request $request)
    {
        $data = BonusController::createPromo($request);
        return $data;
    }

    public function updatePromo(Request $request)
    {

        $data = BonusController::updatePromo($request);
        return $data;
    }


    public function updateBonus(Request $request)
    {

        $data = BonusController::updateBonus($request);
        return $data;
    }
    
    public function updateReferral(Request $request)
    {

        $data = BonusController::updateReferral($request);
        return $data;
    }



    public function calculateRebate()
    {
        return view('bonus.calculate-rebate');
    }

    public function getList(Request $request)
    {

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_rebate_setting');

        $data = RebateController::getList($request);


        return $data;
    }


    public function update(Request $request)
    {

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.edit_rebate_setting');

        $data = RebateController::update($request);
        
        return $data;
    }
}
