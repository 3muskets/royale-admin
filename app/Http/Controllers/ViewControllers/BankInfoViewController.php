<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\BankInfoController;

use Auth;
use Log;

class BankInfoViewController extends Controller
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
        Helper::checkUAC('permissions.view_banking_acc');

        $optionsStatus = BankInfoController::getOptionsStatus();
        $optionsSuspended = BankInfoController::getOptionsSuspended();

        $optionsBankList = BankInfoController::getOptionBankList();

        $user = Auth::user();
        $level = $user->level;
        
        $count = '';
  
        if($level == 0)
        {
            $count = BankInfoController::countActiveAccount();
        }
        
        return view('downline.bankinfo-list')->with(['optionsStatus' => $optionsStatus, 'optionsSuspended' => $optionsSuspended,'optionsBankList' => $optionsBankList]);
    }

    public function createBankIndex()
    {

        return view('downline.bank-new');
    }


    public function bank()
    {
        $optionsBankStatus = BankInfoController::getOptionsBankStatus();


        return view('downline.bank-list')->with(['optionsBankStatus' => $optionsBankStatus]);
    }


    public function createBank(Request $request)
    {
        $data = BankInfoController::createBank($request);

        return $data;        
    }

    public function updateBank(Request $request)
    {
        $data = BankInfoController::updateBank($request);

        return $data;        
    }

    public function getBankList(Request $request)
    {
        $data = BankInfoController::getBankList($request);

        return $data;        
    }

    public function transfer()
    {
        Helper::checkUAC('system.accounts.admin');

        return view('downline.bank-transfer');
    }

    public function getList(Request $request)
    {

        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.view_banking_acc');

        $data = BankInfoController::getList($request);

        return $data;
    }

    public function update(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');
        
        $data = BankInfoController::update($request);

        return $data;
    }

    public function bankCreditTransfer(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');
        
        $data = BankInfoController::bankCreditTransfer($request);

        return $data;
    }

}
