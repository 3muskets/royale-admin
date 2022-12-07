<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\CryptoController;

use Auth;
use Log;

class CryptoViewController extends Controller
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
        Helper::checkUAC('permissions.view_crypto_setting');


        $user = Auth::user();
        $level = $user->level;
        $userId = $user->admin_id;

        $detail = CryptoController::getCryptoSettingDetail();        


        return view('crypto.crypto-setting')->with(['cryptoDetail'=>$detail]);
    }


    public function updateRate(Request $request)     
    {
        Helper::checkUAC('system.accounts.admin');
        Helper::checkUAC('permissions.edit_crypto_setting');

       
        
        $data = CryptoController::updateRate($request);

        return $data;
    }


}
