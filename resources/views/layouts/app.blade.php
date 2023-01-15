<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- <link rel="shortcut icon" href="/coreui/img/favicon.png"> -->

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Royal') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}"/>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="/js/utils.js"></script>
    <script src="/js/auth.js"></script>
    <script src="/js/summernote.js"></script>
  
    <!-- JqueryUI -->
    <script src="/jqueryui/jquery-ui.min.js"></script>

    <!-- Custom CSS -->
    <link href="/css/custom.css" rel="stylesheet">

      <!-- JqueryUI -->
    <link href="/jqueryui/jquery-ui.min.css" rel="stylesheet">

    <!-- CoreUI -->
    <link href="/coreui/vendors/css/flag-icon.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/font-awesome.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/simple-line-icons.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/spinkit.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/ladda-themeless.min.css" rel="stylesheet">
    <link href="/coreui/css/style.css" rel="stylesheet">

    <!-- CoreUI -->
    <script src="/coreui/vendors/js/pace.min.js"></script>
    <script src="/coreui/vendors/js/Chart.min.js" ></script>
    <script src="/coreui/vendors/js/spin.min.js"></script>
    <script src="/coreui/vendors/js/ladda.min.js"></script>
    <script src="/coreui/js/app.js" defer></script>

    <script type="text/javascript">

    var locale = [];
    var dwCount;

    $(document).ready(function() 
    {
        auth.setUserLevel("{{Auth::user()->level}}");

        if(auth.getUserLevel() > 0)
            auth.setUserId("{{Auth::user()->admin_id}}");
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error : function(xhr,textStatus,errorThrown) 
            {
                if(xhr.status == 440)
                    window.location.href = "/?k=1";
                else if(xhr.status == 441)
                    window.location.href = "/?k=2";
            }
        });

        $("#header_tier").html('(' + auth.getUserLevelName() + ')');
        $("#header_dd_tier").html('(' + auth.getUserLevelName() + ')');

        prepareCommonLocale();
        timerTick();


        //web socket        
        // createWS();

        // Echo.private('bo-main.{{ Auth::user()->ws_channel }}')
        //     .listen('.dwreq', (e) => 
        //     {
        //         // console.log(e);
        //         if(auth.getUserLevel() == 0)
        //         {
        //             $('#dwreq_count').html(e.pending_count);
        //             playAudio();

        //             var div = document.getElementById('header_dwreq');
        //             clearTimeout(div.flashTimer);
        //             div.flashCount = 0;
        //             div.maxFlash = -1;
        //             flashDiv(div);
        //         }
        //     });


        dwCount = setInterval(function()
        {
            $.ajax({
                type: "GET",
                url: '/ajax/member/dw/count',
                success: function(data)
                {
                    var currentCount = $("#dwreq_count").text();

                    if(data > 0)
                    {
                        $('#dwreq_count').html(data);
                        playAudio();
                    }
                }
            });
        }, 10000);
    });

    $(window).resize(function() 
    {
        timerTick();
    });    

    var days = locale;

    var timer = setInterval(timerTick, 1000);

    function timerTick()
    {
        $('#current_time').html(utils.getCurrentDateTime());

        $windowWidth = $(window).width();

        if( $windowWidth <= 751 ) {     
            $('#current_time').hide();
        }
        else
        {
            $('#current_time').show();
        }
    }
        
    function prepareCommonLocale()
    {
         //localization
        //data table
        locale['utils.datatable.totalrecords'] = "{!! __('common.datatable.totalrecords') !!}";
        locale['utils.datatable.norecords'] = "{!! __('common.datatable.norecords') !!}";
        locale['utils.datatable.invaliddata'] = "{!! __('common.datatable.invaliddata') !!}";
        locale['utils.datatable.total'] = "{!! __('common.datatable.total') !!}";
        locale['utils.datatable.pagetotal'] = "{!! __('common.datatable.pagetotal') !!}";
        
        //modal
        locale['utils.modal.ok'] = "{!! __('common.modal.ok') !!}";

        locale['utils.datetime.day.0'] = "{!! __('app.header.sun') !!}";
        locale['utils.datetime.day.1'] = "{!! __('app.header.mon') !!}";
        locale['utils.datetime.day.2'] = "{!! __('app.header.tue') !!}";
        locale['utils.datetime.day.3'] = "{!! __('app.header.wed') !!}";
        locale['utils.datetime.day.4'] = "{!! __('app.header.thur') !!}";
        locale['utils.datetime.day.5'] = "{!! __('app.header.fri') !!}";
        locale['utils.datetime.day.6'] = "{!! __('app.header.sat') !!}";

        locale['utils.credit'] = "{!! __('app.header.credit') !!}";
    }

    function createWS()
    {
        window.Echo.options = 
            {
                broadcaster: 'pusher',
                key: "{{ env('PUSHER_APP_KEY') }}",
                cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
                encrypted: false,
                wsHost: "{{ env('PUSHER_WSHOST') }}",
                wsPort: "{{ env('PUSHER_PORT') }}",
                disableStats: true,
            };

        window.Echo.connect();
    }

    function playAudio() 
    { 
        var x = document.getElementById("audioAlert"); 
        var playPromise = x.play(); 

        if (playPromise !== undefined) 
        {
            playPromise.then(_ => 
            {
              // Automatic playback started!
              // Show playing UI.
              // We can now safely pause video...
              video.pause();
            })
            .catch(error => 
            {
              // Auto-play was prevented
              // Show paused UI.
            });
        }
    } 

    function flashDiv(div)
    {
        //maxFlash = -1 : won't stop

        if(div.flashCount > div.maxFlash && div.maxFlash != -1)
        {
            div.style.backgroundColor = '#63c2de';
            return;
        }

        div.flashCount += 1;

        if(div.currentFlash)
        {
            div.currentFlash = false;
            div.style.backgroundColor = '#63c2de';
        }
        else
        {
            div.currentFlash = true;
            div.style.backgroundColor = '#f86c6b';
        }

        div.flashTimer = window.setTimeout(function(){flashDiv(div)},1000); 
    }

    </script>

    <style type="text/css">

        body
        {
            font-size: 12px;
        }

        .app-header.navbar .navbar-brand
        {
            background-image: none;
        }
        @media (max-width: 991.99px)
        {
            .app-header.navbar .navbar-brand
            {
                display:none;
            }
        }

        /*coreui sidebar style*/

        .sidebar .nav-link:hover, .sidebar .navbar .dropdown-toggle:hover, .navbar .sidebar .dropdown-toggle:hover 
        {
            background: rgba(0,0,0,0); 
        }

        .sidebar-minimized .sidebar .nav-item:hover > .nav-link
        , .sidebar-minimized .sidebar .navbar .nav-item:hover > .dropdown-toggle
        , .navbar .sidebar-minimized .sidebar .nav-item:hover > .dropdown-toggle 
        {
            background: #29363d; 
        }

        .sidebar .nav-link.active i
        , .sidebar .navbar .active.dropdown-toggle i
        , .navbar .sidebar .active.dropdown-toggle i 
        {
            color: #fff; 
        }

        .sidebar .nav-link.active
        , .sidebar .navbar .active.dropdown-toggle
        , .navbar .sidebar .active.dropdown-toggle 
        {
            background: #678898 !important; 
        }

        .sidebar .nav-link i, .sidebar .navbar .dropdown-toggle i, .navbar .sidebar .dropdown-toggle i 
        {
            color: #fff; 
        }

        .sidebar .nav-dropdown-toggle::before 
        {
      
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 11 14'%3E%3Cpath fill='%23fff' d='M9.148 2.352l-4.148 4.148 4.148 4.148q0.148 0.148 0.148 0.352t-0.148 0.352l-1.297 1.297q-0.148 0.148-0.352 0.148t-0.352-0.148l-5.797-5.797q-0.148-0.148-0.148-0.352t0.148-0.352l5.797-5.797q0.148-0.148 0.352-0.148t0.352 0.148l1.297 1.297q0.148 0.148 0.148 0.352t-0.148 0.352z'/%3E%3C/svg%3E");

            top:18.5px;
            width: 12px;
            height: 12px;
            right: 5px;
       }

        .sidebar .nav-dropdown.open 
        {
            background: rgba(100,100,100, 0.5);
        }
        /*coreui sidebar style*/



        .navbar-toggler,.navbar-toggler:focus
        {
            outline: 0px !important;
            border-color: rgba(255, 255, 255,1) !important;
            border-radius:5px;
            border-width:2px;
        }
        /*coreui navbar style*/

    </style>

    @yield('head')

