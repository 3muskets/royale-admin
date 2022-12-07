<?php
namespace App\Http\Controllers\ViewControllers\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Reports\AgentCreditController;

class AgentCreditViewController extends Controller
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
        Helper::checkUAC('permissions.agent_credit_report');

        return view('reports.agent-credit');
    }

    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.all');
        Helper::checkUAC('permissions.agent_credit_report');
        
        $data = AgentCreditController::getList($request);

        return $data;
    }
}