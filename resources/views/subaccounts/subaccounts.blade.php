
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
});

function prepareLocale() 
{
    locale['maindata.username'] = "{!! __('app.accounts.subaccounts.maindata.username') !!}";
    locale['maindata.fullname'] = "{!! __('app.accounts.subaccounts.maindata.fullname') !!}";
    locale['maindata.status']  = "{!! __('app.accounts.subaccounts.maindata.status') !!}";
    locale['maindata.createdat']  = "{!! __('app.accounts.subaccounts.maindata.createdat') !!}";
    locale['maindata.status.active'] = "{!! __('app.accounts.subaccounts.maindata.active') !!}";
    locale['maindata.status.inactive'] = "{!! __('app.accounts.subaccounts.maindata.inactive') !!}";
    locale['maindata.password'] = "{!! __('app.accounts.subaccounts.maindata.password') !!}";

    locale['tooltip.changepassword'] = "{!! __('app.accounts.subaccounts.changepasword') !!}";
    
    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";

  
}

var mainData;

function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var data = utils.getDataTableDetails(containerId);

    data["username"] = $("#f_name").val();

    //console.log(data);

    $.ajax({
        type: "GET",
        url: "/ajax/accounts/subaccounts/list",
        data: data,
        success: function(data) 
        {
            // console.log(data);
            if(data.length > 0)
            {
                mainData = JSON.parse(data);
            }
            else
            {
                mainData = [];
            }

            loadMainData(containerId);
        }
    });
}

function loadMainData(containerId)
{
    $("#main-spinner").hide();
    $("#main-table").show();

    var fields = [      
                     ["username",locale['maindata.username'],true,false]   
                     ,["fullname",locale['maindata.fullname'],true,false]                  
                    ,["created_at",locale['maindata.createdat'],true,false]
                    ,["status",locale['maindata.status'],false,false]
                    ,["password",locale['maindata.password'],false,false]
                ];  

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();

        var fieldUsername = utils.getDataTableFieldIdx("username",fields);
        var fieldStatus = utils.getDataTableFieldIdx("status",fields);
        var fieldPassword = utils.getDataTableFieldIdx("password",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            //name
            var a = document.createElement("a");
            a.href = "/accounts/subaccounts/details?id=" + mainData.results[i - 1]["id"];
            a.innerHTML = mainData.results[i - 1]["username"];

            row.cells[fieldUsername].innerHTML = "";
            row.cells[fieldUsername].appendChild(a);

            //status
            if(mainData.results[i - 1]["status"] == "a")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+locale['maindata.status.active']+'</span>';
            else 
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+locale['maindata.status.inactive']+'</span>';

            //actions
            row.cells[fieldPassword].innerHTML = "";
            
            //change password            
            var imgChange = document.createElement("img");
            imgChange.onclick = showPasswordModal;
            imgChange.style.cursor="pointer";
            imgChange.setAttribute("data-toggle", "tooltip");
            imgChange.setAttribute("title", locale['tooltip.changepassword']);
            imgChange.rowId = i;
            imgChange.src="/images/icon/icon-pw4.png";
            imgChange.style.height = '30px';
            imgChange.style.width = '30px';
            row.cells[fieldPassword].appendChild(imgChange);
        } 
    }
}

function showPasswordModal()
{

    var userId = mainData.results[this.rowId - 1]["id"];
    var username = mainData.results[this.rowId - 1]["username"];

    $("#formChangePassword")[0].reset();
    $("#modalMessage").hide();

    $("#modal-id").val(userId);
    $("#nickname").val(username);

    $("#modalChangePassword").modal('show');

    refreshMainData = false;
}

function submitPassword()
{ 
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitPassword","modalChangePassword");

    $.ajax({
        url: "/ajax/accounts/subaccounts/change_password",
        type: "POST",
        data:  new FormData($("#formChangePassword")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            // console.log(data);

            utils.stopLoadingBtn("btnSubmitPassword","modalChangePassword");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                // utils.generateModalMessage("modalMessage",obj.status,locale['success']);
                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);

                $("#modalChangePassword").modal('hide');

                // $("#modalSuccessChangePassword").modal('show');
            }
            else
            {
                utils.generateModalMessage("modalMessage",obj.status,obj.error);
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
    $("#f_name").val("");

    filterMainData();
}

function createMainData()
{
    window.location.href = "/accounts/subaccounts/new";
}

</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">Agent Management</li>
    <li class="breadcrumb-item active">{{ __('app.accounts.subaccounts.breadcrumb.subaccounts') }}</li>
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
                        <div class="col-sm-4">

                            <div class="form-group">
                                <label for="name">{{ __('app.accounts.subaccounts.filter.username') }}</label>
                                <input type="text" class="form-control" id="f_name" placeholder="">
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
                <h4 class="modal-title">{{ __('app.accounts.subaccounts.modal.changepassword') }}</h4>
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
                                        <label>Username</label>
                                        <input type="text" id="nickname" name="nickname" class="form-control" disabled="">
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>{{ __('app.accounts.subaccounts.modal.newpassword') }}</label>
                                        <input type="password" name="password" class="form-control">
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="card-footer">

                            <button id="btnSubmitPassword" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitPassword()">
                                <i class="fa fa-dot-circle-o"></i> {{ __('app.accounts.subaccounts.modal.submit') }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection
