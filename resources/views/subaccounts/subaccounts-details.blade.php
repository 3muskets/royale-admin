@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();
    enableMainForm();

    $("#mainForm").on('submit',(function(e){
        e.preventDefault();
        submitMainForm();
    }));

    //logging
    var aryLogFields = ["id","status"];
    var log_data = utils.generateLogData(aryLogFields);
    $("#log_old").val(log_data);

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
        url: "/ajax/accounts/subaccounts/update",
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
    <li class="breadcrumb-item">Agent Management</li>
    <li class="breadcrumb-item"><a href="/accounts/subaccounts">{{ __('app.accounts.subaccounts.details.breadcrumb.subaccounts') }}</a></li>
    <li class="breadcrumb-item active">{{ __('app.accounts.subaccounts.details.breadcrumb.details') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                @csrf

                <input type="hidden" id="log_old" name="log_old">
                
                <input type="hidden" id="id" name="id" value="{{ app('request')->input('id') }}">

                <div class="card-header">
                    <strong>{{ __('app.accounts.subaccounts.details.title') }}</strong>
                </div>
                
                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-4">

                            <div class="form-group">
                                <label>{{ __('app.accounts.subaccounts.details.username') }}</label>
                                <input type="text" class="form-control" value="{{ $data->username }}" disabled="">
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">

                            <div class="form-group">
                                <label>{{ __('app.accounts.subaccounts.details.fullname') }}</label>
                                <input type="text" name="fullname" class="form-control" value="{{ $data->fullname }}">
                            </div>

                        </div>
                    </div>


                    <div class="row">
                        <div class="col-sm-4">

                            <div class="form-group">
                                <label>{{ __('app.accounts.subaccounts.details.createdat') }}</label>
                                <input type="text" class="form-control" value="{{ $data->created_at }}" disabled="">
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label>{{ __('app.accounts.subaccounts.details.status') }}</label>
                            <select class="form-control" id="status" name="status">

                                {{ Helper::generateOptions($optionsStatus,$data->status) }}

                            </select>
                        </div>
                    </div>

                </div>

                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> {{ __('app.accounts.subaccounts.details.submit') }}
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>



        
@endsection
