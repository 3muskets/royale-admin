<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use App;
use Gate;
use Log;

class Helper extends Controller
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

    public static function checkUAC($module)
    {
        if (Gate::denies($module, auth()->user())) 
        {
            abort(404);
        }
    }

    public static function checkPermissions($type)
    {
        $userId = Auth::user()->id;
        $db = DB::SELECT("
                            SELECT is_deleted
                            FROM permissions
                            WHERE admin_id =? AND type = ? AND is_deleted = 1
                         ", [$userId, $type]
                        );
        if (sizeOf($db) != 0) 
        {
           return false;
        }

        return true;
    }

    public static function checkUserPermissions($type)
    {
        $user = Auth::user();
        $userId = $user->id;

        $db = DB::SELECT("

                            SELECT b.is_deleted 
                            FROM admin_role a 
                            LEFT JOIN role b 
                                ON a.role_id = b.id
                            WHERE a.admin_id = ? AND b.is_deleted = 0
                         ", [$userId]
                        );

        if(sizeof($db) > 0)
        {
             $db = DB::SELECT("

                            SELECT a.is_deleted 
                            FROM role_permissions a 
                            LEFT JOIN admin_role b 
                                ON a.role_id = b.role_id
                            WHERE b.admin_id = ? AND a.type = ? AND a.is_deleted = 1
                         ", [$userId, $type]
                        );

            if (sizeOf($db) != 0) 
            {
               return false;
            }
        }

        return true;
    }



    public static function convertToIndexedArray($data)
    {
        //field name must be "value" and "text"

        try
        {
            $result = [];
            
            foreach($data as $d)
            { 
                array_push($result,[$d->value,$d->text]);
            } 

            return $result;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function convertMultiKeyToSingleIndexArray($data,$key)
    {
       try
        {
            $result = [];
            
            foreach($data as $d)
            { 
                array_push($result,$d->$key);
            } 

            return $result;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault = '')
    {
        $orderTypeAllow = ['asc','desc'];

        $strOrder = '';
        
        if(in_array($orderBy,$orderByAllow))
        {

            if(in_array($orderType,$orderTypeAllow))
            {

                $strOrder = ' '.$orderBy.' '.$orderType;

            }
        }

        if($strOrder == '')
            $strOrder = $orderByDefault;

        if($strOrder != '')
            $strOrder = ' ORDER BY '.$strOrder;

        return $sql.$strOrder;
    }

    public static function appendOrderByTwoValue($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault = '')
    {
        $orderTypeAllow = ['asc','desc'];

        $strOrder = '';

        $firstOrderValue = $orderBy[0];
        $SecondrdeyValue = $orderBy[1];

        if(in_array($firstOrderValue,$orderByAllow) && in_array($SecondrdeyValue,$orderByAllow))
        {

            if(in_array($orderType,$orderTypeAllow))
            {
                $strOrder = $firstOrderValue.','.$SecondrdeyValue.' '.$orderType;

            }
        }

        if($strOrder == '')
            $strOrder = $orderByDefault;

        if($strOrder != '')
            $strOrder = ' ORDER BY '.$strOrder;

        return $sql.$strOrder;
    }

    public static function paginateData($sql,$params,$page,$pageSize=0)
    {
        //pageNo = index 1-based
        //params :pagination_row and :pagination_size : reserved

        if($page == null)
            $page = 1;

        if($pageSize==0)
            $pageSize = env('GRID_PAGESIZE');

        //get data count
        $sqlCount = "SELECT COUNT(0) AS count FROM (".$sql.") AS a";
        $dbCount = DB::select($sqlCount,$params);

        //get data
        $sqlData = $sql." LIMIT :pagination_row,:pagination_size";

        $params['pagination_row'] = (($page - 1) * $pageSize);
        $params['pagination_size'] = $pageSize;

        $dbData = DB::select($sqlData,$params);

        $data = ['count' => $dbCount[0]->count,'page_size' => $pageSize,'results' => $dbData];

        return $data; 
    }

    public static function generateOptions($aryOptions,$default)
    {
        foreach ($aryOptions as $option) 
        {
            $selected = '';

            if($option[0] == $default)
                $selected = 'selected';

            echo '<option value="'.$option[0].'" '.$selected.'>'.$option[1].'</option>';
        }
    }

    public static function checkValidOptions($aryOptions,$value)
    {
        foreach ($aryOptions as $option) 
        {
            if($option[0] == $value)
                return true;
        }

        return false;
    }

    public static function getOptionsValue($aryOptions,$value)
    {
        foreach ($aryOptions as $option) 
        {
            if($option[0] == $value)
                return $option[1];
        }

        return '';
    }

    public static function getLocaleFlag() 
    {
        $localeFlag = array(
                    'en' => 'gb'
                    ,'zh-cn' => 'cn'
                    ,'kr' => 'kr'
                    ,'th' => 'th'
                    ,'vn' => 'vn'
                );

        return $localeFlag[App::getLocale()];
    }

    public static function logAPI($type,$content) 
    {
        //logging for debug
        $db = DB::insert('
            INSERT INTO log_json 
            (type,content)
            VALUES
            (?,?)'
            ,[$type,$content]);
    }

    public static function getData($url,$header = '')
    {
        try
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            
            if($header == '')
            {
                $header = array('Content-Type: application/json');
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function postData($url,$data,$header = '')
    {
        try
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);

            if($header == '')
            {
                $header = array('Content-Type: application/json');
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            if (is_array($data))
            {
                $data = json_encode($data);
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function generateUniqueId($length = 64)
    {
        //minimum length 64

        $length = $length < 64 ? 64 : $length;

        $str = uniqid('',true); //23 char
        $str = md5($str); //32 char

        $str = self::generateRandomString($length - 32).$str;
        return $str;
    }

    public static function generateRandomString($length = 1) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) 
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getTimestamp() 
    {
        $microDate = microtime();
        $aryDate = explode(" ",$microDate);

        $date = date("Y-m-d H:i:s",$aryDate[1]);

        $ms = round($aryDate[0] * 1000);
        $ms = sprintf('%03d', $ms);

        return $date.'.'.$ms;
    }

    public static function formatMoney($money)
    {
        return number_format($money, 2);
    }

    public static function checkInputFormat($type, $data)
    {
        //alphanumeric format
        if($type=='alphanumeric')
        {
            if(preg_match('/[^a-zA-Z0-9]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        //alphabet format
        if($type=='alphabet')
        {
            if(preg_match('/[^a-zA-Z]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //alphabet with space format
        if($type=='alphabetWithSpace')
        {
            if(preg_match('/[0-9\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        //alphanumeric With Dot format
        if($type=='alphanumericWithDot')
        {
            if(preg_match('/[^a-zA-Z\.0-9]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //amount format
        if($type=='amount')
        {
            if(!preg_match('/^\\d+(\\.\\d{1,2})?$/D',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        } 
        //numeric format
        if($type=='numeric')
        {
            if(!preg_match('/^[1-9][0-9\.]*$/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
         //password format
        if($type=='alphanumericwithSymbol')
        {
            if (!preg_match('/^[A-Za-z\.0-9_~\-!@#\$%\^&\*\(\)\_\+\-\=\{\}\[\]\|\\\:\"\;\'\<\>\,\.\?\/]+$/', $data)) 
            {
                return false;
            }
            else
            {
                return true;
            }
        }                          
    }

    public static function checkInputLength($data, $min, $max)
    {
        if(strlen($data)<$min || strlen($data)>$max)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function log(Request $request,$action)
    {
        try
        {
            $userId = Auth::id();
            $ip = \Request::ip();

            $referer = parse_url($request->headers->get('referer'));
            $path = $referer['path'];
            $query = '';

            //data that doesn't being stored in new data
            $except = [
                        '_token'
                        ,'log_old'
                        ,'username'
                        ,'action_details'
                        ,'group_id'
                        ,'merchantcode'
                        ,'merchant'
                        ,'id'
                    ];

            $username = $request->input('username');
            $actionDetails = $request->input('action_details');

            if(array_key_exists('query', $referer))
            {
                $query = $referer['query'];
            }
            else if($request->input('id'))
            {
                $query = "id=".$request->input('id');
            }

            $logOld = $request->input('log_old');
            $logNew = $request->except($except);
            $logNew = json_encode($logNew);

            DB::insert("
                INSERT INTO admin_log(user_id,path,query,action,data_old,data_new,ip_address,username,action_details)
                VALUES
                (?,?,?,?,?,?,?,?,?)
                "
                ,[  $userId
                    ,$path
                    ,$query
                    ,$action
                    ,$logOld
                    ,$logNew
                    ,$ip
                    ,$username
                    ,$actionDetails
                ]);
            
        }
        catch(\Exception $e)
        {
            Log::info($e);
        }
    }

    public static function validurl($uri)
    {
        if(preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$uri))
        {
          return $uri;
        }
        else
        {
            return false;
        }
    }

        public static function prepareWhereIn($sql,$params)
    {
        $returnSql = $sql;
        $returnParams = [];

        $paramCount = 0;

        for($i = 0 ; $i < sizeOf($params) ; $i++)
        {
            if(is_array($params[$i]))
            {
                $explodeParams = str_repeat('?, ', count($params[$i]));
                $explodeParams = rtrim($explodeParams, ', ');

                $pos = self::strposOffset('?', $returnSql, $paramCount + 1);
                
                $returnSql = substr_replace($returnSql,$explodeParams,$pos,1);
                
                for($j = 0 ; $j < sizeOf($params[$i]) ; $j++)
                {
                    array_push($returnParams,$params[$i][$j]);
                    $paramCount++;
                }
            }
            else
            {
                array_push($returnParams,$params[$i]);
                $paramCount++;
            }
        }

        return ['sql' => $returnSql , 'params' => $returnParams];
    }

    public static function prepareBulkInsert($sql,$aryParams)
    {
        //reserved keyword :( and ):
        try
        {
            $returnSQL = '';
            $returnParams = [];

            $valueStart = self::strposOffset(':(', $sql, 1);
            $valueEnd = self::strposOffset('):', $sql, 1);

            $value = substr($sql,$valueStart + 1,$valueEnd - $valueStart);

            $values = str_repeat(','.$value, count($aryParams));
            $values = ltrim($values,',');

            $returnSQL = substr_replace($sql,$values,$valueStart,$valueEnd - $valueStart + 2);

            foreach ($aryParams as $params) 
            {
                foreach ($params as $param) 
                {
                    array_push($returnParams,$param);
                }
            }

            return ['sql' => $returnSQL,'params' => $returnParams];
        }
        catch (\Exception $e) 
        {
            return [];
        }
    }

    public static function strposOffset($search, $string, $offset)
    {
        $arr = explode($search, $string);

        switch($offset)
        {
            case $offset == 0:
            return false;
            break;
        
            case $offset > max(array_keys($arr)):
            return false;
            break;

            default:
            return strlen(implode($search, array_slice($arr, 0, $offset)));
        }
    }

    public static function convertSQLBindingParams($sql,$params)
    {
        //convert sql with params with ? to :
        //reserved binding params key : params_

        $returnSql = $sql;
        $returnParams = [];

        $paramCount = 0;

        for($i = 0 ; $i < sizeOf($params) ; $i++)
        {

            $pos = self::strposOffset('?', $returnSql, $paramCount + 1);
            $returnSql = substr_replace($returnSql,':params_'.$i,$pos,1);

            $returnParams['params_'.$i] = $params[$i];
        }

        return ['sql' => $returnSql , 'params' => $returnParams];
    }

    public static function getTierCodeWithoutSub()
    {
        try
        {   
            $user = Auth::user();

            $userType = $user->type;
            //CA got full access, so no tier code
            if($userType == 'c')
                return '';

            $userId = $user->admin_id;

             $db = DB::select("
                            SELECT username
                            FROM admin
                            WHERE id =?
                         ", [$userId]
                        );

            if (sizeOf($db) == 0) 
            {
               return false;
            }

            $username = $db[0]->username;

            return $username;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function getTierToLoad($tier)
    {
        try
        {   
            $loadIndex = 0;
            $maxIndex = 0;

            $userType = Auth::user()->type;

            if($userType == 'c')
                $maxIndex = 3;

            $checkTier = self::getUserType($tier);

            if($checkTier == 'm')
                $loadIndex = 1;
            else
                $loadIndex = 3;

            if($loadIndex >= $maxIndex)
                $loadIndex = $maxIndex;

            return $loadIndex;
        }
        catch(\Exception $e)
        {
            return 0;
        }
    }

    public static function getUserType($tier)
    {
        try
        {
            $db = DB::select("
                                SELECT type
                                FROM admin
                                WHERE username =?
                             ", [$tier]
                            );

            return $db[0]->type;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function prepareRefId($type)
    {
        try
        {
            
            if ($type == 1) 
            {
                $db['db'] = 'admin_credit_txn';
            }
            elseif ($type == 2) 
            {
                $db['db'] = 'member_credit_txn';
            }

            $id = DB::select("SELECT MAX(txn_id) 'id' FROM ".$db['db']);

            $id = $id[0]->id + 1;

            return $id;

        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public static function validAmount($money)
    {
        if(strlen($money) > 15)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function removePrecision(&$number,$precision = 2)
    {
        //remove unwanted precision without rounding

        try
        {
            $precSize = strlen(explode('.',$number)[1]);

            if($precSize > $precision)
            {
                $numLen = strlen($number) - ($precSize - $precision);

                $number =  floatval(substr($number,0,$numLen));
            }
        }
        catch(\Exception $e)
        {}
    }

    public static function getLevelByTier($id)
    {
        if($id == '')
            return '';
        try
        {
            $db = DB::SELECT('SELECT level FROM admin WHERE id = ?',[$id]);

            if(sizeOf($db) == 0)
            {
                return '';
            }
            else
            {
                return $db[0]->level;
            }
            
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return '';
        }
    }

    public static function getUsernameByTier($id)
    {
        if($id == '')
            return '';
        try
        {
            $db = DB::SELECT('SELECT username FROM admin WHERE id = ?',[$id]);

            if(sizeOf($db) == 0)
            {
                return '';
            }
            else
            {
                return $db[0]->username;
            }
            
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return '';
        }
    }

    public static function getUpperTierUsername($tier)
    {
        if($tier == '')
            return '';
        try
        {
            $db = DB::SELECT('SELECT up1_tier,up2_tier FROM tiers WHERE admin_id = ?',[$tier]);

            if(sizeOf($db) == 0)
            {
                return '';
            }
            else
            {
                $db = DB::SELECT('SELECT username,level,id FROM admin WHERE id in(?,?)',[$db[0]->up1_tier,$db[0]->up2_tier]);

                return $db;
            }
            
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return '';
        }
    }

    public static function isDirectDownline($tier)
    {
        //true if direct downline 
        try
        {   
            $userLevel = Auth::user()->level;

            $db = DB::select('SELECT level FROM admin where id = ?',[$tier]);

            //CA got full access
            if($userLevel == '')
                if($db[0]->level == 1)
                    return true;

            if($userLevel == 1)
                if($db[0]->level == 2)
                    return true;

            if($userLevel == 2)
                if($db[0]->level == 3)
                    return true;

            return false;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public static function getAdminIdByUserId($id)
    {
        try
        {   
            $db = DB::select('
                    SELECT admin_id
                    FROM member 
                    WHERE id = ?',[$id]
                );

            if(sizeof($db) == 0)
            {
                return '';
            }
            else
            {
                return $db[0]->admin_id;
            }

        }
        catch(\Exception $e)
        {
            return '';
        }
    }


}
