<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//***********************************
//  SBO
//***********************************

//balance
Route::get('/sbo/create-agent', function(){return abort(404);});
Route::post('/sbo/create-agent', 'Providers\SBOController@createAgent');

//debit
Route::get('/sbo/GetBalance', function(){return abort(404);});
Route::post('/sbo/GetBalance', 'Providers\SBOController@getBalance');

//debit
Route::get('/sbo/Deduct', function(){return abort(404);});
Route::post('/sbo/Deduct', 'Providers\SBOController@debit');

//credit
Route::get('/sbo/Settle', function(){return abort(404);});
Route::post('/sbo/Settle', 'Providers\SBOController@credit');


Route::get('/sbo/Rollback', function(){return abort(404);});
Route::post('/sbo/Rollback', 'Providers\SBOController@rollback');


Route::get('/sbo/Cancel', function(){return abort(404);});
Route::post('/sbo/Cancel', 'Providers\SBOController@cancel');

 
Route::get('/sbo/Tip', function(){return abort(404);});
Route::post('/sbo/Tip', 'Providers\SBOController@tip');

Route::get('/sbo/Bonus', function(){return abort(404);});
Route::post('/sbo/Bonus', 'Providers\SBOController@bonus');

Route::get('/sbo/GetBetStatus', function(){return abort(404);});
Route::post('/sbo/GetBetStatus', 'Providers\SBOController@getBetStatus');

Route::get('/sbo/LiveCoinTransaction', function(){return abort(404);});
Route::post('/sbo/LiveCoinTransaction', 'Providers\SBOController@liveCoinTransaction');

Route::get('/sbo/ReturnStake', function(){return abort(404);});
Route::post('/sbo/ReturnStake', 'Providers\SBOController@returnStake');

//***********************************
//  EVO
//***********************************

Route::get('/evo/check', function(){return abort(404);});
Route::post('/evo/check', 'Providers\EVOController@checkUser');

Route::get('/evo/balance', function(){return abort(404);});
Route::post('/evo/balance', 'Providers\EVOController@getBalance');

Route::get('/evo/debit', function(){return abort(404);});
Route::post('/evo/debit', 'Providers\EVOController@debit');

Route::get('/evo/credit', function(){return abort(404);});
Route::post('/evo/credit', 'Providers\EVOController@credit');

Route::get('/evo/cancel', function(){return abort(404);});
Route::post('/evo/cancel', 'Providers\EVOController@cancel');

//***********************************
//  IBC
//***********************************

Route::get('/ibc/confirmbet', function(){return abort(404);});
Route::post('/ibc/confirmbet', 'Providers\IBCController@confirmbet');

Route::get('/ibc/getbalance', function(){return abort(404);});
Route::post('/ibc/getbalance', 'Providers\IBCController@getBalance');

Route::get('/ibc/placebet', function(){return abort(404);});
Route::post('/ibc/placebet', 'Providers\IBCController@placebet');

Route::get('/ibc/settle', function(){return abort(404);});
Route::post('/ibc/settle', 'Providers\IBCController@credit');

Route::get('/ibc/cancelbet', function(){return abort(404);});
Route::post('/ibc/cancelbet', 'Providers\IBCController@cancel');

Route::get('/ibc/resettle', function(){return abort(404);});
Route::post('/ibc/resettle', 'Providers\IBCController@resettle');

Route::get('/ibc/unsettle', function(){return abort(404);});
Route::post('/ibc/unsettle', 'Providers\IBCController@unsettle');

Route::get('/ibc/placebetparlay', function(){return abort(404);});
Route::post('/ibc/placebetparlay', 'Providers\IBCController@placebetparlay');

Route::get('/ibc/confirmbetparlay', function(){return abort(404);});
Route::post('/ibc/confirmbetparlay', 'Providers\IBCController@confirmbetparlay');

Route::get('/ibc/adjustbalance', function(){return abort(404);});
Route::post('/ibc/adjustbalance', 'Providers\IBCController@adjustbalance');

//***********************************
//  Joker
//***********************************

//get game list
Route::get('/joker/get-game-list', function(){return abort(404);});
Route::post('/joker/get-game-list', 'Providers\JokerController@getGameList');

//open game
Route::get('/joker/open-game', function(){return abort(404);});
Route::post('/joker/open-game', 'Providers\JokerController@openGame');

//auth token
Route::get('/joker/authenticate-token', function(){return abort(404);});
Route::post('/joker/authenticate-token', 'Providers\JokerController@authToken');

//balance
Route::get('/joker/balance', function(){return abort(404);});
Route::post('/joker/balance', 'Providers\JokerController@balance');

//deposit
Route::get('/joker/deposit', function(){return abort(404);});
Route::post('/joker/deposit', 'Providers\JokerController@deposit');

//withdraw
Route::get('/joker/withdraw', function(){return abort(404);});
Route::post('/joker/withdraw', 'Providers\JokerController@withdraw');

//debit
Route::get('/joker/bet', function(){return abort(404);});
Route::post('/joker/bet', 'Providers\JokerController@debit');

//credit
Route::get('/joker/settle-bet', function(){return abort(404);});
Route::post('/joker/settle-bet', 'Providers\JokerController@credit');

//cancel
Route::get('/joker/cancel-bet', function(){return abort(404);});
Route::post('/joker/cancel-bet', 'Providers\JokerController@cancel');

Route::get('/joker/bonus-win', function(){return abort(404);});
Route::post('/joker/bonus-win', 'Providers\JokerController@bonusWin');

Route::get('/joker/jackpot-win', function(){return abort(404);});
Route::post('/joker/jackpot-win', 'Providers\JokerController@jackpotWin');

Route::get('/joker/transaction', function(){return abort(404);});
Route::post('/joker/transaction', 'Providers\JokerController@transaction');

//***********************************
//  SA Gaming
//***********************************
//balance
Route::get('/sagaming/GetUserBalance', function(){return abort(404);});
Route::post('/sagaming/GetUserBalance', 'Providers\SAController@balance');

//debit
Route::get('/sagaming/PlaceBet', function(){return abort(404);});
Route::post('/sagaming/PlaceBet', 'Providers\SAController@debit');

//credit
Route::get('/sagaming/PlayerWin', function(){return abort(404);});
Route::post('/sagaming/PlayerWin', 'Providers\SAController@creditWin');

//cancel
Route::get('/sagaming/PlayerLost', function(){return abort(404);});
Route::post('/sagaming/PlayerLost', 'Providers\SAController@creditLose');

Route::get('/sagaming/PlaceBetCancel', function(){return abort(404);});
Route::post('/sagaming/PlaceBetCancel', 'Providers\SAController@cancel');

//***********************************
//  PLAYTECH
//***********************************

Route::post('/playtech/backUrl', function(){return abort(404);});
Route::get('/playtech/backUrl', 'Providers\PTController@backUrl');

Route::get('/playtech/auth', function(){return abort(404);});
Route::post('/playtech/auth', 'Providers\PTController@auth');

Route::get('/playtech/balance', function(){return abort(404);});
Route::post('/playtech/balance', 'Providers\PTController@balance');

Route::get('/playtech/transaction', function(){return abort(404);});
Route::post('/playtech/transaction', 'Providers\PTController@transaction');

Route::get('/playtech/payUp', function(){return abort(404);});
Route::post('/playtech/payUp', 'Providers\PTController@payUp');

//***********************************
//  MEGA
//***********************************

//balance
Route::get('/mega', function(){return abort(404);});
Route::post('/mega', 'Providers\MEGAController@api');
