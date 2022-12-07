@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

    $("#mainForm").attr("enabled",1);

    $("#mainForm").on('submit',(function(e){
        e.preventDefault();
        submitMainForm();
    }));

    $("#username").on('keyup', function() {
        checkUser();
    });

    $("#icon_checking").hover(

        function() //mouseenter
        {
            if(!flag)
            {
                $("#entry_valid").tooltip("show");
                $("#entry_non_valid").tooltip("hide");
            }
            else
            {
                $("#entry_valid").tooltip("hide");
                $("#entry_non_valid").tooltip("show");
            }
        },

        function() //mouseleave
        {
            $("#entry_valid").tooltip("hide");
            $("#entry_non_valid").tooltip("hide");

        }

    );
});

function prepareLocale() 
{
    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
}

function submitMainForm()
{   
    if($("#mainForm").attr("enabled") == 0)
    {
        return;
    }

    $("#mainForm").attr("enabled",0);

    utils.startLoadingBtn("btnSubmit","mainForm");

    $.ajax({
        url: "/ajax/accounts/subaccounts/create",
        type: "POST",
        data:  new FormData($("#mainForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            // console.log(data);

            utils.stopLoadingBtn("btnSubmit","mainForm");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,onMainModalDismiss);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onMainModalDismissError);
            }
        },
        error: function(){}             
    }); 
}

var flag;

function checkUser()
{
    var username = $("#username").val().trim(); // this.value

    if(!username)
    {
            $("#entry_valid").hide();
            $("#entry_non_valid").hide();

            $("#entry_valid").tooltip("hide");
            $("#entry_non_valid").tooltip("hide");
    }
    else
    {
        $.ajax({ 
            url: '/ajax/accounts/subaccounts/check_user',
            data: { username : username},
            type: 'post',
            success: function(data)
            {
                data = JSON.parse(data);
                flag = data;

                if(!data)
                {
                    $("#entry_valid").show();
                    $("#entry_non_valid").hide();

                    $("#entry_non_valid").tooltip("hide");
                    $("#entry_valid").attr("data-original-title", "{!! __('app.merchants.merchant.create.username_valid') !!}");
                }
                else 
                {
                    $("#entry_valid").hide();
                    $("#entry_non_valid").show();

                    $("#entry_valid").tooltip("hide");
                    $("#entry_non_valid").attr("data-original-title", data);
                }
            },
        });
    }

}

function onMainModalDismiss()
{
    window.location.href = "/accounts/subaccounts";
}

function onMainModalDismissError()
{
    $("#mainForm").attr("enabled",1);
}

</script>

<style>

    .heading 
    {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    #entry_valid, #entry_non_valid 
    {
        display: none;
        float: right
    }

</style>
@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">Agent Management</li>
    <li class="breadcrumb-item"><a href="/accounts/subaccounts">{{ __('app.accounts.subaccounts.create.breadcrumb.subaccounts') }}</a></li>
    <li class="breadcrumb-item active">{{ __('app.accounts.subaccounts.create.breadcrumb.create') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                @csrf

                <div class="card-header">
                    <strong>New SubAccount</strong>
                </div>
                
                <div class="card-body">

                    <div class="heading">SubAccount Details</div>

                    <div class="form-group row">

                        <div class="col-sm-2">

                            <label>Username</label>

                        </div>

                        <div class="col-sm-3">

                            <input type="text" name="username" id="username" class="form-control" id="username" required title="{!! __('error.input.required') !!}" autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')" autocomplete="off">

                            <div id="icon_checking">

                                <i class="fa fa-check fa-lg" id="entry_valid" style="color: green;" data-toggle="tooltip"></i>
                                <i class="fa fa-close fa-lg" id="entry_non_valid" style="color: red" data-toggle="tooltip"></i>
                            </div>

                        </div>
                    </div>

                    <div class="form-group row">

                        <div class="col-sm-2">

                            <label>{{ __('app.accounts.subaccounts.create.password') }}</label>

                        </div>

                        <div class="col-sm-3">

                            <input class="form-control" type="password" name="password" required title="{!! __('error.input.required') !!}" autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')" autocomplete="off">
                            <p style="color:blue;">The password must be 8 or more characters</p>
                            
                        </div>

                    </div>

                    <div class="form-group row">

                        <div class="col-sm-2">
                            <label>{{ __('app.accounts.subaccounts.create.name') }} </label>

                        </div>

                        <div class="col-sm-3">

                            <input class="form-control" name="fullname" autocomplete="off">
                        </div>
                                
                    </div>

                </div>

                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> {{ __('app.accounts.subaccounts.create.submit') }}
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>



        
@endsection
