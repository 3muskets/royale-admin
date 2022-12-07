<?php
namespace App\Http\Controllers\ViewControllers\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Reports\PaymentGatewayController;

class PaymentGatewayViewController extends Controller
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

        return view('reports.statement-paymentgateway');
    }

    public function getList(Request $request)
    {
        
        $data = PaymentGatewayController::getList($request);

        return $data;
    }
}