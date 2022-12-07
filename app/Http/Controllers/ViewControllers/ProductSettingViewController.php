<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\ProductSettingController;
use App\Http\Controllers\Helper;

class ProductSettingViewController extends Controller
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

        return view('settings.product-setting');
    }

    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.super.admin');

        $data = ProductSettingController::getList($request);

        return $data;
    }

    public function update(Request $request)
    {
        Helper::checkUAC('system.accounts.super.admin');

        $data = ProductSettingController::update($request);

        return $data;
    }

}
