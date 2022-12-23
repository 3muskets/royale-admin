<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */




/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
 */
Route::get('/', 'Auth\LoginController@showLoginForm')->name('/');

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('/auth/changepassword', 'ViewControllers\PasswordViewController@changePasswordView')->name('changepassword');

// ajax
Route::post('/ajax/accounts/change_password', 'PasswordController@changePassword');

/*
|--------------------------------------------------------------------------
| Header Routes
|--------------------------------------------------------------------------
 */

//ajax
Route::get('/ajax/header/credit/{admin_id}', 'CreditController@getCreditBalance');
Route::get('/ajax/header/admin/credit', 'AdminCreditController@getCreditBalance');

/*
|--------------------------------------------------------------------------
| Home Routes
|--------------------------------------------------------------------------
 */
Route::get('/home', 'ViewControllers\HomeViewController@index');
Route::get('/ajax/home', 'ViewControllers\HomeViewController@display');

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
 */

Route::get('/dashboard', 'ViewControllers\DashboardViewController@index');

/*
|--------------------------------------------------------------------------
| Admins Routes
|--------------------------------------------------------------------------
 */


Route::get('/admins/admin', 'ViewControllers\AdminViewController@index');
Route::get('/admins/admin/details', 'ViewControllers\AdminViewController@details');
Route::get('/admins/admin/new', 'ViewControllers\AdminViewController@new');

Route::get('/admins/credit', 'ViewControllers\AdminCreditViewController@index');
Route::post('/ajax/admins/credit/update', 'ViewControllers\AdminCreditViewController@update');

//admin roles
Route::get('/admins/roles/new', 'ViewControllers\AdminViewController@newRoles');
Route::get('/admins/roles', 'ViewControllers\AdminViewController@rolesDetails');
Route::get('/admins/roles/permission', 'ViewControllers\AdminViewController@editRoles');

//ajax admin roles
Route::post('/ajax/admins/roles/create', 'ViewControllers\AdminViewController@createRoles');
Route::get('/ajax/admins/roles/list', 'ViewControllers\AdminViewController@getRolesList');
Route::post('/ajax/admins/roles/delete', 'ViewControllers\AdminViewController@deleteRole');
Route::get('/ajax/admins/roles/permission', 'ViewControllers\AdminViewController@getRolesPermission');
Route::post('/ajax/admins/roles/update', 'ViewControllers\AdminViewController@editRolesPermission');



//ajax
Route::get('/ajax/admins/admin/list', 'ViewControllers\AdminViewController@getList');
Route::post('/ajax/admins/admin/update', 'ViewControllers\AdminViewController@update');
Route::post('/ajax/admins/admin/create', 'ViewControllers\AdminViewController@create');
Route::post('/ajax/admins/admin/change_password', 'ViewControllers\AdminViewController@changePassword');
Route::post('/ajax/admins/admin/check_user', 'ViewControllers\AdminViewController@checkUser');


//admin roles
Route::get('/admins/roles/new', 'ViewControllers\AdminViewController@newRoles');
Route::get('/admins/roles', 'ViewControllers\AdminViewController@rolesDetails');
Route::get('/admins/roles/permission', 'ViewControllers\AdminViewController@editRoles');


/*
|--------------------------------------------------------------------------
| Merchants Routes
|--------------------------------------------------------------------------
 */
Route::get('/merchants/merchant/new', 'ViewControllers\DownlineViewController@new');
Route::get('/merchants/merchant', 'ViewControllers\DownlineViewController@index');
Route::get('/merchants/merchant/member', 'ViewControllers\MemberViewController@index');
Route::get('/merchants/merchant/member/new', 'ViewControllers\MemberViewController@new');


//ajax
Route::post('/ajax/merchants/merchant/create', 'ViewControllers\DownlineViewController@create');
Route::post('/ajax/merchants/merchant/member/create', 'ViewControllers\MemberViewController@create');
Route::post('/ajax/merchants/merchant/member/update', 'ViewControllers\MemberViewController@update');
Route::post('/ajax/merchants/merchant/member/change_password', 'ViewControllers\MemberViewController@changePassword');
Route::get('/ajax/merchants/merchant/list', 'ViewControllers\DownlineViewController@getList');
Route::get('/ajax/merchants/merchant/member', 'ViewControllers\MemberViewController@getList');
Route::post('/ajax/merchants/merchant/check_user', 'ViewControllers\DownlineViewController@checkUser');
Route::post('/ajax/merchants/merchant/member/check_user', 'ViewControllers\MemberViewController@checkUser');
Route::post('/ajax/merchants/merchant/update', "DownlineController@update");
Route::post('/ajax/merchants/merchant/change_password', 'ViewControllers\DownlineViewController@changePassword');

