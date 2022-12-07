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
    locale['mainData.no'] = "No.";
    locale['mainData.bank_image'] = "Bank Image";
    locale['mainData.bank_name'] = "Bank Name";
    locale['mainData.url'] = "Site Url";
    locale['mainData.status'] = "Status";
    locale['mainData.edit'] = "Edit";
    locale['mainData.actions'] = "Action";

    locale['option.bank.offline'] = "Offline";
    locale['option.bank.online'] = "Online";
    locale['option.bank.suspend'] = "Suspend";

    locale['tooltip.edit'] = "Edit";
    locale['tooltip.delete'] = "Delete";

    locale['info'] = "Info";
    locale['success'] = "Success";
    locale['error'] = "Error";
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

    data["name"] = $("#f_bankname").val();

    $.ajax({
        type: "GET",
        url: "/ajax/bank/bank/list",
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
                    ["id","Bank ID",true,false]
                    ,["name",locale['mainData.bank_name'],true,false]
                    ,["site_url",locale['mainData.url'],false,false]
                    ,["status",locale['mainData.status'],false,false]
                    ,["",locale['mainData.actions'],false,false]
                                    ];  

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();

        var fieldNo = utils.getDataTableFieldIdx("id",fields);
        var fieldStatus = utils.getDataTableFieldIdx("status",fields);
        var fieldActions = utils.getDataTableFieldIdx("",fields);


        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            if(mainData.results[i - 1]["status"] == "0")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-danger">'+locale['option.bank.offline']+'</span>';
            else if(mainData.results[i - 1]["status"] == "1")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+locale['option.bank.online']+'</span>';
            else 

            row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+locale['option.bank.suspend']+'</span>';

            row.cells[fieldActions].innerHTML = "";
                        
            var btnEdit = document.createElement("i");
            btnEdit.style.cursor="pointer";
            btnEdit.className = "fa fa-edit fa-2x";
            btnEdit.style.color = "#11acf4";
            btnEdit.setAttribute("data-toggle", "tooltip");
            btnEdit.setAttribute("title", 'Edit');
            btnEdit.rowId = i;
            btnEdit.style.height = '25px';
            btnEdit.style.width = '25px';
            btnEdit.onclick = showModal;

            row.cells[fieldActions].innerHTML = "";
            row.cells[fieldActions].appendChild(btnEdit);
            
        } 
    }
}

function showModal()
{

    $("#modal-bankname").val('');
    $("#modal-siteurl").val('');
    $("#status").val('');


    var bankId = mainData.results[this.rowId - 1]["id"];
    var status = mainData.results[this.rowId - 1]["status"];
    var siteUrl = mainData.results[this.rowId - 1]["site_url"];
    var bankName = mainData.results[this.rowId - 1]["name"];

    $("#modal-id").val(bankId);
    $('#status').append('<option>' + '</option>').children().remove();
    $('#status').append('{{ Helper::generateOptions($optionsBankStatus,'') }}');
    
    document.getElementById("status").value = status;
    document.getElementById("modal-siteurl").value = siteUrl;
    document.getElementById("modal-bankname").value = bankName;

    $("#modalMessage").hide();
    $("#modalAction").modal('show');
}


function editBank()
{ 
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmit","modalAction");

    $("#formAction").attr("enabled",0);

    $.ajax({
        url: "/ajax/bank/update",
        type: "POST",
        data:  new FormData($("#formAction")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            utils.stopLoadingBtn("btnSubmit","modalAction");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $("#modalAction").modal('hide');
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onModalDismissError);
            }
        },
        error: function(){}             
    }); 
}


function onModalDismiss() 
{
    window.location.href = "/bank/bank";
}

function onModalDismissError()
{
    $("#formAction").attr("enabled",1);
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
    $("#f_bankname").val("");

    filterMainData();
}

function createMainData()
{
    window.location.href = "/bank/bank/new";
}

function edit()
{   
    var id = this.rowId;
    window.location.href = "/bank/bank/details?id=" + id;
}

</script>


@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">Banking</li>
    <li class="breadcrumb-item active">Bank List</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">
        <div class="card">
            <form method="POST" id="filterForm">
                <div class="card-header" style="display:block;">
                    <strong>Filter</strong>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="form-inline ml-2">
                            <div class="form-group ml-2">
                                <label for="name">Bank Name</label>
                                <input type="text" class="form-control form-control-sm ml-2" id="f_bankname" placeholder="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> Filter</button>

                    <button type="button" class="btn btn-sm btn-danger" onclick="resetMainData()"><i class="fa fa-ban"></i> Reset</button>

                    <button type="button" class="btn btn-sm btn-primary pull-right" onclick="createMainData()"><i class="fa fa-plus"></i> Create</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div id="main-spinner" class="card-body"></div>

            <div id="main-table" class="card-body"></div>

            <div id="notes" class="card-body">Note: The date will be based on time zone GMT+7</div>
        </div>
    </div>
</div>

<div id="modalAction" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Bank</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formAction">
                        
                        <input type="hidden" id="modal-id" name="bank_id">

                        <div class="card-body">

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.banking.bank.modal.bankname') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" id="modal-bankname" name="bank_name" class="form-control">


                                </div>
                            </div>

                           <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>Site Url</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" id="modal-siteurl" name="site_url" class="form-control">

                                </div>
                            </div>

                        

                            <div class="form-group row">
                                <div class="col-sm-4">

                                    <label>{{ __('app.banking.bank.modal.status') }}</label>
                                </div>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        {{ Helper::generateOptions($optionsBankStatus,'') }}
                                    </select>
                                </div>

                            </div>
                                              
                        </div>

                        <div class="card-footer">

                            <button id="btnSubmit" type="button" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="editBank()">
                                <i class="fa fa-dot-circle-o"></i> {{ __('common.modal.submit') }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection
