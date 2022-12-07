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
        url: "/ajax/settings/defaultag",
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

function onMainModalDismiss()
{
    window.location.href = "/settings/defaultag";
}

function onMainModalDismissError()
{
    $("#mainForm").attr("enabled",1);
}



</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.settings.defaultag.breadcrumb.settings') }}</li>
    <li class="breadcrumb-item active">{{ __('app.settings.defaultag.breadcrumb.defaultag') }}</li>
</ol>
<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                @csrf

                <div class="card-header">
                    <strong>
                       {{ __('app.settings.defaultag.title') }}
                    </strong>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label>{{ __('app.settings.defaultag.agent') }}</label>
                                </div>
                                <div class="col-sm-8">
                                    <select id="ag" name="ag" class="form-control">
                                        @foreach ($agList as $ag)
                                            <option value="{{ $ag->id }}">{{ $ag->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                            </div>

                        </div>
                    </div>
                </div>
                @can('permissions.edit_default_agent')
                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> {{ __('app.settings.defaultag.update') }}
                    </button>

                </div>
                @endcan
            </form>

        </div>

    </div>
</div>

@endsection
