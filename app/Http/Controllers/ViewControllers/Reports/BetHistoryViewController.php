<?php
namespace App\Http\Controllers\ViewControllers\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Reports\BetHistoryController;
use App\Http\Controllers\Reports\WinlossDetailsController;
use Log;

class BetHistoryViewController extends Controller
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
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.txn_history_report');

        $optionProduct = WinlossDetailsController::getOptionsProduct();
        
        return view('reports.bet-history')->with(['optionProduct' => $optionProduct]);
    }

    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.txn_history_report');

        $data = BetHistoryController::getList($request);

        return $data;
    }

    public function getDetails(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.txn_history_report');
        
        $data = BetHistoryController::getDetails($request);

        return $data;
    }
}