/*
|--------------------------------------------------------------------------
| Member Details
|--------------------------------------------------------------------------
 */
Route::get('/merchants/merchant/sma/member', 'ViewControllers\MemberDetailsViewController@index');

//ajax
Route::get('/merchants/merchant/sma/member/list', 'ViewControllers\MemberDetailsViewController@getList');




/*
|--------------------------------------------------------------------------
| Member Level Setting
|--------------------------------------------------------------------------
 */


Route::get('/merchants/merchant/member/levelsetting', 'ViewControllers\MemberDetailsViewController@memberLevelSetting');

Route::get('/ajax/merchants/merchant/memberlevel/settings/list', 'ViewControllers\MemberDetailsViewController@getLevelSettingList');
Route::post('/ajax/merchants/merchant/memberlevel/settings/update', 'ViewControllers\MemberDetailsViewController@updateLevelSetting');


/*
|--------------------------------------------------------------------------
| Member Referral Listing
|--------------------------------------------------------------------------
 */


Route::get('/merchants/merchant/member/referral', 'ViewControllers\MemberDetailsViewController@memberReferList');

Route::get('/ajax/merchants/merchant/referral/list', 'ViewControllers\MemberDetailsViewController@getReferList');




/*
|--------------------------------------------------------------------------
| Credit Routes
|--------------------------------------------------------------------------
 */

Route::get('/merchants/merchant/credit', 'ViewControllers\CreditViewController@index');
Route::get('/merchants/merchant/member/credit', 'ViewControllers\CreditViewController@memberCredit');

//ajax
Route::get('/ajax/merchants/merchant/credit', 'ViewControllers\CreditViewController@getCreditList');
Route::post('/ajax/merchants/merchant/credit_transfer', "ViewControllers\CreditViewController@creditTransfer");

Route::get('/ajax/merchants/merchant/member/credit', 'ViewControllers\CreditViewController@getMemberCreditList');
Route::post('/ajax/merchants/merchant/member/credit_transfer', "ViewControllers\CreditViewController@memberCreditTransfer");
Route::post('/ajax/merchants/merchant/member/all/credit_transfer', "ViewControllers\CreditViewController@multipleMemberCreditTransfer");


/*
|--------------------------------------------------------------------------
| Subaccounts Routes
|--------------------------------------------------------------------------
 */

Route::get('/accounts/subaccounts', 'ViewControllers\SubAccountViewController@index');
Route::get('/accounts/subaccounts/details', 'ViewControllers\SubAccountViewController@details');
Route::get('/accounts/subaccounts/new', 'ViewControllers\SubAccountViewController@new');

//ajax
Route::get('/ajax/accounts/subaccounts/list', 'SubAccountController@getList');
Route::post('/ajax/accounts/subaccounts/update', 'SubAccountController@update');
Route::post('/ajax/accounts/subaccounts/create', 'SubAccountController@create');
Route::post('/ajax/accounts/subaccounts/change_password', 'SubAccountController@changePassword');
Route::post('/ajax/accounts/subaccounts/check_user', 'SubAccountController@checkUser');

/*
|--------------------------------------------------------------------------
| Member deposit/withdraw request
|--------------------------------------------------------------------------
 */

Route::get('/member/dwreq', 'ViewControllers\MemberDWReqViewController@index');

//ajax
Route::get('/ajax/member/dwreq/list', 'ViewControllers\MemberDWReqViewController@getList');
Route::post('/ajax/member/dwreq/approve', 'ViewControllers\MemberDWReqViewController@approve');
Route::post('/ajax/member/dwreq/reject', 'ViewControllers\MemberDWReqViewController@reject');

Route::get('/ajax/member/wallet/balance', 'ViewControllers\MemberDWReqViewController@getWalletBalance');


/*
|--------------------------------------------------------------------------
| Bank Info
|--------------------------------------------------------------------------
 */

Route::get('/banking/bankinfo', 'ViewControllers\BankInfoViewController@index');
Route::get('/bank/bank', 'ViewControllers\BankInfoViewController@bank');
Route::get('/bank/bank/new', 'ViewControllers\BankInfoViewController@createBankIndex');
Route::get('/banking/transfer', 'ViewControllers\BankInfoViewController@transfer');

//ajax
Route::get('/ajax/banking/bankinfo/list', 'ViewControllers\BankInfoViewController@getList');
Route::post('/ajax/banking/bankinfo/update', 'ViewControllers\BankInfoViewController@update');
Route::post('/ajax/banking/bankinfo/credit/transfer', 'ViewControllers\BankInfoViewController@bankCreditTransfer');
Route::get('/ajax/bank/bank/list', 'ViewControllers\BankInfoViewController@getBankList');

Route::post('/ajax/bank/create', 'ViewControllers\BankInfoViewController@createBank');
Route::post('/ajax/bank/update', 'ViewControllers\BankInfoViewController@updateBank');





