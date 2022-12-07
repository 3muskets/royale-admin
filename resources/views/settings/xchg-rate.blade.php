
@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();
    utils.createSpinner("main-spinner");
    utils.createSpinner("main-history-spinner");

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));
});

function prepareLocale() 
{
    locale['maindata.status']  = "{!! __('app.accounts.subaccounts.maindata.status') !!}";
    
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

    data["currency"] = $("#currency").val();

    // console.log(data);

    $.ajax({
        type: "GET",
        url: "/ajax/settings/exchange-rate/list",
        data: data,
        success: function(data) 
        {
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
                     ["id","ID",true,false]
                     ,["code","Currency Code",true,false]
                     ,["country","Currency Exchange Country",true,false]          
                     ,["name","Currency Exchange Name",true,false]         
                     ,["rate","Exchange Rate",true,false]
                     ,["updated_at","Last Renewal Time",true,false]
                    ,["","",false,false]
                ];  

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();

        var fieldEdit = utils.getDataTableFieldIdx("",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            //actions
            row.cells[fieldEdit].innerHTML = "";
                      
            var btnEdit = document.createElement("i");
            btnEdit.className = "fa fa-edit fa-2x";
            btnEdit.onclick = showEditTableModal;
            btnEdit.rowId = i;
            btnEdit.style.cursor = "pointer";
            btnEdit.style.color = "#11acf4";
            btnEdit.style.padding = "0px 5px";
            btnEdit.setAttribute("data-toggle", "tooltip");
            btnEdit.setAttribute("title", "Edit");

            var btnHistory = document.createElement("i");
            btnHistory.className = "fa fa-history fa-2x";
            btnHistory.onclick = showHistoryTableModal;
            btnHistory.rowId = i;
            btnHistory.style.cursor = "pointer";
            btnHistory.style.color = "#11acf4";
            btnHistory.style.padding = "0px 5px";
            btnHistory.setAttribute("data-toggle", "tooltip");
            btnHistory.setAttribute("title", "History");

            var btnDelete = document.createElement("i");
            btnDelete.className = "fa fa-trash fa-2x";
            btnDelete.onclick = confirmationDelete;
            btnDelete.rowId = i;
            btnDelete.style.cursor = "pointer";
            btnDelete.style.color = "#11acf4";
            btnDelete.style.padding = "0px 5px";
            btnDelete.setAttribute("data-toggle", "tooltip");
            btnDelete.setAttribute("title", "Delete");

            row.cells[fieldEdit].innerHTML = "";
            row.cells[fieldEdit].appendChild(btnEdit);
            row.cells[fieldEdit].appendChild(btnHistory);
            row.cells[fieldEdit].appendChild(btnDelete);
            row.cells[fieldEdit].className = "pb-0";
        } 
    }
}

function showEditTableModal()
{
    var id = mainData.results[this.rowId - 1]["id"];
    var code = mainData.results[this.rowId - 1]["code"];
    var country = mainData.results[this.rowId - 1]["country"];
    var barCode = mainData.results[this.rowId - 1]["bar_code"];
    var name = mainData.results[this.rowId - 1]["name"];
    var rate = mainData.results[this.rowId - 1]["rate"];

    $("#formChangeDetails")[0].reset();
    $("#modalMessage").hide();

    $("#modalEditCurrency").modal('show');

    $("#currency_id").val(id);
    $("#code").val(code);
    $("#country").val(country);
    $("#name").val(name);
    $("#rate").val(rate);

    refreshMainData = false;
}

function showHistoryTableModal()
{
    var containerId = "main-history-table";
    var code = mainData.results[this.rowId - 1]['code'];

    $("#modalHistoryCurrency").modal('show');

    $("#main-history-spinner").show();
    $("#main-history-table").hide();

    $.ajax({
        type: "GET",
        url: "/ajax/settings/exchange-rate/history-list",
        data: {"code" : code},
        success: function(data) 
        {
            if(data.length > 0)
            {
                mainHistoryData = JSON.parse(data);
            }
            else
            {
                mainHistoryData = [];
            }

            loadHistoryMainData(containerId);
        }
    });
}

function loadHistoryMainData(containerId)
{
    $("#main-history-spinner").hide();
    $("#main-history-table").show();

    var fields = [      
                     ["rate","Exchange Rate",true,false]
                     ,["created_at","Date",true,false]
                ];  

    var table = utils.createDataTable(containerId,mainHistoryData,fields,sortMainData,pagingMainData);

    document.getElementById("country-history").innerHTML = "";
    document.getElementById("code-history").innerHTML = "";
    document.getElementById("name-history").innerHTML = "";

    if(table != null)
    {
        document.getElementById("country-history").innerHTML = "<span style='font-size:14px;margin-bottom:5px;'>Currency Country : " + mainHistoryData.results[0]['country'] + "</span>";

        document.getElementById("code-history").innerHTML = "<span style='font-size:14px;margin-bottom:5px;'>Currency Code : " + mainHistoryData.results[0]['code'] + "</span>";

        document.getElementById("name-history").innerHTML = "<span style='font-size:14px;margin-bottom:5px;'>Currency Name : " + mainHistoryData.results[0]['name'] + "</span>";
    }
}

function submitDetails()
{ 
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitDetails","main-table");

    $.ajax({
        url: "/ajax/settings/exchange-rate/update-details",
        type: "POST",
        data:  new FormData($("#formChangeDetails")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            // console.log(data);

            utils.stopLoadingBtn("btnSubmitDetails","main-table");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                // utils.generateModalMessage("modalMessage",obj.status,locale['success']);
                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);

                $("#modalEditCurrency").modal('hide');

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
    $("#currency").val("");

    filterMainData();
}

function createMainData()
{
    $("#modalNewCurrency").modal('show');
    $("#modalMessage").hide();

    $("#new_code").val("");
    $("#new_country").val("");
    $("#new_name").val("");
    $("#new_rate").val("");
}

function submitNewDetails()
{
    utils.startLoadingBtn("btnSubmitDetails","main-table");

    $.ajax({
        url: "/ajax/settings/exchange-rate/create",
        type: "POST",
        data:  new FormData($("#formChangeDetails")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            // console.log(data);

            utils.stopLoadingBtn("btnSubmitDetails","main-table");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                // utils.generateModalMessage("modalMessage",obj.status,locale['success']);
                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);

                $("#modalNewCurrency").modal('hide');

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

function confirmationDelete()
{
    var result = confirm("Want to delete?");

    var newId= mainData.results[this.rowId-1]['id'];
    var newCode = mainData.results[this.rowId-1]['code'];
    var newCountry = mainData.results[this.rowId-1]['country'];
    var newName = mainData.results[this.rowId-1]['name'];
    var newRate = mainData.results[this.rowId-1]['rate'];

    if (result) 
    {
        $.ajax({
            type: "POST",
            url: "/ajax/settings/exchange-rate/delete",
            data: {"id" : newId,
                    "code" : newCode,
                    "country" : newCountry,
                    "name" : newName,
                    "rate" : newRate},
            success: function(data) 
            {
                if(data.length > 0)
                {
                    mainData = JSON.parse(data);
                }
                else
                {
                    mainData = [];
                }

                getMainData();
            }
        });
    }
}

</script>

<style type="text/css">
    label
    {
        position: relative;
    }

    .switch 
    {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }

    .switch input 
    { 
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider 
    {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #de2336;
      -webkit-transition: .4s;
      transition: .2s;
      width: 35px;
    }

    .slider:before 
    {
      position: absolute;
      content: "";
      height: 10px;
      width: 10px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      -webkit-transition: .4s;
      transition: .4s;
    }

     /* Rounded sliders */
    .slider.round {
      border-radius: 34px;
    }

    .slider.round:before {
      border-radius: 50%;
    }

    input[name="checkbox"]:checked + .slider 
    {
      background-color: #5FFA39;
    }

    input[name="checkbox"]:focus + .slider 
    {
      box-shadow: 0 0 1px #5FFA39;
    }

    input[name="checkbox"]:checked + .slider:before 
    {
      -webkit-transform: translateX(17px);
      -ms-transform: translateX(17px);
      transform: translateX(17px);
    }
</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">Settings</li>
    <li class="breadcrumb-item active">Exchange Rate</li>
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
                                <label for="name">Currency</label>
                                <input type="text" class="form-control" id="currency" placeholder="">
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

<div id="modalNewCurrency" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">New Currency & Exchange Rate</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formChangeDetails">

                        <div class="card-body">

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>Currency Code</label>
                                        <input id="new_code" name="new_code" class="form-control" maxlength="3">
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>Currency Country</label>
                                        <input id="new_country" name="new_country" class="form-control">
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>Currency Name</label>
                                        <input id="new_name" name="new_name" class="form-control">
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>Currency Rate</label>
                                        <input id="new_rate" name="new_rate" class="form-control">
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="card-footer">

                            <button id="btnSubmitDetails" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitNewDetails()">
                                <i class="fa fa-dot-circle-o"></i> Add
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

<div id="modalEditCurrency" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Exchange Rate</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formChangeDetails">

                        <div class="card-body">

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>ID</label>
                                        <input id="currency_id" name="currency_id" class="form-control" readonly="readonly">
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>Currency Code</label>
                                        <input id="code" name="code" class="form-control">
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>Currency Country</label>
                                        <input id="country" name="country" class="form-control">
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>Currency Name</label>
                                        <input id="name" name="name" class="form-control">
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>Currency Rate</label>
                                        <input id="rate" name="rate" class="form-control">
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="card-footer">

                            <button id="btnSubmitDetails" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitDetails()">
                                <i class="fa fa-dot-circle-o"></i> {{ __('app.accounts.subaccounts.modal.submit') }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

<div id="modalHistoryCurrency" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Exchange Rate History</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div id="code-history"></div>

                <div id="country-history"></div>

                <div id="name-history"></div>

                <div class="card">

                    <div id="main-history-spinner" class="card-body"></div>

                    <div id="main-history-table" class="card-body"></div>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection
