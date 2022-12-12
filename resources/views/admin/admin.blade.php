@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

    utils.createSpinner("main-spinner");

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    $("#formChangePassword").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));
});

function prepareLocale()
{
    locale['mainData.username'] = "{!! __('app.admins.admin.maindata.username') !!}";
    locale['mainData.dateregistered'] = "{!! __('app.admins.admin.maindata.dateregistered') !!}";
    locale['mainData.status'] = "{!! __('app.admins.admin.maindata.status') !!}";
    locale['mainData.actions'] = "{!! __('app.admins.admin.maindata.actions') !!}";
    locale['mainData.action'] = "{!! __('app.admins.admin.maindata.action') !!}";
    locale['tooltip.changepassword'] = "{!! __('app.admins.admin.tooltip.changepassword') !!}";


    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
}

var mainData;
var refreshMainData = false;

function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();

    var data = utils.getDataTableDetails(containerId);

    data["username"] = $("#f_username").val();

    $.ajax({
        type: "GET",
        url: "/ajax/admins/admin/list",
        data: data,
        success: function(data) 
        {
            mainData = JSON.parse(data);
            loadMainData(containerId);
        }
    });
}

function loadMainData(containerId)
{
    $("#main-spinner").hide();
    $("#main-table").show();

    var fields = [                           
                    ["username",locale['mainData.username'],true,false]
                    , ["type",'Role Type',true,false]
                    ,["created_at",locale['mainData.dateregistered'],true,false]
                    ,["status",locale['mainData.status'],false,false]
                    @can('permissions.edit_admin_list') 
                    ,["",locale['mainData.action'],false,false]
                    @endcan
                ];  

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();

        var fieldUsername = utils.getDataTableFieldIdx("username",fields);
        var fieldStatus = utils.getDataTableFieldIdx("status",fields);
        var fieldActions = utils.getDataTableFieldIdx("",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            //username
            var a = document.createElement("a");
            a.href = "/admins/admin/details?id=" + mainData.results[i - 1]["role_id"];
            a.innerHTML = mainData.results[i - 1]["username"];

            row.cells[fieldUsername].innerHTML = "";
            row.cells[fieldUsername].appendChild(a);

            //status
            if(mainData.results[i - 1]["status"] == "a")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+mainData.results[i-1]["status_desc"]+'</span>';
            else 

            row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+mainData.results[i-1]["status_desc"]+'</span>';

            @can('permissions.edit_admin_list') 
            var imgChange = document.createElement("img");
            imgChange.onclick = showPasswordModal;
            imgChange.style.cursor="pointer";
            imgChange.setAttribute("data-toggle", "tooltip");
            imgChange.setAttribute("title", locale['tooltip.changepassword']);
            imgChange.rowId = i;
            imgChange.src="/images/icon/icon-pw4.png";
            imgChange.style.height = '30px';
            imgChange.style.width = '30px';

            row.cells[fieldActions].innerHTML = "";
            row.cells[fieldActions].appendChild(imgChange);
            @endcan
        } 
    }
}

function showPasswordModal()
{

    var userId = mainData.results[this.rowId - 1]["id"];

    $("#formChangePassword")[0].reset();
    $("#modalMessage").hide();

    $("#modal-id").val(userId);

    $("#modalChangePassword").modal('show');

    refreshMainData = false;
}

function submitPassword()
{ 
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitPassword","modalChangePassword");

    $("#formChangePassword").attr("enabled",0);

    $.ajax({
        url: "/ajax/admins/admin/change_password",
        type: "POST",
        data:  new FormData($("#formChangePassword")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            utils.stopLoadingBtn("btnSubmitPassword","modalChangePassword");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);

                $("#modalChangePassword").modal('hide');

            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onModalDismissError);
            }
        },
        error: function(){}             
    }); 
}

function onModalChangePasswordDismiss()
{
    if(refreshMainData)
    {
        getMainData();
    }
}

function onModalDismissError()
{
    $("#modalChangePassword").attr("enabled",1);
}

function sortMainData()
{
    utils.prepareDataTableSortData(this.containerId,this.orderBy);

    getMainData();
}

function pagingMainData()
{
    utils.prepareDataTablePagingData(this.containerId,this.page);

    getMainData();
}

function filterMainData()
{
    utils.resetDataTableDetails("main-table");

    getMainData();
}

function resetMainData()
{
    $("#f_username").val("");

    filterMainData();
}

function createMainData()
{
    window.location.href = "/admins/admin/new";
}

</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
	<li class="breadcrumb-item">{{ __('app.admins.admin.breadcrumb.admins') }}</li>
	<li class="breadcrumb-item active">{{ __('app.admins.admin.breadcrumb.adminslist') }}</li>
</ol>

<div class="container-fluid">
	<div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="filterForm">

                <div class="card-header">
                    <strong>{{ __('common.filter.title') }}</strong>
                </div>
                
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-sm-2">

                            <div class="form-group">
                                <label for="name">{{ __('app.admins.admin.filter.username') }}</label>
                                <input type="text" class="form-control" id="f_username" placeholder="" autocomplete="">
                            </div>

                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> {{ __('common.filter.submit') }}</button>

                    <button type="button" class="btn btn-sm btn-danger" onclick="resetMainData()"><i class="fa fa-ban"></i> {{ __('common.filter.reset') }}</button>

                    <button type="button" class="btn btn-sm btn-primary pull-right" onclick="createMainData()"><i class="fa fa-plus"></i> {{ __('common.filter.create') }}</button>
                </div>

            </form>

        </div>

		<div class="card">

			<div id="main-spinner" class="card-body"></div>

			<div id="main-table" class="card-body"></div>

            <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>

		</div>
	</div>
</div>

<div id="modalChangePassword" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.admins.admin.modal.changepassword') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formChangePassword">
                        
                        <input type="hidden" id="modal-id" name="id">

                        <div class="card-body">

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">

                                        <label>{{ __('app.admins.admin.modal.newpassword') }}</label>
                                        <input type="password" name="password" class="form-control">

                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="card-footer">

                            <button id="btnSubmitPassword" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitPassword()">
                                <i class="fa fa-dot-circle-o"></i> {{ __('app.admins.admin.modal.submit') }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection
