<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Http\Controllers\Helper;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('system.accounts.downline', function ($user) 
        {
            return $user->level != '3' ?true : false;
        });

        Gate::define('system.accounts.subaccount', function ($user) 
        {
            return $user->level != '0' && $user->is_sub == 0 ? true : false;
        });

        Gate::define('system.accounts.admin', function ($user) 
        {
            return $user->level == '0' && $user->is_sub == 0 ? true : false;
        });

        Gate::define('system.accounts.member', function ($user) 
        {
           return $user->level == '3' ?  true : false;

        });

        Gate::define('system.accounts.sma', function ($user) 
        {
            return $user->level == '1' ? true : false;
        });


        Gate::define('system.accounts.super.admin', function ($user) 
        {
           return $user->level == '0' && $user->super_admin == 1 ?  true : false;

        });


        Gate::define('system.accounts.all', function ($user) 
        {
           return $user->level == '0' || $user->level == '1' ||  $user->level == '2' || $user->level == '3' ?  true : false;

        });


        Gate::define('permissions.create_downline', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('create_downline'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.view_downline_list', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_downline_list'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.edit_downline_list', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('edit_downline_list'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.view_agent_credit', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_agent_credit'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.edit_agent_credit', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('edit_agent_credit'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.member_credit', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('member_credit'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.view_member_list', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_member_list'))
                return true;
            else
                return false;

        });


        Gate::define('permissions.view_member_levelsetting', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_member_levelsetting'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.edit_member_levelsetting', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('edit_member_levelsetting'))
                return true;
            else
                return false;

        });


        Gate::define('permissions.view_banking_acc', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_banking_acc'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.view_dw_request', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_dw_request'))
                return true;
            else
                return false;

        });



        

        Gate::define('permissions.member_msg', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('member_msg'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.view_crypto_setting', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_crypto_setting'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.edit_crypto_setting', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('edit_crypto_setting'))
                return true;
            else
                return false;

        });



        Gate::define('permissions.view_rebate_setting', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_rebate_setting'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.edit_rebate_setting', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('edit_rebate_setting'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.view_cashback_setting', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_cashback_setting'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.edit_cashback_setting', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('edit_cashback_setting'))
                return true;
            else
                return false;

        });


        Gate::define('permissions.txn_history_report', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('txn_history_report'))
                return true;
            else
                return false;

        });


        Gate::define('permissions.win_loss_report', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('win_loss_report'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.win_loss_by_product_report', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('win_loss_by_product_report'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.agent_credit_report', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('agent_credit_report'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.member_credit_report', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('member_credit_report'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.member_referral_report', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('member_referral_report'))
                return true;
            else
                return false;

        });


        Gate::define('permissions.create_admin', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('create_admin'))
                return true;
            else
                return false;

        });

        Gate::define('permissions.view_admin_list', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_admin_list'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.edit_admin_list', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('edit_admin_list'))
                return true;
            else
                return false;

        });


        Gate::define('permissions.view_default_agent', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('view_default_agent'))
                return true;
            else
                return false;

        });
        Gate::define('permissions.edit_default_agent', function ($user) 
        {
            //for settings
            if(Helper::checkUserPermissions('edit_default_agent'))
                return true;
            else
                return false;

        });











    }
}
