<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Helper;

use Auth;
use Log;
use Lang;

class ProviderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //TODO enable auth when BO module done
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public static function getGameListHaba(Request $request)
    {
        try
        {
            $hostName = env('HABA_HOSTNAME');
            $brandId = env('HABA_BRAND_ID');
            $apiKey = env('HABA_API_KEY');


            $data = ['BrandId'=>$brandId,
                     'APIKey'=> $apiKey
                    ];

            $url = $hostName;

            $header = ['Content-Type: application/x-www-form-urlencoded'];

            $response = Helper::postData($url,$data,$header);

            $response = json_decode($response);

            $aryGame = $response->{'Games'};

            foreach($aryGame as $game)
            {
                $gameId = $game->KeyName;
                $gameName = $game->Name;
                
                $gameTrans = $game->TranslatedNames;
                $lines = '';

                foreach($gameTrans as $g)
                {
                    if($g->LanguageId == 3)
                        $gameNameCn = $g->Translation;
                }

                $gameType = $game->GameTypeName;
                $theme = $game->GameTypeName;

                if(isset($game->LineDesc))
                {
                    $line = $game->LineDesc;
                }
                
                DB::insert('
                    INSERT INTO haba_games(game_id,game_name,game_name_cn,theme,game_type,line,updated_at)
                    VALUES (?,?,?,?,?,?,NOW())
                    ON duplicate key UPDATE
                        updated_at = NOW()'
                    ,[  $gameId
                        ,$gameName
                        ,$gameNameCn
                        ,$theme
                        ,$gameType
                        ,$line
                        ]);
            }

            return true;

            
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return false;
            
        } 
    }

    public static function getGameListPP(Request $request)
    {
        try
        {
            $hostName = env('PP_HOSTNAME');
            $secureLogin = env('PP_SECURELOGIN');
            $hashKey = env('PP_HASHKEY');

            $method = 'CasinoGameAPI/getCasinoGames';

            $hash = md5('secureLogin='.$secureLogin.$hashKey);
            $method .= '?secureLogin='.$secureLogin.'&hash='.$hash;

            $url = $hostName.$method;

            $header = ['Content-Type: application/x-www-form-urlencoded'];

            $response = Helper::postData($url,'',$header);

            $response = json_decode($response);

            $aryGame = $response->{'gameList'};

            foreach($aryGame as $game)
            {
                $gameId = $game->gameID;
                $gameName = $game->gameName;

                DB::insert('
                    INSERT INTO pp_games(id,game_name,created_at)
                    VALUES (?,?,NOW())
                    ON duplicate key UPDATE
                        updated_at = NOW()'
                    ,[  $gameId
                        ,$gameName]);
            }

            return true;
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return false;
        } 
    }

    public static function getGameListWm(Request $request)
    {
        try
        {
            $hostName = env('WM_SERVERNAME');
            $licensesToken = env('WM_LICENSEE_TOKEN');

            $method = '/platform/feed/games/';

            //EN
            $url = $hostName.$method.$licensesToken.'/?language=EN';

            $header = ['Content-Type: application/x-www-form-urlencoded'];

            $response = Helper::getData($url,$header);

            $response = json_decode($response);

            foreach ($response as $r) 
            {
                $gameName = $r->Name;
                $identity = $r->Identity;

                $identity = explode("-", $identity);

                $gameId = $identity[1];

                $configId = $identity[2];

                $url = $r->ThumbnailUrl;

                $parts = explode("/", $url);

                $path = '/'.$parts[5].'/'.$parts[6];

                DB::insert('
                        INSERT INTO wm_games(game_id,config_id,game_name,game_pic_path,created_at)
                        VALUES (?,?,?,?,NOW())
                        ON duplicate key UPDATE
                            game_name = ?,
                            game_pic_path = ?,
                            updated_at = NOW()'
                        ,[  $gameId
                            ,$configId
                            ,$gameName
                            ,$path
                            ,$gameName
                            ,$path]);
            }


            //ZH
            $url = $hostName.$method.$licensesToken.'/?language=ZH';

            $header = ['Content-Type: application/x-www-form-urlencoded'];

            $response = Helper::getData($url,$header);

            $response = json_decode($response);

            foreach ($response as $r) 
            {
                $gameName = $r->Name;
                $identity = $r->Identity;

                $identity = explode("-", $identity);

                $gameId = $identity[1];

                $configId = $identity[2];

                DB::insert('UPDATE wm_games
                            SET game_name_cn = ?
                            WHERE game_id = ?
                                AND config_id = ?'
                            ,[$gameName,$gameId,$configId]);
            }
                        

            return true;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            return false;
        }
    }
    
        public static function getEvoBetDetail(Request $request)
    {
        try
        {
            $hostName = env('AAS_HOSTNAME');
            $agentCode = env('AAS_AGENT');
            $secretKey = env('AAS_SECRET_KEY');
            $token = env('AAS_TOKEN');

            $prdId = $request->input('prd_id');

            $txnId = $request->input('txn_id');

            // $language = Lang::locale();

            $language = 'en';

            $url = $hostName.'betresults';

            $data = [
                        "lang" => $language,
                        "prd_id" => $prdId,
                        "txn_id" => $txnId
                    ];

            $header = [
                        'Content-Type: application/json',
                        'ag-code: '.$agentCode,
                        'ag-token: '.$token,
                        'secret-key: '.$secretKey
                    ];

            $response = Helper::postData($url,$data,$header);

            return $response;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return false;
        }
    }

    public static function getPPBetDetail(Request $request)
    {
        try
        {
            $hostName = env('PP_HOSTNAME');
            $secureLogin = env('PP_SECURELOGIN');
            $hashKey = env('PP_HASHKEY');

            $method = 'HistoryAPI/OpenHistoryExtended/';

            $memberId = $request->input('member_id');
            $roundId = $request->input('round_id');
            $language = Lang::locale();

            if($language == 'zh-cn')
            {
                $language = 'zh';
            }
    
            $db = DB::SELECT('SELECT username
                        FROM member
                        WHERE id = ?',[$memberId]
                    );

            if(sizeof($db) == 0)
            {
                return '';
            }
            else
            {
                $playerId = $db[0]->username;
            }
    
            $hash = md5('language='.$language.'&playerId='.$playerId.'&roundId='.$roundId.'&secureLogin='.$secureLogin.$hashKey);
            
            $method .= '?secureLogin='.$secureLogin.'&playerId='.$playerId.'&roundId='.$roundId.'&language='.$language.'&hash='.$hash;

            $url = $hostName.$method;

            $header = ['Content-Type: application/x-www-form-urlencoded'];

            $response = Helper::postData($url,'',$header);

            $response = json_decode($response);

            $iframe = $response->{'url'}; 

            return $iframe;
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return '';
        }
    }

    public static function getHabaBetDetail(Request $request)
    {
        try
        {
            $hostName = env('HABA_DOMAINNAME');
            $brandId = env('HABA_BRAND_ID');
            $apiKey = env('HABA_API_KEY');

            $method = '/games/history';
            $language = Lang::locale();
            
            $gameId = $request->input('round_id');

            $apiKey = strtolower($apiKey);

            $hash = hash('sha256',$gameId.$brandId.$apiKey);

            $url = $hostName.$method;



            $url = $url.'?brandId='.$brandId.'&gameinstanceid='.$gameId.'&locale='.$language.'&hash='.$hash;

            return $url;

        }   
        catch(\Exception $e)
        {
            Log::Debug($e);

        }
    }


    public static function getWmBetDetail(Request $request)
    {
        try
        {
            $hostName = env('WM_SERVERNAME');
            $licensesId = env('WM_LICENSEEID');
            $authKey = env('WM_LICENSEE_TOKEN');
            $memberId = $request->input('member_id');
            //game bet id 
            $roundId = $request->input('round_id');
            $gameId = $request->input('game_id');
            $configId = $request->input('config_id');
            $language = Lang::locale();

            if($language == 'zh-cn')
            {
                $language = 'zh';
            }

            $method = '/games/history/'.$licensesId.'/'.$gameId.'/'.$configId.'/';

            $db = DB::SELECT('SELECT a.member_token
                        FROM wm_users a
                        WHERE member_id = ?'
                        ,[$memberId]
                    );

            $authUser = $db[0]->member_token;


            $url = $hostName.$method.'?language='.$language.'&gamebetid='.$roundId.'&authuser='.$authUser.'&authkey='.$authKey.'&display=inline';


            return $url;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
        }
    }
}

