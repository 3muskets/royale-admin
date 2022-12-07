<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\SubAccountController;
use App\Http\Controllers\Helper;
use Auth;

class SubAccountViewController extends Controller
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
        Helper::checkUAC('system.accounts.subaccount');

        return view('subaccounts.subaccounts');
    }

    public function details(Request $request)
    {
        Helper::checkUAC('system.accounts.subaccount');

        $data = SubAccountController::getSubAccount($request);

        $optionsStatus = SubAccountController::getOptionsStatus();

        return view('subaccounts.subaccounts-details')->with(['data' => $data,'optionsStatus' => $optionsStatus]);

    }

    public function new()
    {
        Helper::checkUAC('system.accounts.subaccount');
        
        return view('subaccounts.subaccounts-new');
    }

}
