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

    checkCredit();

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
        url: "/ajax/merchants/merchant/create",
        type: "POST",
        data: new FormData($("#mainForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
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

function checkCredit()
{
    var input = $("form").find("#credit");
    utils.formatCurrencyInput(input);
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
            url: '/ajax/merchants/merchant/check_user',
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
    window.location.href = "/merchants/merchant";
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
    <li class="breadcrumb-item">{{ __('app.merchants.merchant.create.breadcrumb.agentmanagement') }}</li>
    <li class="breadcrumb-item"><a href="/merchants/merchant">{{ __('app.merchants.merchant.create.breadcrumb.agentlist') }}</a></li>
    <li class="breadcrumb-item active">{{ __('app.merchants.merchant.create.breadcrumb.create') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                @csrf

                <div class="card-header">
                    <strong>
                            {{ __('app.merchants.merchant.create.title.ag') }}
                    </strong>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="heading"> 
                                {{ __('app.merchants.merchant.create.details.ag') }}
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.merchants.merchant.create.merchantcode') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" name="username" id="username" class="form-control" required title="{!! __('error.input.required') !!}" autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')" autocomplete="off">

                                    <div id="icon_checking">

                                        <i class="fa fa-check fa-lg" id="entry_valid" style="color: green;" data-toggle="tooltip"></i>
                                        <i class="fa fa-close fa-lg" id="entry_non_valid" style="color: red" data-toggle="tooltip"></i>

                                    </div>

                                </div>
                                
                            </div>


                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.merchants.merchant.create.password') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <input class="form-control" type="password" name="password" required title="{!! __('error.input.required') !!}" autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')" autocomplete="off">
                                    <p style="color:blue;">{{ __('app.merchants.merchant.create.password_reminder') }}</p>

                                </div>
                                
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.merchants.merchant.create.confirmpassword') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <input class="form-control" type="password" name="confirmpassword" required title="{!! __('error.input.required') !!}" autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')" autocomplete="off">

                                </div>
                                
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.merchants.merchant.create.alias') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" name="fullname" class="form-control" required title="{!! __('error.input.required') !!}" autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')" autocomplete="off">

                                </div>

                            </div>

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.merchants.merchant.create.status') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <select class="form-control" name="status">

                                        {{ Helper::generateOptions($optionsStatus,'') }}

                                    </select>

                                </div>
                                
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.merchants.merchant.create.currency') }}</label>

                                </div>

                                @if(Auth::user()->level == '0')
                                <div class="col-sm-8">
                                    <input type="hidden" name="currency" value="MYR">
                                    <select class="form-control" disabled="">

                                        {{ Helper::generateOptions($optionsCurrency,'') }}

                                    </select>

                                </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> {{ __('app.merchants.merchant.create.create') }}
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

@endsection
