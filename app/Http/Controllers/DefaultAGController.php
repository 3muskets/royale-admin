<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

use Auth;
use Log;

class DefaultAGController extends Controller
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

    public static function getList()
    {
        $db = DB::select('SELECT username, id
                        FROM admin
                        WHERE level = 3 
                        	AND IFNULL(is_sub, 0) = 0
                            AND reg_cd IS NOT NULL
                        ORDER BY is_default DESC'
                    );
           
        if(sizeof($db) == 0)
            return [];
        else
           return $db; 
    }

    public static function update(Request $request)
    {
        
		try 
        {
            $id = $request->input('ag');

            //validation
            $errMsg = [];

            $db = DB::select('SELECT id
                        FROM admin
                        WHERE level = 3 
                        	AND IFNULL(is_sub, 0) = 0
                            AND reg_cd IS NOT NULL
                        	AND id = ?',[$id]
                    );

            if(sizeof($db) == 0)
	        {
	        	array_push($errMsg, __('error.ag.invalid'));
	        }

            if($errMsg)
            {
                $response = ['status' => 0
	                ,'error' => $errMsg
	            ];

	            return json_encode($response);
	        }

	        DB::update('UPDATE admin 
	        			SET is_default = 
	        				CASE WHEN id = ? THEN 1
                            ELSE NULL
                        	END 
                        WHERE level = 3
                        	AND IFNULL(is_sub, 0) = 0'
                        ,[$id]
	        );

	        $response = ['status' => 1];

	        return json_encode($response);

        } 
        catch (Exception $e) 
        {
            $response = ['status' => 0
            ,'error' => __('error.admin.internal_error')
        		];

        	return json_encode($response);
        }
    }
}