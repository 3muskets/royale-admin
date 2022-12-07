<?php

namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use Log;
use Hash;

class MEGAController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
    public function __construct(Request $request)
    {
    }

    public function api(Request $request)
    {
    	try
    	{
            $data = substr($request->getContent(), strpos($request->getContent(), "=") + 1); 
            $data = json_decode($data,true);

            log::debug($data);

        	$method = $data['method'];

        	$response = '';

        	if ($method == 'open.operator.user.login') 
        	{
        		$response = self::login($data);
        	}
        	else if ($method == 'open.operator.user.logout') 
        	{
        		$response = self::logout($data);
        	}

        	return $response;
	    }
		catch(\Exception $e)
		{
			log::debug($e);

			$response = ['status' => 0
                    ,'error' => 'UNKNOWN_ERROR'];

            return json_encode($response);
		}
    }

    public static function login($data)
    {
    	try 
    	{
    		$params = $data['params'];
        	$id = $data['id'];
        	$jsonrpc = $data['jsonrpc'];
        	//inside params
        	$random = $params['random'];
        	$loginId = $params['loginId'];
        	$password = $params['password'];
        	$digest = $params['digest'];
        	$sn = $params['sn'];

        	$sessionId = Helper::generateUniqueId(32);

        	$secretCode = env('MEGA_SECRET_CODE');

        	$response = [
            	"id" => $id
            	,"result" => [
            		"success" => 1
            		,"sessionId" => $sessionId
            		,"msg" => 'Success'
            	]
            	,"error" => null
            	,"jsonrpc" => $jsonrpc
            ];

            if (!self::ipRestrict()) 
            {
                $response['result']['success'] = 0;
                $response['result']['msg'] = 'IP_ADDRESS_ERROR';
                log::debug($response);
                

            	return json_encode($response);
            }

        	if (strtoupper(md5($random.$sn.$loginId.$secretCode)) != $digest) 
        	{
                $response['result']['success'] = 0;
                $response['result']['msg'] = 'ACCESS_DENIED';

            	return json_encode($response);
        	}

        	$db = DB::select("SELECT a.password 
        					FROM member a
        					LEFT JOIN mega_users b
        						ON a.id = b.member_id
        					WHERE b.login_id = ?"
        					,[$loginId]);

        	if (sizeof($db) == 0) 
        	{
        		$response['result']['success'] = 0;
                $response['result']['msg'] = 'MEMBER_DOES_NOT_EXISTS';
                log::debug($response);


            	return json_encode($response);
        	}

        	$hashPassword = $db[0]->password;

        	if (!Hash::check($password,$hashPassword)) 
        	{
        		$response['result']['success'] = 0;
                $response['result']['msg'] = 'PASSWORD_INCORRECT';

                log::debug($response);

            	return json_encode($response);
        	}

        	return json_encode($response);
    	} 
    	catch (Exception $e) 
    	{
    		log::debug($e);
    		$response = [
            	"id" => $id
            	,"result" => [
            		"success" => 1
            		,"sessionId" => $sessionId
            		,"msg" => 'Success'
            	]
            	,"error" => null
            	,"jsonrpc" => $jsonrpc
            ];
    		
    		$response['result']['success'] = 0;
            $response['result']['msg'] = 'INTERNAL_ERROR';

        	return json_encode($response);
    	}
    }

    public static function logout($data)
    {
    	try 
    	{
    		$params = $data['params'];
        	$id = $data['id'];
        	$jsonrpc = $data['jsonrpc'];
        	//inside params
        	$random = $params['random'];
        	$loginId = $params['loginId'];
        	$password = $params['password'];
        	$digest = $params['digest'];
        	$sn = $params['sn'];

        	$sessionId = Helper::generateUniqueId(32);

        	$secretCode = env('MEGA_SECRET_CODE');

        	$response = [
            	"id" => $id
            	,"result" => [
            		"success" => 1
            		,"sessionId" => $sessionId
            		,"msg" => 'Success'
            	]
            	,"error" => null
            	,"jsonrpc" => $jsonrpc
            ];

            if (!self::ipRestrict()) 
            {
                $response['result']['success'] = 0;
                $response['result']['msg'] = 'IP_ADDRESS_ERROR';

            	return json_encode($response);
            }

        	if (strtoupper(md5($random.$sn.$loginId.$secretCode)) != $digest) 
        	{
                $response['result']['success'] = 0;
                $response['result']['msg'] = 'ACCESS_DENIED';

            	return json_encode($response);
        	}

        	$db = DB::select("SELECT a.password 
        					FROM member a
        					LEFT JOIN mega_users b
        						ON a.id = b.member_id
        					WHERE b.login_id = ?"
        					,[$loginId]);

        	if (sizeof($db) == 0) 
        	{
        		$response['result']['success'] = 0;
                $response['result']['msg'] = 'MEMBER_DOES_NOT_EXISTS';

            	return json_encode($response);
        	}

        	return json_encode($response);
    	} 
    	catch (Exception $e) 
    	{
    		log::debug($e);
    		$response = [
            	"id" => $id
            	,"result" => [
            		"success" => 1
            		,"sessionId" => $sessionId
            		,"msg" => 'Success'
            	]
            	,"error" => null
            	,"jsonrpc" => $jsonrpc
            ];
    		
    		$response['result']['success'] = 0;
            $response['result']['msg'] = 'INTERNAL_ERROR';

        	return json_encode($response);
    	}
    }

    // public function prepareErrorResponse()
    // {
    // 	$response = [
    //         	"id" => $id
    //         	,"error" => [
    //         		"success" => 1
    //         		,"sessionId" => Helper::generateUniqueId(32)
    //         		,"msg" => 'Success'
    //         	]
    //         	,"error" => null
    //         	,"jsonrpc" => $jsonrpc
    //         ]; 
    // }

    // public function prepareSuccessResponse()
    // {
    // 	$response = [
    //         	"id" => ''
    //         	,"result" => [
    //         		"success" => 1
    //         		,"sessionId" => Helper::generateUniqueId(32)
    //         		,"msg" => 'Success'
    //         	]
    //         	,"error" => null
    //         	,"jsonrpc" => env('MEGA_JSON_RPC')
    //         ]; 

    //     return $response;
    // }

    public static function ipRestrict()
    {
        $ips = explode(",",env('MEGA_IP_ADDRESS'));
        $allowIp = false;

        foreach ($ips as $ip) 
        {
            if ($_SERVER['REMOTE_ADDR'] == $ip) 
            {
                $allowIp = true;
            }
        }

        return $allowIp;
    }
}
