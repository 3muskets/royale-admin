<?php

namespace App\Http\Controllers\ViewControllers\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Reports\WinlossByProductController;
use App\Http\Controllers\Helper;
use App\Http\Controllers\DownlineController;
use App\Http\Controllers\Reports\WinlossDetailsController;
use Auth;
use Log;

class WinlossByProductViewController extends Controller
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
    public function index(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_by_product_report');


        $optionProduct = WinlossDetailsController::getOptionsProduct();
        

        $tier = $request->input('tier');
        $userLevel = Auth::user()->level;
      
        return view('reports.winlossby-product')->with(['tier' => $tier
                                                ,'userLevel' => $userLevel 
                                                ,'optionProduct' =>$optionProduct

                                            ]);
    }


    public function details(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_by_product_report');

        $userLevel = Auth::user()->level;

        $optionProduct = WinlossDetailsController::getOptionsProduct();
      
        return view('reports.winlossby-product-details')->with(['userLevel' => $userLevel 
                                                                ,'optionProduct' =>$optionProduct
                                            ]);


    }

    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_by_product_report');

        $data = WinlossByProductController::getList($request);

        return $data;
    }

    public function getDetails(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_by_product_report');

        $data = WinlossByProductController::getDetails($request);

        return $data;
    }

    public function getResultsBet(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.win_loss_by_product_report');
        
        $data = WinlossDetailsController::getResultsBet($request);

        return $data;
    }
}