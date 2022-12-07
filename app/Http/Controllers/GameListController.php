<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ProviderContoller;

use Auth;
use Log;

class GameListController extends Controller
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

    public static function updateGameList(Request $request)
    {
        Helper::checkUAC('system.accounts.admin');
        
        $prdId = $request->input('prd_id');

        if($prdId == 2)
        {
            return self::updateHabaGameList($request);
        }
        else if($prdId == 3){
            return self::updatePragGameList($request);
        }
        else if($prdId == 4)
        {
            return self::updateWmGameList($request);
        }
        else
        {
            $response = ["error" => "No Record", "status" => 0];    

            return json_encode($response); 
        }
    }

    public static function updateHabaGameList(Request $request)
    {
        try
        {
            $updateHabaList = ProviderController::getGameListHaba($request);

            if($updateHabaList == true)
            {
               $response =  ["msg" => "Update Success", 
                            "status" => 1];   
            }
            else
            {
                $response = ["error" => __('error.gamelist.fail'),
                             "status" => 0];  
            }

            return json_encode($response); 
        }
        catch(\Exception $e)
        {

            $response = ["error" => __('error.gamelist.internal_error'),
                         "status" => 0];    

            return json_encode($response); 
        }
    }

    public static function updatePragGameList(Request $request)
    {
        try
        {
    
            $updatePPList = ProviderController::getGameListPP($request);
            
            if($updatePPList == true)
            {
               $response =  ["msg" => "Update Success",
                            "status" => 1];   
            }
            else
            {
                $response = ["error" => __('error.gamelist.fail'), 
                            "status" => 0];  
            }

            return json_encode($response); 
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            $response = ["error" => __('error.gamelist.internal_error'),
                         "status" => 0];    

            return json_encode($response); 
        }
    }

    public static function updateWmGameList(Request $request)
    {
        try
        {
            $updateWmList = ProviderController::getGameListWm($request);

            if($updateWmList == true)
            {
               $response =  ["msg" => "Update Success", 
                            "status" => 1];   
            }
            else
            {
                $response = ["error" => __('error.gamelist.fail'),
                             "status" => 0];  
            }  

            return json_encode($response); 
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            $response = ["error" => __('error.gamelist.internal_error'),
                        "status" => 0];    

            return json_encode($response); 
        }
    }

}