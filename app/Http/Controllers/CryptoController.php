<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;

use Auth;
use App;
use Log;
use Lang;

class CryptoController extends Controller
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

    public static function getCryptoSettingDetail()
    {

        $db = DB::select("
            SELECT id 'token_id',token,rate
            FROM crypto_setting
            WHERE id = 1
            "
        );

        return $db;

    }


    public static function updateRate(Request $request)
    {
        try
        {

            $rate = $request->input('crypto_rate');
            $tokenId = $request->input('token_id');
            $user = Auth::user();
            $userId = $user->id;

            //validation
            $errMsg = [];

            if ($rate < 0) 
            {
                array_push($errMsg, __('error.crypto.ratevalue.is_amount'));
            }        


            if($errMsg)
            {
                $response = ['status' => 0
                ,'error' => $errMsg
                ];

                return json_encode($response);

            }   

            $update = DB::update("
                UPDATE crypto_setting
                SET rate = ?,update_by = ?
                WHERE id = ? 
                ",[$rate,$userId,$tokenId]
            );



            $response = ['status' => 1];


            return json_encode($response);

            
        }
        catch(\Exception $e)
        {
            log::debug($e);
            
            $response = ['status' => 0
                        ,'error' => __('error.memberdw.internal_error')
                        ];

            return json_encode($response);
        }
    }


}
