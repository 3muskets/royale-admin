<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use DB;
use Auth;
use Log;
use App\Http\Controllers\RebateController;



class RebateViewController extends Controller
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
        Helper::checkUAC('permissions.view_rebate_setting');


        $optionsStatus = RebateController::getOptionsStatus();

        $optionsFrequency = RebateController::getOptionsFrequency();

        return view('bonus.rebate-setting')->with(['optionsStatus' => $optionsStatus,'optionsFrequency' => $optionsFrequency]);

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