/*
|--------------------------------------------------------------------------
| Crypto Info
|--------------------------------------------------------------------------
 */

Route::get('/crypto/setting', 'ViewControllers\CryptoViewController@index');


Route::post('/ajax/crypto/update/rate', 'ViewControllers\CryptoViewController@updateRate');

/*
|--------------------------------------------------------------------------
| Bonus
|--------------------------------------------------------------------------
 */

Route::get('/rebate/setting', 'ViewControllers\RebateViewController@index');
Route::get('/rebate/calculate', 'ViewControllers\RebateViewController@calculateRebate');


Route::get('/promo/setting', 'ViewControllers\BonusViewController@promoSetting');

Route::get('/bonus/setting', 'ViewControllers\BonusViewController@bonusSetting');

Route::get('/referral/setting', 'ViewControllers\BonusViewController@referralSetting');


Route::get('/ajax/promo/getList', 'ViewControllers\BonusViewController@getPromoList');
Route::get('/ajax/bonus/getList', 'ViewControllers\BonusViewController@getBonusList');
Route::get('/ajax/referral/getList', 'ViewControllers\BonusViewController@getReferralList');


Route::post('/ajax/promo/setting/update', 'ViewControllers\BonusViewController@updatePromo');
Route::post('/ajax/promo/setting/create', 'ViewControllers\BonusViewController@createPromo');
Route::post('/ajax/bonus/setting/update', 'ViewControllers\BonusViewController@updateBonus');
Route::post('/ajax/referral/setting/update', 'ViewControllers\BonusViewController@updateReferral');



Route::get('/ajax/rebate/setting/detail', 'ViewControllers\RebateViewController@getList');
Route::post('/ajax/rebate/setting/update', 'ViewControllers\RebateViewController@update');




/*
|--------------------------------------------------------------------------
| CashBack
|--------------------------------------------------------------------------
 */

Route::get('/cashback/setting', 'ViewControllers\CashBackSettingViewController@index');

Route::get('/ajax/cashback/setting/detail', 'ViewControllers\CashBackSettingViewController@getList');
Route::post('/ajax/cashback/setting/update', 'ViewControllers\CashBackSettingViewController@update');


/*
|--------------------------------------------------------------------------
| Member Message
|--------------------------------------------------------------------------
 */

Route::get('/member/msg', 'ViewControllers\MemberMessageViewController@index');
Route::get('/member/msg/detail', 'ViewControllers\MemberMessageViewController@detail');
//ajax
Route::get('/ajax/member/message/list', 'ViewControllers\MemberMessageViewController@getList');
Route::get('/ajax/member/message/detail', 'ViewControllers\MemberMessageViewController@getDetail');
Route::post('/ajax/member/message/update', 'ViewControllers\MemberMessageViewController@updateMsg');
Route::post('/ajax/member/message/delete', 'ViewControllers\MemberMessageViewController@deleteMsg');


/*
|--------------------------------------------------------------------------
| Reports Routes
|--------------------------------------------------------------------------
 */

Route::get('/reports/bet_history', 'ViewControllers\Reports\BetHistoryViewController@index');
Route::get('/ajax/reports/bet_history/list', 'ViewControllers\Reports\BetHistoryViewController@getList');
Route::get('/ajax/reports/bet_history/list/details', 'ViewControllers\Reports\BetHistoryViewController@getDetails');

Route::get('/reports/winloss/agent', 'ViewControllers\Reports\WinlossDetailsViewController@agent');
Route::get('/reports/winloss/member', 'ViewControllers\Reports\WinlossDetailsViewController@member');
Route::get('/reports/winloss', 'ViewControllers\Reports\WinlossDetailsViewController@index');
Route::get('/reports/winloss/bet', 'ViewControllers\Reports\WinlossDetailsViewController@bet');

Route::get('/ajax/reports/winloss/agent', 'ViewControllers\Reports\WinlossDetailsViewController@getAgentList');
Route::get('/ajax/reports/winloss/member', 'ViewControllers\Reports\WinlossDetailsViewController@getMemberList');
Route::get('/ajax/reports/winloss/summary', 'ViewControllers\Reports\WinlossDetailsViewController@getAgentSummary');
Route::get('/ajax/reports/winloss/bet', 'ViewControllers\Reports\WinlossDetailsViewController@getBet');
Route::get('/ajax/reports/winloss/bet/details', 'ViewControllers\Reports\WinlossDetailsViewController@getBetDetails');

