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
    locale['mainData.name'] = "{!! __('app.admins.admins_role.maindata.name') !!}";

    locale['mainData.edit'] = "{!! __('app.admins.admins_role.maindata.edit') !!}";
    locale['mainData.delete'] = "{!! __('app.admins.admins_role.maindata.delete') !!}";

    locale['tooltip.edit'] = "{!! __('app.admins.admins_role.tooltip.edit') !!}";
    locale['tooltip.delete'] = "{!! __('app.admins.admins_role.tooltip.delete') !!}";


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

    data["name"] = $("#name").val();

    // console.log(data);

    $.ajax({
        type: "GET",
        url: "/ajax/admins/roles/list",
        data: data,
        success: function(data) 
        {
            mainData = JSON.parse(data);
            console.log(mainData);
            loadMainData(containerId);
        }
    });
}

function loadMainData(containerId)
{
    $("#main-spinner").hide();
    $("#main-table").show();

    var fields = [                           
                    ["type",locale['mainData.name'],false,false]
                    ,["edit",locale['mainData.edit'],false,false]
                    ,["delete",locale['mainData.delete'],false,false]
                ];  

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();

        var fieldEdit = utils.getDataTableFieldIdx("edit",fields);
        var fieldDelete = utils.getDataTableFieldIdx("delete",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {  
            //edit
            var btnEdit = document.createElement("i");
            btnEdit.className = "fa fa-edit fa-2x";
            btnEdit.onclick = editRolePermission;
            btnEdit.rowId = i;
            btnEdit.style.cursor = "pointer";
            btnEdit.style.color = "#11acf4";
            btnEdit.setAttribute("data-toggle", "tooltip");
            btnEdit.setAttribute("title", locale['tooltip.edit']);
            row.cells[fieldEdit].innerHTML = "";
            row.cells[fieldEdit].appendChild(btnEdit);
            row.cells[fieldEdit].className = "pb-0";

            //delete
            var btnDelete = document.createElement("i");
            btnDelete.className = "fa fa-trash fa-2x ml-2";
            btnDelete.onclick = showDeleteModal;
            btnDelete.rowId = i;
            btnDelete.style.cursor = "pointer";
            btnDelete.style.color = "#11acf4";
            btnDelete.setAttribute("data-toggle", "tooltip");
            btnDelete.setAttribute("title", locale['tooltip.delete']);
            row.cells[fieldDelete].innerHTML = "";
            row.cells[fieldDelete].appendChild(btnDelete);
            row.cells[fieldDelete].className = "pb-0";
        } 
    }
}

function showDeleteModal()
{   
    var modal = document.createElement("div");
    modal.className = "modal fade";
    modal.id = "modal-delete";
    modal.setAttribute("role", "dialog");     

    var dialog = document.createElement("div");
    dialog.className = "modal-dialog";
    
    dialog.setAttribute("role", "document");   
    modal.appendChild(dialog);              

    var content = document.createElement("div");
    content.className = "modal-content";
    dialog.appendChild(content);   

    var header = document.createElement("div");
    header.className = "modal-header";
    content.appendChild(header);   

    var title = document.createElement("h4");
    title.className = "modal-title";
    title.innerHTML = "{!! __('app.admins.admins_role.delete') !!}";
    header.appendChild(title);

    var btnX = document.createElement("button");
    btnX.className = "close";
    btnX.setAttribute("data-dismiss", "modal");
    btnX.innerHTML = "×";
    header.appendChild(btnX);

    var body = document.createElement("div");
    body.className = "modal-body";

    body.innerHTML = "{!! __('app.admins.admins_role.delete.body') !!}";

    content.appendChild(body); 

    var footer = document.createElement("div");
    footer.className = "modal-footer";
    content.appendChild(footer); 

    var btnDelete = document.createElement("button");
    btnDelete.className = "btn btn-primary";
    btnDelete.setAttribute("data-dismiss", "modal");
    btnDelete.innerHTML = "{!! __('app.admins.admins_role.delete') !!}";
    btnDelete.id = "btn-delete";
    btnDelete.rowId = this.rowId;
    btnDelete.onclick = deleteGame;
    footer.appendChild(btnDelete);

    $(modal).modal('show');

    // speed up focus on close btn
    setTimeout(function (){
        $(btnDelete).focus();
    }, 150);

    //fail safe to focus
    $(modal).on('shown.bs.modal', function() {
        $(btnDelete).focus();
    });
}

function deleteGame()
{
    utils.startLoadingBtn("btn-delete","modal-delete");

    var name = mainData.results[this.rowId - 1]["type"];

    var log_data = '{"name":"'+name+'"}';

    $.ajax({
        url: "/ajax/admins/roles/delete",
        type: "POST",
        data:  {"name": name, "log_old": log_data},
        success: function(data)
        {
            // console.log(data);

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.stopLoadingBtn("btn-delete","modal-delete");

                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);
                $("#modal-delete").modal('hide');

            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status, getMainData);
                $("#modal-delete").modal('hide');
            }
        },
        error: function(){}
    }); 
}

function editRolePermission()
{
    var id = mainData.results[this.rowId - 1]["id"];

    window.location.href = "/admins/roles/permission?id=" + id;
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
    $("#name").val("");

    filterMainData();
}

function createMainData()
{
    window.location.href = "/admins/roles/new";
}

</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.admins.admin.breadcrumb.admins') }}</li>
    <li class="breadcrumb-item active">{{ __('app.admins.admin.breadcrumb.admins_role') }}</li>
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
                                <label for="name">{{ __('app.admins.admins_role.name') }}</label>
                                <input type="text" class="form-control" id="name" autocomplete="off" placeholder="">
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

@endsection
