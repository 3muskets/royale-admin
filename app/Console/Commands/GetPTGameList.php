<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JobController;
use Log;

class GetPTGameList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetPTGameList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Playtech Game';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        Log::info('Cron : GetPTGameList START');

        try 
        {
            $url = env('PLAYTECH_API_URL');
            $url = $url.'game/list';
            $key = env('PLAYTECH_SECRET_KEY');
            $brandId = env('PLAYTECH_BRAND_ID');
            $requestId = Helper::generateUniqueId(32);
            $hash = md5($key);

            $data = ['requestId'=>$requestId
                    ,'brandId'=>$brandId
                    ,'size'=>10000];

            $convData = self::convertRawData($data);
            $hashData = md5($convData);
            $url = $url.'?hash='.$hashData;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if ($response['error'] != 0) 
            {
                Log::info('Cron : GetPTGameList Failed');
                Log::debug($response);
            }
            else
            {
                $games = $response['records'];

                foreach ($games as $g) 
                {
                    $gameType = $g['gameType'];
                    $gameCode = $g['gameCode'];
                    $gameNameEn = $g['enName'];
                    $gameNameCn = $g['cnName'];
                    $providerCode = $g['providerCode'];
                    $mobile = $g['mobile'];
                    $desktop = $g['desktop'];
                    $description = (isset($g['description']))?$g['description']:NULL;
                    $imgDefault = $g['imgDefault'];
                    $status = $g['status'];

                    $db = DB::select("SELECT game_code
                                    FROM pt_games
                                    WHERE game_code = ?"
                                    ,[$gameCode]);

                    if (sizeOf($db) == 0) 
                    {
                        DB::insert("INSERT INTO pt_games(game_code,game_type,game_name,game_name_cn,provider_code,mobile,desktop,description,image,status,created_at)
                        VALUES (?,?,?,?,?,?,?,?,?,?,NOW())"
                        ,[$gameCode,$gameType,$gameNameEn,$gameNameCn,$providerCode,$mobile,$desktop,$description,$imgDefault,$status]);
                    }
                    else
                    {
                        DB::update("UPDATE pt_games
                                    SET game_type = ?
                                    ,game_name = ?
                                    ,game_name_cn = ?
                                    ,image = ?
                                    ,updated_at = NOW()
                                    WHERE game_code = ?",
                                    [$gameType,$gameNameEn,$gameNameCn,$imgDefault,$gameCode]);
                    }
                }
            }
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            Log::info('Cron : GetPTGameList Failed');
        }

        Log::info('Cron : GetPTGameList END');
    }

    public static function convertRawData($data)
    {
        try 
        {
            $rawData = '';
            $secret = env('PLAYTECH_SECRET_KEY');
            ksort($data);

            foreach ($data as $key => $value) 
            {
                // $rawKey = strtolower($key);
                $rawValue = $value;

                if ($rawData != '') 
                {
                    $rawData = $rawData.'&';
                }

                $rawData = $rawData.$key.'='.$rawValue;
            }

            return $rawData.$secret;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return '';
        }   
    }
}