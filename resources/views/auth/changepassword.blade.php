@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();
    enableMainForm();
    
    utils.createSpinner("main-spinner");

    $("#main-spinner").hide();

    $("#mainForm").on('submit',(function(e){
        e.preventDefault();
        submitMainForm();
    }));
});

function prepareLocale() 
{
    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
}

function submitMainForm()
{   
    $("#main-spinner").show();
    $("#main-data").hide();

    if($("#mainForm").attr("enabled") == 0)
    {
        return;
    }

    $("#mainForm").attr("enabled",0);
            //console.log($("#mainForm")[0]);

    utils.startLoadingBtn("btnSubmit","mainForm");

    $.ajax({
        url: "/ajax/accounts/change_password",
        type: "POST",
        data:  new FormData($("#mainForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            $("#main-spinner").hide();
            $("#main-data").show();
            $("#mainForm")[0].reset();

            utils.stopLoadingBtn("btnSubmit","mainForm");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,enableMainForm);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,enableMainForm);
            }
        },
        error: function(){}             
    }); 
}

function enableMainForm()
{
    $("#mainForm").attr("enabled",1);
}

</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.profile.changepassword.breadcrumb.profile') }}</li>
    <li class="breadcrumb-item active">{{ __('app.profile.changepassword.breadcrumb.profile.changepassword') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">


            <form method="POST" id="mainForm">
                @csrf

                <div class="card-header">
                    <strong>{{ __('app.profile.changepassword.profile.details.title') }}</strong>
                </div>

                <div id="main-spinner" class="card-body"></div>
                
                <div class="card-body" id="main-data">

                    <div class="row">
                        <div class="col-sm-2">

                            <div class="form-group">
                                <label>{{ __('app.profile.changepassword.profile.details.username') }}</label>
                                <input type="text" class="form-control" value="{{ $data['username'] }}" disabled="">
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-2">

                            <div class="form-group">
                                <label>{{ __('app.profile.changepassword.profile.details.currentpassword') }}</label>
                                <input name="current_password" type="password" class="form-control">
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-2">

                            <div class="form-group">
                                <label>{{ __('app.profile.changepassword.profile.details.newpassword') }}</label>
                                <input name="new_password" type="password" class="form-control">
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-2">

                            <div class="form-group">
                                <label>{{ __('app.profile.changepassword.profile.details.confirmnewpassword') }}</label>
                                <input name="confirm_password" type="password" class="form-control">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> {{ __('app.profile.changepassword.profile.details.update') }}
                    </button>

                </div>

            </form>



        </div>

    </div>
</div>



        
@endsection
