@extends('layouts.app')

@section('content')
<script type="text/javascript">
$(document).ready(function() 
{
    prepareLocale();
});

function prepareLocale()
{

    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
}


function updateGameList(prdId,submitType) 
{

    data = {};

    data["prd_id"] = prdId;

    console.log(data);

    utils.startLoadingBtn(submitType,"main-data");

    $.ajax({
        type: "POST",
        url: "/ajax/update/gamelist",
        data: data,
        success: function(data) 
        {
            utils.stopLoadingBtn(submitType,"main-data");

            var obj = JSON.parse(data);
            console.log(obj);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status);
            }

            
        },
        error: function()
        {
        } 
    });
}

</script>

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.settings.gamelist.breadcrumb.settings') }}</li>
    <li class="breadcrumb-item active">{{ __('app.settings.gamelist.breadcrumb.gamelist') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">
        <div class="card">
            <div class="card-header">
                <strong>{{ __('app.settings.gamelist.gamelist') }}</strong>
            </div>
            <div class="card-body" id="main-data">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="name">{{ __('app.settings.gamelist.haba.gamelist') }}</label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <button type="button" id="updateHaba" class="btn btn-sm btn-success" onclick="updateGameList(2,'updateHaba')"><i class="fa fa-dot-circle-o"></i>{{ __('app.settings.gamelist.update') }}  
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="name">{{ __('app.settings.gamelist.prag.gamelist') }}</label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <button type="button" id="updatePrag" class="btn btn-sm btn-success" onclick="updateGameList(3,'updatePrag')"><i class="fa fa-dot-circle-o"></i>{{ __('app.settings.gamelist.update') }}  
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="name">{{ __('app.settings.gamelist.wm.gamelist') }}</label>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <button type="button" id="updateWm" class="btn btn-sm btn-success" onclick="updateGameList(4,'updateWm')"><i class="fa fa-dot-circle-o"></i>{{ __('app.settings.gamelist.update') }} 
                        </div>
                    </div>
                </div>
        </div>     
    </div>
</div>

@endsection
