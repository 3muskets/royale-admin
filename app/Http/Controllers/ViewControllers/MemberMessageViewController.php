<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\MemberMessageController;

use Auth;
use Log;

class MemberMessageViewController extends Controller
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
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.member_msg');
        
        return view('member.member-message');
    }

    public function detail()
    {   
        // Helper::checkUAC('system.accounts.member');
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.member_msg');
        return view('member.member-message-detail');
    }


    public function getList(Request $request)
    {
        // Helper::checkUAC('system.accounts.member');
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.member_msg');

        $data = MemberMessageController::getList($request);

        return $data;
    }

    public function getDetail(Request $request)
    {
        // Helper::checkUAC('system.accounts.member');

        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.member_msg');
    
        $data = MemberMessageController::getDetail($request);

        return $data;
    }

    public function updateMsg(Request $request)
    {
        // Helper::checkUAC('system.accounts.member');
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.member_msg');
        $data = MemberMessageController::updateMsg($request);

        return $data;
    }

    public function deleteMsg(Request $request)
    {
        // Helper::checkUAC('system.accounts.member');
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.member_msg');
        $data = MemberMessageController::deleteMsg($request);

        return $data;
    }




}
