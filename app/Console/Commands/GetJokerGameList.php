<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JobController;
use Log;

class GetJokerGameList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetJokerGameList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Joker Game';

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
        Log::info('Cron : GetJokerGameList START');

        try 
        {
            $url = env('JOKER_API_URL');
            $url = $url.'list-games';
            $appId = env('JOKER_APPID');
            $timestamp = time();

            $data = [
                        'AppID' => $appId,
                        'Timestamp' => $timestamp
                    ];

            $convData = self::convertRawData($data);
            $hashData = md5($convData);
            $data['Hash'] = $hashData;
            ksort($data);

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if ($response['Error'] != 0) 
            {
                Log::info('Cron : GetJokerGameList Failed');
                Log::debug($response);
            }
            else
            {
                $games = $response['ListGames'];

                foreach ($games as $g) 
                {
                    $gameType = $g['GameType'];
                    $gameCode = $g['GameCode'];
                    $gameName = $g['GameName'];
                    $specials = $g['Specials'];
                    $image = $g['Image1'];

                    $db = DB::select("SELECT game_code
                                    FROM joker_games
                                    WHERE game_code = ?"
                                    ,[$gameCode]);

                    if (sizeOf($db) == 0) 
                    {
                        DB::insert("INSERT INTO joker_games(game_code,game_type,game_name,specials,image)
                        VALUES (?,?,?,?,?)"
                        ,[$gameCode,$gameType,$gameName,$specials,$image]);
                    }
                    else
                    {
                        DB::update("UPDATE joker_games
                                    SET game_type = ?
                                    ,game_name = ?
                                    ,specials = ?
                                    ,image = ?
                                    ,updated_at = NOW()
                                    WHERE game_code = ?",
                                    [$gameType,$gameName,$specials,$image,$gameCode]);
                    }
                }

                Log::info('Cron : GetJokerGameList Success');
            }

            return $response;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
        }

        Log::info('Cron : GetJokerGameList END');
    }

    public static function convertRawData($data)
    {
        try 
        {
            $rawData = '';
            $secret = env('JOKER_SECRET');
            ksort($data);

            foreach ($data as $key => $value) 
            {
                $rawKey = strtolower($key);
                $rawValue = $value;

                if ($rawData != '') 
                {
                    $rawData = $rawData.'&';
                }

                $rawData = $rawData.$rawKey.'='.$rawValue;
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