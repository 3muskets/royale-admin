<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\AdminCreditController;
use App\Http\Controllers\Helper;

class AdminCreditViewController extends Controller
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
        Helper::checkUAC('system.accounts.super.admin');

        $availableCredit = AdminCreditController::getCreditBalance();

        return view('admin.admin-credit')->with([
                                                    'availableCredit' => $availableCredit
                                                ]);
    }

    public function update(Request $request)
    {
        Helper::checkUAC('system.accounts.super.admin');

        $data = AdminCreditController::update($request);

        return $data;
    }
}
