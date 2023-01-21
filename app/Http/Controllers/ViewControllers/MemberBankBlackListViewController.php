<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\MemberBankBlackListController;

use Auth;
use Log;

class MemberBankBlackListViewController extends Controller
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
        
        return view('member.member-bank-blacklist');
    }


    public function getList(Request $request)
    {

        $data = MemberBankBlackListController::getList($request);

        return $data;
    }

 
    public function add(Request $request)
    {

        $data = MemberBankBlackListController::add($request);

        return $data;
    }
 
    public function delete(Request $request)
    {

        $data = MemberBankBlackListController::delete($request);

        return $data;
    }

}