Route::get('/reports/winloss', 'ViewControllers\Reports\WinlossDetailsViewController@index');
Route::get('/reports/winloss/products', 'ViewControllers\Reports\WinlossDetailsViewController@products');
Route::get('/reports/winloss/details', 'ViewControllers\Reports\WinlossDetailsViewController@details');
Route::get('/ajax/reports/winloss/list', 'ViewControllers\Reports\WinlossDetailsViewController@getList');
Route::get('/ajax/reports/winloss/products', 'ViewControllers\Reports\WinlossDetailsViewController@getProduct');
Route::get('/ajax/reports/winloss/details', 'ViewControllers\Reports\WinlossDetailsViewController@getDetails');
Route::get('/ajax/reports/winloss/products/get_results', 'ViewControllers\Reports\WinlossDetailsViewController@getResultsBet');


Route::get('/reports/winloss_by_product', 'ViewControllers\Reports\WinlossByProductViewController@index');
Route::get('/reports/winloss_by_product/details', 'ViewControllers\Reports\WinlossByProductViewController@details');
Route::get('/ajax/reports/winloss_by_product/list', 'ViewControllers\Reports\WinlossByProductViewController@getList');
Route::get('/ajax/reports/winloss_by_product/details', 'ViewControllers\Reports\WinlossByProductViewController@getDetails');

Route::get('/reports/agent_credit', 'ViewControllers\Reports\AgentCreditViewController@index');
Route::get('/ajax/reports/agent_credit/list', 'ViewControllers\Reports\AgentCreditViewController@getList');

Route::get('/reports/member_credit', 'ViewControllers\Reports\MemberCreditViewController@index');
Route::get('/ajax/reports/member_credit/list', 'ViewControllers\Reports\MemberCreditViewController@getList');



Route::get('/reports/member_referral', 'ViewControllers\Reports\MemberReferralViewController@index');
Route::get('/ajax/reports/member_referral/list', 'ViewControllers\Reports\MemberReferralViewController@getList');


Route::get('/reports/statement/paymentgateway', 'ViewControllers\Reports\PaymentGatewayViewController@index');
Route::get('/ajax/reports/statement/paymentgateway/list', 'ViewControllers\Reports\PaymentGatewayViewController@getList');


Route::get('/reports/promotion', 'ViewControllers\Reports\PromotionViewController@index');
Route::get('/ajax/reports/promotion/list', 'ViewControllers\Reports\PromotionViewController@getList');


/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
 */

Route::get('/cms/banner', 'ViewControllers\CMSViewController@index');
Route::get('/cms/announcement', 'ViewControllers\CMSViewController@indexAnn');
Route::get('/cms/popup', 'ViewControllers\CMSViewController@indexPopup');

Route::get('/ajax/banner/getList', 'ViewControllers\CMSViewController@getMainBannerList');
Route::get('/ajax/announcement/getList', 'ViewControllers\CMSViewController@getAnnouncementList');
Route::get('/ajax/popup/getDetail', 'ViewControllers\CMSViewController@getPopUpDetail');

Route::post('/ajax/cms/banner/create', 'ViewControllers\CMSViewController@createBanner');
Route::post('/ajax/cms/banner/update', 'ViewControllers\CMSViewController@updateBanner');


Route::post('/ajax/cms/announcement/create', 'ViewControllers\CMSViewController@createAnnouncement');
Route::post('/ajax/cms/announcement/update', 'ViewControllers\CMSViewController@updateAnnouncement');

Route::post('/ajax/cms/popup/update', 'ViewControllers\CMSViewController@updatePopup');

/*
|--------------------------------------------------------------------------
| Product Setting
|--------------------------------------------------------------------------
 */

Route::get('/product/setting', 'ViewControllers\ProductSettingViewController@index');

//ajax
Route::get('/ajax/product/list', 'ViewControllers\ProductSettingViewController@getList');
Route::post('/ajax/product/update', 'ViewControllers\ProductSettingViewController@update');



/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
 */

Route::get('/settings/log', 'ViewControllers\LogViewController@index');
Route::get('/ajax/settings/log/list', 'LogController@getList');

Route::get('/settings/defaultag', 'ViewControllers\DefaultAGViewController@index');
Route::post('/ajax/settings/defaultag', 'ViewControllers\DefaultAGViewController@update');

//Game List
Route::get('/settings/gamelist', 'ViewControllers\GameListViewController@index');

Route::get('/ajax/update/gamelist', function () {
	return abort(404);
});
Route::post('/ajax/update/gamelist', 'GameListController@updateGameList');


/*
|--------------------------------------------------------------------------
| Locale Routes
|--------------------------------------------------------------------------
 */

Route::get('/locale', function () {
	return abort(404);
});
Route::post('/locale', 'Locale@setLocale')->name('locale');

/*
|--------------------------------------------------------------------------
| Cookies Routes
|--------------------------------------------------------------------------
 */

Route::get('/cookies/sidebar', function () {
	return abort(404);
});
Route::post('/cookies/sidebar', 'Cookies@setSidebar');