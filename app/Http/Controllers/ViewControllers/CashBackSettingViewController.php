<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\CashBackSettingController;
use DB;
use Auth;
use Log;




class CashBackSettingViewController extends Controller
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

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_cashback_setting');
        
     
        $optionsStatus = CashBackSettingController::getOptionsStatus();
        $optionsFrequency = CashBackSettingController::getOptionsFrequency();

        return view('bonus.cashback-setting')->with(['optionsStatus' => $optionsStatus,'optionsFrequency' => $optionsFrequency]);

    }

    public function getList(Request $request)
    {

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_cashback_setting');

        $data = CashBackSettingController::getList($request);

        return $data;
    }



    public function update(Request $request)
    {

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.edit_cashback_setting');

        $data = CashBackSettingController::update($request);
        
        return $data;
    }
}
