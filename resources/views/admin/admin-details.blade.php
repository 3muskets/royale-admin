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
    var aryLogFields = ["status","merchant","admin","settings"];
    var log_data = utils.generateLogData(aryLogFields);
    $("#log_old").val(log_data);


    $('#status').prop('disabled', true);
    $('#role').prop('disabled', true);

    @can('permissions.edit_admin_list')

     $('#status').prop('disabled', false);
     $('#role').prop('disabled', false);

    @endcan


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
        url: "/ajax/admins/admin/update",
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

<style>

    .heading 
    {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 1rem;
    }

</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.admins.admindetails.breadcrumb.admins') }}</li>
    <li class="breadcrumb-item">
        <a href="/admins/admin">
            {{ __('app.admins.admindetails.breadcrumb.adminslist') }}
        </a>
    </li>
    <li class="breadcrumb-item active">{{ __('app.admins.admindetails.breadcrumb.details') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                @csrf

                <input type="hidden" id="log_old" name="log_old">
                
                <input type="hidden" id="id" name="id" value="{{ app('request')->input('id') }}">

                <div class="card-header">
                    <strong>{{ __('app.admins.admindetails.title') }}</strong>
                </div>
                
                <div class="card-body">

                    <div class="heading" style=" margin-bottom: 1rem">
                            {{ __('app.admins.admindetails.details') }}
                    </div>

                    <div class="row">
                        <div class="col-sm-4">

                            <div class="form-group">
                                <label>{{ __('app.admins.admindetails.username') }}</label>
                                <input type="text" class="form-control" name="username" value="{{ $data->username }}" readonly="">
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">

                            <div class="form-group">
                                <label>{{ __('app.admins.admindetails.dateregistered') }}</label>
                                <input type="text" class="form-control" value="{{ $data->created_at }}" disabled="">
                            </div>

                        </div>
                    </div>


                    <div class="row">
                        <div class="form-group col-sm-4">
                                <label for="name">Role Type</label>
                                <select class="form-control" name="role" id="role">
                                    {{ Helper::generateOptions($optionsAdminRoles,$data->id) }}
                                </select>
                        </div>
                    </div>


                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label>{{ __('app.admins.admindetails.status') }}</label>
                            <select class="form-control" id="status" name="status">

                                {{ Helper::generateOptions($optionsStatus,'') }}

                            </select>
                        </div>
                    </div>
                </div>

                @can('permissions.edit_admin_list')
                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> {{ __('app.admins.admindetails.update') }}
                    </button>

                </div>
                @endcan

            </form>

        </div>

    </div>
</div>
 
@endsection
