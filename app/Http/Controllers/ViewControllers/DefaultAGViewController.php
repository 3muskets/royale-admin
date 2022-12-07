<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\DefaultAGController;

use Auth;
use Log;

class DefaultAGViewController extends Controller
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

        Helper::checkUAC('permissions.view_default_agent');
        
        $agList = DefaultAGController::getList();

        
        return view('settings.default-ag')->with(['agList' => $agList]);
    }

    public function update(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');

        Helper::checkUAC('permissions.edit_default_agent');

        $data = DefaultAGController::update($request);

        return $data;
    }
}