</head>

<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden 
    {{ Cookie::get('sidebar') }}">
    <header class="app-header navbar navbar-dark" style="color:white">

        <audio id="audioAlert">
            <source src="/audio/ogg/definite.ogg" type="audio/ogg">
            <source src="/audio/mpeg/definite.mp3" type="audio/mpeg">
        </audio>

        <button class="navbar-toggler mobile-sidebar-toggler d-lg-none" type="button">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand" href="/home">
        </a>

        <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" style="min-width:45px">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="current_time" style="padding: 10px;width:300px;color:black;"></div>
        @can('system.accounts.admin')
        <a href="/member/dwreq" style="text-decoration: none;padding-left:2px">
            <div id="header_dwreq" style="padding:5px 10px 5px 10px;border-radius:5px;background:#63c2de;color:white;font-weight:bold">{{ __('app.sidebar.banking.dwrequest') }} : 
                <span id="dwreq_count">{{ isset($pendingDWReq) ? $pendingDWReq : '-' }}</span>
            </div>
        </a>
        @endcan

        <ul class="nav navbar-nav ml-auto">

            <li class="nav-item px-1">
                <span><b id="tier"></b></span>
            </li>
        
            <li class="nav-item dropdown" style="padding-bottom:5px">
                <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    
                    <img src="/coreui/img/avatars/0.jpg" class="img-avatar" alt="" style="border:1px solid white">
                </a>
                
                <div class="dropdown-menu dropdown-menu-right" style="margin-top:6px">
                    <div class="dropdown-header text-center">
                        <strong>{{ Auth::user()->username }} <b id="header_dd_tier"></b></strong>
                    </div>
                    
                    <a class="dropdown-item" href="{{ route('changepassword') }}">
                        <i class="fa fa-lock"></i> {{ __('app.header.changepassword') }}
                    </a>

                    <a class="dropdown-item" href="{{ route('logout') }}" 
                        onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
                        <i class="fa fa-lock"></i> {{ __('app.header.logout') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                </div>
            </li>   

            <li class="nav-item dropdown" style="padding-top:5px;">
                <a class="nav-link nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" >
                    <i class="flag-icon flag-icon-{{ Helper::getLocaleFlag() }} h1" title="{{ __('auth.language') }}" id="gb" style="width:35px;"></i> 
                </a>
                
                <div class="dropdown-menu dropdown-menu-right" style="margin-top:-1px">

                    <div class="dropdown-header text-center">
                        <strong>{{ __('app.header.language') }}</strong>
                    </div>

                     <a class="dropdown-item" href="#"
                        onclick="event.preventDefault();
                                    document.getElementById('locale').value = 'en';
                                    document.getElementById('form-locale').submit();">
                        </i>English
                    </a>
                    <a class="dropdown-item" href="#"
                        onclick="event.preventDefault();
                                    document.getElementById('locale').value = 'zh-cn';
                                    document.getElementById('form-locale').submit();">
                        </i>中文
                    </a>

                    <form id="form-locale" action="{{ route('locale') }}" method="POST" style="display: none;">
                        @csrf
                        <input type="hidden" id="locale" name="locale" value="">
                    </form>
                </div>
            </li>   
        </ul>
    </header>

    <div class="app-body">
        <div class="sidebar">
            <nav class="sidebar-nav">
                <ul class="nav">

                    <li class="nav-item">
                        <a class="nav-link" href="/home"><i class="icon-home"></i> {{ __('app.sidebar.home') }}</a>
                    </li>
                    @can('system.accounts.admin')
                    @canany(['permissions.member_credit','permissions.view_member_list','permissions.view_member_levelsetting'])
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-user"></i> 
                             {{ __('app.sidebar.membermanagement') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            @can('permissions.view_member_list')
                            <li class="nav-item">
                                <a class="nav-link" href="/merchants/merchant/member"><i class="icon-cursor"></i> 
                                    {{ __('app.sidebar.membermanagement.list') }}
                                </a>
                            </li>
                            @endcan
                            @can('permissions.member_credit')
                            <li class="nav-item">
                                <a class="nav-link" href="/merchants/merchant/member/credit"><i class="icon-cursor"></i> 
                                    {{ __('app.sidebar.membermanagement.credit') }}
                                </a>
                            </li>
                            @endcan

                            @can('system.accounts.admin')
                            @can('permissions.view_member_levelsetting')
                            <li class="nav-item">
                                <a class="nav-link" href="/merchants/merchant/member/levelsetting"><i class="icon-cursor"></i> 
                                   Member Level Setting
                                </a>
                            </li> 
                            @endcan
                            @endcan    
                        </ul>
                    </li> 
                    @endcan
                    @endcan

                    @can('system.accounts.admin')
                    @canany(['permissions.view_banking_acc', 'permissions.view_dw_request'])
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-wallet"></i> 
                             {{ __('app.sidebar.banking') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">                           
                            @can('permissions.view_banking_acc') 
                            <li class="nav-item">
                                <a class="nav-link" href="/banking/bankinfo"><i class="icon-info"></i>
                                   Bank Account List
                                </a>
                            </li>
                            @endcan                 
                            @can('permissions.view_dw_request')
                            <li class="nav-item">
                                <a class="nav-link" href="/member/dwreq"><i class="icon-refresh"></i>
                                   {{ __('app.sidebar.banking.dwrequest') }}
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @endcan

<!--                     @can('system.accounts.admin')
                    @canany(['permissions.view_crypto_setting'])
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-wallet"></i> 
                             {{ __('app.sidebar.crypto') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            @can('permissions.view_crypto_setting') 
                            <li class="nav-item">
                                <a class="nav-link" href="/crypto/setting"><i class="icon-info"></i>
                                   {{ __('app.sidebar.crypto.cryptosetting') }}
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @endcan -->



                    @can('system.accounts.admin')
                    @canany(['permissions.view_promo', 'permissions.view_cashback_setting'])
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-wallet"></i> 
                             Bonus
                        </a>
                        
                        <ul class="nav-dropdown-items">   
                            @can('permissions.view_promo')
                            <li class="nav-item">
                                <a class="nav-link" href="/promo/setting"><i class="icon-tag"></i>
                                    Promo Setting
                                </a>
                            </li>
                            @endcan
                        </ul>
                        <ul class="nav-dropdown-items">   
                            @can('permissions.view_cashback_setting')
                            <li class="nav-item">
                                <a class="nav-link" href="/cashback/setting"><i class="fa fa-dollar"></i>
                                    Cashback Setting
                                </a>
                            </li>
                            @endcan
                        </ul>

                    </li>
                    @endcan
                    @endcan

                    @can('system.accounts.admin')
                    @canany(['permissions.txn_history_report', 'permissions.win_loss_report', 'permissions.win_loss_by_product_report', 'permissions.agent_credit_report', 'permissions.member_credit_report','permissions.promotion_report'])
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-notebook"></i> 
                            {{ __('app.sidebar.reports') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                             @can('permissions.txn_history_report')
                             <li class="nav-item">
                                <a class="nav-link" href="/reports/bet_history"><i class="icon-action-undo"></i>   
                                    {{ __('app.sidebar.reports.txnhistory') }}
                                </a>
                            </li>
                             @endcan
                             @can('permissions.win_loss_report')
                             <li class="nav-item">
                                <a class="nav-link" href="/reports/winloss"><i class="icon-action-redo"></i>  
                                  {{ __('app.sidebar.reports.winloss') }}
                                </a>
                            </li>
                             @endcan

                            @can('permissions.member_promotion_report')
                            <li class="nav-item">
                                <a class="nav-link" href="/reports/promotion"><i class="icon-notebook"></i>  
                                   Member Promotion Report
                                </a>
                            </li>
                            @endcan

                            @can('permissions.member_credit_report')
                            <li class="nav-item">
                                <a class="nav-link" href="/reports/member_credit"><i class="icon-action-redo"></i>  
                                   {{ __('app.sidebar.reports.credit.member') }}
                                </a>
                            </li>
                            @endcan
<!--                             @can('permissions.member_referral_report')
                            <li class="nav-item">
                                <a class="nav-link" href="/reports/member_referral"><i class="icon-action-redo"></i>  
                                   Member Referral Report
                                </a>
                            </li>
                            @endcan -->
<!--                             <li class="nav-item">
                                <a class="nav-link" href="/reports/statement/paymentgateway"><i class="icon-action-redo"></i>  
                                   Statement By PG
                                </a>
                            </li> -->
                        </ul>
                    </li>
                    @endcan
                    @endcan


                    @can('permissions.member_msg') 
                    <li class="nav-item">
                        <a class="nav-link" href="/member/msg"><i class="icon-bell"></i>
                           {{ __('app.sidebar.message') }} 
                        </a>
                    </li>
                    @endcan


<!--                     <li class="nav-item">
                        <a class="nav-link" href="/product/setting"><i class="fa fa-wrench"></i>
                           Product Setting
                        </a>
                    </li>

 -->

                    @can('system.accounts.admin')
                    @canany(['permissions.cms_main_banner', 'permissions.cms_topbar_announcement', 'permissions.cms_popup'])
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-settings"></i> 
                            CMS
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            @can('permissions.cms_main_banner') 
                            <li class="nav-item">
                                <a class="nav-link" href="/cms/banner"><i class="icon-settings"></i>  
                                    Main Banner
                                </a>
                            </li>
                            @endcan 
                            @can('permissions.cms_topbar_announcement') 
                            <li class="nav-item">
                                <a class="nav-link" href="/cms/announcement"><i class="icon-settings"></i>  
                                    Top Bar Announcement
                                </a>
                            </li>  
                            @endcan
                            @can('permissions.cms_popup') 
                            <li class="nav-item">
                                <a class="nav-link" href="/cms/popup"><i class="icon-settings"></i>  
                                    Pop Up
                                </a>
                            </li> 
                            @endcan

                            @can('permissions.cms_whatsapp') 
                            <li class="nav-item">
                                <a class="nav-link" href="/cms/whatsapp"><i class="icon-settings"></i>  
                                    Whatsapp
                                </a>
                            </li> 
                            @endcan
                             
                        </ul>
                    </li>
                    @endcan
                    @endcan

                    
                    @can('system.accounts.super.admin')
                    @canany(['permissions.create_admin','permissions.view_admin_list','permissions.view_default_agent']) 
                    <li class="nav-item nav-dropdown">
                        <a class="nav-link nav-dropdown-toggle" href="#"><i class="icon-settings"></i> 
                            {{ __('app.sidebar.settings') }}
                        </a>
                        
                        <ul class="nav-dropdown-items">
                            @can('permissions.create_admin')
                            <li class="nav-item">
                                <a class="nav-link" href="/admins/admin/new"><i class="icon-user-follow"></i>  
                                    Create Sub Account
                                </a>
                            </li>  
                            @endcan
                            @can('permissions.view_admin_list')
                            <li class="nav-item">
                                <a class="nav-link" href="/admins/admin"><i class="icon-people"></i> 
                                    Sub Account List
                                </a>
                            </li>
                            @endcan
                            @can('system.accounts.admin')
                            <li class="nav-item">
                                <a class="nav-link" href="/admins/roles/new"><i class="icon-settings"></i>  
                                    Create Admin Roles
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admins/roles"><i class="icon-settings"></i>  
                                    Admin Roles
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @endcan

                </ul>
            </nav>

            <button class="sidebar-minimizer brand-minimizer" type="button"></button>
        </div>

        <!-- Main content -->
        <main class="main">
            @yield('content')
        </main>

    </div>

    <footer class="app-footer">
        <span>© {{ date('Y') }} Ditto Gaming</span>
    </footer>

</body>
</html>