@extends('layouts.app')

@section('head')
<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

    var date = utils.getToday();
    var s_date = utils.getParameterByName("s_date");
    var e_date = utils.getParameterByName("e_date");

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
    locale['mainData.id'] = "{!! __('app.settings.log.maindata.id') !!}";
    locale['mainData.username'] = "{!! __('app.settings.log.maindata.username') !!}";
    locale['mainData.operator_admin'] = "{!! __('app.settings.log.maindata.operator_admin') !!}";
    locale['mainData.path'] = "{!! __('app.settings.log.maindata.path') !!}";
    locale['mainData.query'] = "{!! __('app.settings.log.maindata.query') !!}";
    locale['mainData.action'] = "{!! __('app.settings.log.maindata.action') !!}";
    locale['mainData.ipaddress'] = "{!! __('app.settings.log.maindata.ipaddress') !!}";
    locale['mainData.timestamp'] = "{!! __('app.settings.log.maindata.timestamp') !!}";
    locale['mainData.actions'] = "{!! __('app.settings.log.maindata.actions') !!}";

    locale['mainData.create'] = "{!! __('app.settings.log.maindata.create') !!}";
    locale['mainData.deposit_withdraw'] = "{!! __('app.settings.log.maindata.deposit_withdraw') !!}";
    locale['mainData.product_setting'] = "{!! __('app.settings.log.maindata.product_setting') !!}";
    locale['mainData.update'] = "{!! __('app.settings.log.maindata.update') !!}";
    locale['mainData.pt_setting'] = "{!! __('app.settings.log.maindata.pt_setting') !!}";
    locale['mainData.credit'] = "{!! __('app.settings.log.maindata.credit') !!}";

    locale['mainData.actions.details'] = "{!! __('app.settings.log.maindata.actions.details') !!}";

    locale['old_data'] = "{!! __('app.settings.log.details.olddata') !!}";
    locale['new_data'] = "{!! __('app.settings.log.details.newdata') !!}";
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
    data["action"] = $("#f_action").val();

    $.ajax({
        type: "GET",
        url: "/ajax/settings/log/list",
        data: data,
        success: function(data) 
        {
            if(data.length > 0)
            {
                mainData = JSON.parse(data);
            }
            else
                mainData = [];
            
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
                    ,["operator_admin",locale['mainData.operator_admin'],true,false]                 
                    ,["username",locale['mainData.username'],true,false]
                    ,["action_details",locale['mainData.actions.details'],false,false]
                    ,["ip_address",locale['mainData.ipaddress'],false,false]
                    ,["timestamp",locale['mainData.timestamp'],true,false]
                    ,["",locale['mainData.actions'],false,false]
                ];  

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();

        var fieldActions = utils.getDataTableFieldIdx("",fields);
        
        for (var i = 1, row; row = table.rows[i]; i++) 
        {
            actionName = mainData.results[i - 1]["action_details"];

            var btnDetails = document.createElement("button");
            btnDetails.className = "btn btn-sm btn-success";
            btnDetails.action = actionName;
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
    var operatorAdmin = mainData.results[this.rowId - 1]["operator_admin"];
    var username = mainData.results[this.rowId - 1]["username"];
    var action = this.action;
    var oldData = mainData.results[this.rowId - 1]["data_old"];
    var newData = mainData.results[this.rowId - 1]["data_new"];
    var timeStamp = mainData.results[this.rowId - 1]["timestamp"];
    var ipAddress = mainData.results[this.rowId - 1]["ip_address"];

    var newDataList = newData;

    if(newDataList['Password'])
        newDataList['Password'] = '*';
    
    var oldDataList = oldData;

    $("#details-admin").html(operatorAdmin);
    $("#details-username").html(username);
    $("#details-action").html(action);
    $("#details-time").html(timeStamp);
    $("#details-ip").html(ipAddress);
    // $("#details-action")[0].style.textTransform = "Capitalize";

    //create table
    var table = document.createElement("table");
    table.className = "table table-bordered table-sm";
    table.id = "content_table";

    div = document.getElementById("modal_table");
    div.innerHTML = "";
    div.appendChild(table);

    //header fields
    var fields = [  
                    [""]
                    ,[locale['old_data']]
                    ,[locale['new_data']]
                 ];

    //create header
    var tHead = table.createTHead();
    var row = tHead.insertRow(0); 
    var tBody = document.createElement('tbody');

    table.appendChild(tBody);

    //header - row 1
    for(var z = 0; z < fields.length; z++)
    {
        var fieldTitle = fields[z][0];

        var th = document.createElement('th');

        th.style.textAlign = 'center'; 
        th.innerHTML = fieldTitle;  

        row.appendChild(th);
    }

    var item = Object.keys(newDataList);
    
    for(var a = 0, k = 1; a < item.length; a++)
    {
        var key = item[a];

        var key_id = key.replace(/ /g, "_");


        var row2 = document.createElement('tr');

        var td = document.createElement("td");

        var td_old= document.createElement("td");
        td_old.id = "old_" + key;

        if(oldDataList)
        {
            if(!oldDataList[key])
            {
                td_old.innerHTML = "-";
            }
            else
            {
               td_old.innerHTML = oldDataList[key]; 
            }
        }

        var td_new = document.createElement("td");
        td_new.id = "new_" + key;

        if(!newDataList[key])
        {
            td_new.innerHTML = "-";
        }
        else
        {
           td_new.innerHTML = newDataList[key]; 
        }

        td.innerHTML = "<span style='font-weight:bold;' colspan='3'>" + key + "</span>";

        row2.appendChild(td);
        row2.appendChild(td_old);
        row2.appendChild(td_new);

        tBody.appendChild(row2);

        k++;
    }


    groupTable($('#content_table tr:has(td)'),0,3);
    $('#content_table .deleted').remove();

    $("#modalDetails").modal('show');
}

String.prototype.replaceAt=function(index, char) 
{
    var a = this.split("");

    for(var c = 0; c < char.length; c++)
    {
       a[index] = char[c]; 

       index++;
    }
    
    return a.join("");
}

function groupTable($rows, startIndex, total)
{
    //rows = jQuery object of table rows to be grouped
    //startIndex = index of first column to be grouped
    //total = total number of columns to be grouped

    if(total === 0)
    {
        return;
    }

    var i, currentIndex = startIndex, count=1, lst=[];

    var tds = $rows.find('td:eq('+ currentIndex +')');

    var ctrl = $(tds[0]);

    lst.push($rows[0]);

    for (i=1;i<=tds.length;i++)
    { 
        //delete the row with same content
        if (ctrl.text() ==  $(tds[i]).text())
        {
            count++;
            $(tds[i]).addClass('deleted');
            lst.push($rows[i]);
        }
        else
        {
            if (count>1)
            {
                ctrl.attr('rowspan',count);
                groupTable($(lst), startIndex + 1, total - 1);//proceed next column
            }
            count=1;
            lst = [];
            ctrl = $(tds[i]);
            lst.push($rows[i]);
        }
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
                                <label for="name">{{ __('app.settings.log.filter.username') }}</label>
                                <input type="text" class="form-control" id="f_username" placeholder="">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('common.filter.fromdate') }}</label>
                                <input type="text" class="form-control" name="s_date" id="s_date" placeholder="dd/mm/yyyy">
                                <input type="hidden" name="s_date1" id="s_date1">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>{{ __('common.filter.todate') }}</label>
                                <input type="text" class="form-control" name="e_date" id="e_date" placeholder="dd/mm/yyyy">
                                <input type="hidden" name="e_date1" id="e_date1">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <label for="name">{{ __('app.settings.log.filter.action') }}</label>
                            <select class="form-control" id="f_action" name="action">
                                <option value="" selected>{{ __('app.settings.log.filter.action.all') }}</option>
                                <option value="1">{{ __('app.settings.log.filter.action.create_merchant') }}</option>
                                <option value="2">{{ __('app.settings.log.filter.action.update_merchant') }}</option>
                                <option value="3">{{ __('app.settings.log.filter.action.merchant_activation') }}</option>
                                <option value="4">{{ __('app.settings.log.filter.action.merchant_settings') }}</option>
                                <option value="5">{{ __('app.settings.log.filter.action.create_bets') }}</option>
                                <option value="6">{{ __('app.settings.log.filter.action.update_bets') }}</option>
                                <option value="7">{{ __('app.settings.log.filter.action.create_coins') }}</option>
                                <option value="8">{{ __('app.settings.log.filter.action.update_coins') }}</option>
                                <option value="9">{{ __('app.settings.log.filter.action.create_admin') }}</option>
                                <option value="10">{{ __('app.settings.log.filter.action.update_admin') }}</option>
                                <option value="11">{{ __('app.settings.log.filter.action.admin_change_pwd') }}</option>
                                <option value="12">{{ __('app.settings.log.filter.action.create_sub') }}</option>
                                <option value="13">{{ __('app.settings.log.filter.action.update_sub') }}</option>
                                <option value="14">{{ __('app.settings.log.filter.action.sub_change_pwd') }}</option>
                                <option value="15">{{ __('app.settings.log.filter.action.change_pwd') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> {{ __('common.filter.submit') }}</button>

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
                <h4 class="modal-title">{{ __('app.settings.log.details.title') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card" style="border:none;">

                    <div class="card-body" style="padding:0 0.5rem;">
                        <div class="row">
                            <div class="col-sm-12">
                                <label>{{ __('app.settings.log.details.operatoradmin') }}:</label>
                                <label id="details-admin" class="text_bold"></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <label>{{ __('app.settings.log.details.username') }}:</label>
                                <label id="details-username" class="text_bold"></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <label>{{ __('app.settings.log.details.action') }}:</label>
                                <label id="details-action" class="text_bold"></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <label>{{ __('app.settings.log.details.timestamp') }}:</label>
                                <label id="details-time" class="text_bold"></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <label>{{ __('app.settings.log.details.ip') }}:</label>
                                <label id="details-ip" class="text_bold"></label>
                            </div>
                        </div>
                    </div>

                    <div id="modal_table" class="card-body"></div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" id="close" data-dismiss="modal">{{ __('common.modal.ok') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection
