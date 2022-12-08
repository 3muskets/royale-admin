<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\MemberDWController;

use Auth;
use Log;

class MemberDWReqViewController extends Controller
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
        // Helper::checkUAC('system.accounts.member');
        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_dw_request');  


        return view('member.member-dwreq-list');
    }

    public function getList(Request $request)
    {
        // Helper::checkUAC('system.accounts.member');

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_dw_request');  

        $data = MemberDWController::getList($request);

        return $data;
    }

    public function approve(Request $request)
    {
        // Helper::checkUAC('system.accounts.member');

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_dw_request');  

        $data = MemberDWController::approve($request);

        return $data;
    }

    public function reject(Request $request)
    {
        // Helper::checkUAC('system.accounts.member');
        
        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_dw_request');  

        $data = MemberDWController::reject($request);

        return $data;
    }


    public function getWalletBalance(Request $request)
    {

        Helper::checkUAC('system.accounts.admin');

        $data = MemberDWController::getWalletBalance($request);

        return $data;

    }
}
