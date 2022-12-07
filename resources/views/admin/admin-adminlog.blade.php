@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    input = document.getElementsByTagName('input');
    for(i = 0; i < input.length; i++) 
    {
        input[i].addEventListener("keyup", function(event) {
          if (event.keyCode === 13) 
          {
           event.preventDefault();
           document.getElementById("submit").click();
          }
        });
    }
    
    prepareLocale();

    // datepicker
    var date = utils.getTodayDB();
    var s_date = utils.getParameterByName("s_date")
    var e_date = utils.getParameterByName("e_date")

    $("#s_date").val(utils.formattedDate(s_date));
    $("#e_date").val(utils.formattedDate(e_date));

    $("#s_date1").val(s_date);
    $("#e_date1").val(e_date);

    if (!s_date) 
    {
        utils.datepickerStart('s_date','e_date','s_date1',date);
    }
    if (!e_date) 
    {
        utils.datepickerEnd('s_date','e_date','e_date1',date);
    }
    if (s_date == "") 
    {
        document.getElementById('s_date').value = "";
        document.getElementById('s_date1').value = "";
    }
    if (e_date == "") 
    {
        document.getElementById('e_date').value = "";
        document.getElementById('e_date1').value = "";
    }  

    utils.datepickerStart('s_date','e_date','s_date1','');
    utils.datepickerEnd('s_date','e_date','e_date1','');

    utils.createSpinner("main-spinner");

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));
});

function prepareLocale()
{
    locale['mainData.id'] = "Id";
    locale['mainData.username'] = "Username";
    locale['mainData.path'] = "Path";
    locale['mainData.query'] = "Query";
    locale['mainData.action'] = "Action";
    locale['mainData.ipaddress'] = "IP address";
    locale['mainData.timestamp'] = "Timestamp";
    locale['mainData.actions'] = "Actions";
    locale['mainData.create'] = "Create";
    locale['mainData.update'] = "Update";
    locale['mainData.delete'] = "Delete";
    locale['mainData.unlock'] = "Unlock";
    locale['mainData.map'] = "Map";
    locale['mainData.unmap'] = "Unmap";

    locale['mainData.actions.details'] = "Details";
}

var mainData;

function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var data = utils.getDataTableDetails(containerId);

    data["username"] = $("#f_username").val();
    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();


    $.ajax({
        type: "GET",
        url: "/ajax/admins/admin/admin_log",
        data: data,
        success: function(data) 
        {
            mainData = JSON.parse(data);
            // console.log(mainData);
            loadMainData(containerId);
        }
    });
}

function loadMainData(containerId)
{
    $("#main-spinner").hide();
    $("#main-table").show();
    
    var fields = [         
                    ["id",locale['mainData.id'],false,false]                  
                    ,["username",locale['mainData.username'],true,false]
                    ,["path",locale['mainData.path'],false,false]
                    ,["query",locale['mainData.query'],false,false]
                    ,["action",locale['mainData.action'],false,false]
                    ,["ip_address",locale['mainData.ipaddress'],false,false]
                    ,["timestamp",locale['mainData.timestamp'],true,false]
                    ,["",locale['mainData.actions'],false,false]
                ];  

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();

        var fieldActions = utils.getDataTableFieldIdx("",fields);
        var fieldAction = utils.getDataTableFieldIdx("action",fields);
        
        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            if (mainData.results[i-1].action == "create") 
            {
                row.cells[fieldAction].innerHTML = locale['mainData.create'];
            }
            else if (mainData.results[i-1].action == "unlock") 
            {
                row.cells[fieldAction].innerHTML = locale['mainData.unlock'];
            }
            else if (mainData.results[i-1].action == "update") 
            {
                row.cells[fieldAction].innerHTML = locale['mainData.update'];
            }
            else if (mainData.results[i-1].action == "delete") 
            {
                row.cells[fieldAction].innerHTML = locale['mainData.delete'];
            }
            else if (mainData.results[i-1].action == "map") 
            {
                row.cells[fieldAction].innerHTML = locale['mainData.map'];
            }
            else if (mainData.results[i-1].action == "unmap") 
            {
                row.cells[fieldAction].innerHTML = locale['mainData.unmap'];
            }
            

            var btnDetails = document.createElement("button");
            btnDetails.className = "btn btn-sm btn-success";
            btnDetails.onclick = showDetailsModal;
            btnDetails.rowId = i;

            var icon = document.createElement("i");
            icon.className = "fa fa-info-circle";
            btnDetails.appendChild(icon);

            row.cells[fieldActions].innerHTML = "";
            row.cells[fieldActions].appendChild(btnDetails);
        
        } 
    }
}

function showDetailsModal()
{
    var id = mainData.results[this.rowId - 1]["id"];
    var oldData = mainData.results[this.rowId - 1]["data_old"];
    var newData = mainData.results[this.rowId - 1]["data_new"];

    if(oldData === null)
    {
        $("#details-old-data-title").hide();
        $("#details-old-data-content").hide();
    }
    else
    {
        $("#details-old-data-title").show();
        $("#details-old-data-content").show();
    }

    $("#details-id").html(id);
    $("#details-old-data").html(oldData);
    $("#details-new-data").html(newData);

    $("#modalDetails").modal('show');
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
    $("#e_date, #e_date1").val("");
    $("#s_date, #s_date1").val("");

    filterMainData();
}

</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">Admins</li>
    <li class="breadcrumb-item active">Log</li>
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
                                <label for="name">Username</label>
                                <input type="text" class="form-control" id="f_username" placeholder="">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <label for="name">{{ __('common.filter.fromdate') }}</label>
                            <input type="text" style="font-size: 16px; min-height: 2.2em;" class="form-control date-picker" name="s_date" id="s_date" placeholder="dd/mm/yyyy">
                                <input type="hidden" name="s_date1" id="s_date1">
                        </div>

                        <div class="col-sm-2">
                            <label>{{ __('common.filter.todate') }}</label>
                            <input type="text" style="font-size: 16px; min-height: 2.2em;" class="form-control date-picker" name="e_date" id="e_date" placeholder="dd/mm/yyyy">
                                <input type="hidden" name="e_date1" id="e_date1">
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="button" id="submit" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> {{ __('common.filter.submit') }}</button>

                    <button type="button" class="btn btn-sm btn-danger" onclick="resetMainData()"><i class="fa fa-ban"></i> {{ __('common.filter.reset') }}</button>

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

<div id="modalDetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Details</h4>
                <button class="close" id="close" data-dismiss="modal" style="color: #fff;">×</button>
            </div>
            <div class="modal-body">

                <div class="card">
                    
                    <div class="card-body">

                        <div class="row">
                            <div class="col-sm-12">
                                <label style=>Id</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div id="details-id" class="alert alert-info" role="alert"></div>
                            </div>
                        </div>

                        <div class="row" id="details-old-data-title">
                            <div class="col-sm-12">
                                <label style=>Old data</label>
                            </div>
                        </div>

                        <div class="row" id="details-old-data-content">
                            <div class="col-sm-12">
                                <div id="details-old-data" class="alert alert-info" role="alert"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <label>New data</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div id="details-new-data" class="alert alert-info" role="alert"></div>
                            </div>
                        </div>  

                    </div>

                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="close" data-dismiss="modal">{{ __('common.modal.ok') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection
