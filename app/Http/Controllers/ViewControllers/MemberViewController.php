<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\MemberController;
use App\Http\Controllers\DownlineController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\AdminCreditController;
use App\Http\Controllers\Helper;

use Auth;
use Log;

class MemberViewController extends Controller
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
        $user = Auth::user();
        $level = $user->level;
        $adminId = $user->admin_id;

        if($level != '0')
        {
            $availableCredit = CreditController::getCreditBalance($adminId);
            $availableCurrency = DownlineController::getAvailableCurrency($adminId);
        }
        else
        {
            $availableCredit = AdminCreditController::getCreditBalance();
            $availableCurrency = '';

        }

        $optionsStatus = MemberController::getOptionsStatus();
        $optionsCurrency = MemberController::getOptionsCurrency();


        return view('member.member-new')->with(['optionsStatus' => $optionsStatus
                                                    ,'optionsCurrency' => $optionsCurrency
                                                    ,'availableCredit' => $availableCredit
                                                    ,'availableCurrency' => $availableCurrency
                                                ]);
    }

    public function index()
    {   
        $optionsStatus = MemberController::getOptionsStatus();
        $optionsSuspended = MemberController::getOptionsSuspended();
        
        return view('member.member-list')->with(['optionsStatus' => $optionsStatus
                                                    ,'optionsSuspended' => $optionsSuspended
                                            ]);
    }

    public function create(Request $request)
    {
        $data = MemberController::create($request);

        return $data;
    }

    public function getList(Request $request)
    {
        $data = MemberController::getList($request);

        return $data;
    }

    public function update(Request $request)
    {
        $data = MemberController::update($request);

        return $data;
    }

    public function changePassword(Request $request)
    {
        $data = MemberController::changePassword($request);

        return $data;
    }

    public function checkUser(Request $request)
    {
        $data = MemberController::checkUser($request);

        return $data;
    }

}
