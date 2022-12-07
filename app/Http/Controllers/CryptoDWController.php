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

class CryptoDWController extends Controller
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

    public static function createAccessToken($data)
    {
        $key = env('CRYPTO_KEY');
        $sign_str = '';

        ksort($data);

        foreach($data as $k=>$v)
        {
            if($k!=="access_tonken")
                $sign_str .= $k."|".$v;
        }

        return MD5($sign_str.$key);
    }

    public static function createUser($userId)
    {
        try
        {
            $url = env('CRYPTO_URL');
            $method = 'walletopen.create_user';
            $time = time();

            $data = array(
                "access_tonken" => "",
                "method" => $method,
                "time" => $time,
                "out_user_id" => $userId,
                );

            //create access Token
            $accessTonken = self::createAccessToken($data);

            $data['access_tonken'] = $accessTonken;

            $str = "";
            foreach ($data as $key => $val) 
            {
                $str = $str . $key . "=" . $val . "&";
            }

            $createUserUrl = $url.'?'.$str;

            $response = Helper::getData($createUserUrl);
            $response = json_decode($response,true);

            if ($response['status'] == 1) 
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return false;
        }
    }

    public static function getRate()
    {
        try
        {
            $url = env('CRYPTO_URL');
            $method = 'walletopen.get_rate';
            $time = time();

            $data = array(
                "access_tonken" => "",
                "method" => $method,
                "time" => $time,
                );

            //create access Token
            $accessTonken = self::createAccessToken($data);

            $data['access_tonken'] = $accessTonken;

            $str = "";
            foreach ($data as $key => $val) 
            {
                $str = $str . $key . "=" . $val . "&";
            }

            $getRateUrl = $url.'?'.$str;

            $response = Helper::getData($getRateUrl);
            $response = json_decode($response,true);

            if ($response['status'] == 1) 
            {
                return $response['data'];
            }
            else
            {
                return '';
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return '';
        }
    }

    public static function withdraw($amount,$address,$userId,$orderId)
    {
        DB::beginTransaction();

        try
        {
            //withdraw credential
            $url = env('CRYPTO_URL');
            $method = 'walletopen.create_withdraw_order';
            $time = time();

            $createUser = self::createUser($userId);

            if (!$createUser) 
            {
                return false;
            }

            $rate = self::getRate();

            if (!self::getRate()) 
            {
                return false;
            }

            $tokenAmount = $amount/$rate;

            $data = array(
                "access_tonken" => "",
                "coin_name" => "USDT",
                "method" => $method,
                "token_amount" => $tokenAmount,
                "usdt_rate" => $rate,
                "usd_amount" => $amount,
                "time" => $time,
                "out_user_id" => $userId,
                "out_order_id" => $orderId,
                "out_address" => $address,
                );

            //create access Token
            $accessTonken = self::createAccessToken($data);

            $data['access_tonken'] = $accessTonken;

            $str = "";
            foreach ($data as $key => $val) 
            {
                $str = $str . $key . "=" . $val . "&";
            }

            $withdrawUrl = $url.'?'.$str;

            $response = Helper::getData($withdrawUrl);
            $response = json_decode($response,true);

            if ($response['status'] == 1) 
            {
                $withdrawOrderId = $response['data']['withdraw_order_id'];

                DB::update("UPDATE member_crypto_dw
                            SET ref_id = ?
                            WHERE id = ?"
                            ,[$withdrawOrderId,$orderId]);

                DB::commit();

                return true;
            }
            else
            {
                DB::rollback();

                return false;
            }
        }
        catch(\Exception $e)
        {
            DB::rollback();

            log::debug($e);
            return false;
        }
    }
}
