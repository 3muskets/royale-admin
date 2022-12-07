@extends('layouts.app')

@section('content')
<script type="text/javascript">
 $(document).ready(function() 
{
    prepareLocale();

    getMainData();
});

function prepareLocale()
{
    locale['mainData.id'] = "Id";
    locale['mainData.name'] = "Name";
    locale['mainData.status'] = "Remark";
    locale['mainData.action'] = "Status";
    locale['mainData.updated_at'] = "Updated At";

    locale['info'] = "Info";
    locale['success'] = "Success";
    locale['error'] = "Error";
}

var mainData;
var refreshMainData = false;

function getMainData() 
{
    var containerId = "main-data";

    $("#main-spinner").show();
    $("#main-data").hide();
    $("#notes").hide();

    var data = utils.getDataTableDetails(containerId);

    // data["username"] = $("#f_username").val();
    $.ajax({
        type: "GET",
        url: "/ajax/product/list",
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
    $("#main-data").show();

    var fields = [                           
                    ["prd_id",locale['mainData.id'],true,false]
                    ,["prd_name",locale['mainData.name'],true,false]
                    ,["status",locale['mainData.status'],false,false]
                    ,["action",locale['mainData.action'],false,false]
                    ,["updated_at",locale['mainData.updated_at'],false,false]
                ];  

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();

        var fieldStatus = utils.getDataTableFieldIdx("status",fields);
        var fieldActions = utils.getDataTableFieldIdx("action",fields);
        
        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            //status
            if(mainData.results[i - 1]["status"] == "0")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-danger">'+mainData.results[i-1]["prd_name"]+' is under Maintenance</span>';
            else if(mainData.results[i - 1]["status"] == "1")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+mainData.results[i-1]["prd_name"]+' is available.</span>';
            else if(mainData.results[i - 1]["status"] == "2")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">Currently '+mainData.results[i-1]["prd_name"]+' is not available. Coming Soon.</span>';
            else 
                row.cells[fieldStatus].innerHTML = '';
        
            var div = document.createElement("div");
            div.className = "onoffswitch";
            div.rowId = mainData.results[i-1].prd_id;
            div.game = mainData.results[i-1]["prd_name"];

            var input = document.createElement("input");
            input.type = "checkbox";
            input.name = "onoffswitch";
            input.className = "onoffswitch-checkbox";
            input.tabIndex  = "0";
            input.id = "switch" + mainData.results[i-1].prd_id;

            var label = document.createElement("label");
            label.className = "onoffswitch-label";
            label.for = "switch" + mainData.results[i-1].prd_id;

            var spanInner = document.createElement("span");
            spanInner.className = "onoffswitch-inner";
            var spanSwitch = document.createElement("span");
            spanSwitch.className = "onoffswitch-switch";

            div.onclick = update;

            if(mainData.results[i - 1]["status"] == "1")
            {
                input.checked = "true";
                div.newStatus = 0;
            }
            else
            {
                input.checked = "";
                div.newStatus = 1;
            }

            label.appendChild(spanInner);
            label.appendChild(spanSwitch);

            div.appendChild(input);
            div.appendChild(label);

            row.cells[fieldActions].innerHTML = "";
            row.cells[fieldActions].appendChild(div);
            
        } 
    }
}

function update() 
{
    onOff = document.getElementById('switch'+this.rowId);

    if (onOff.checked == true)
        onOff.checked = false;
    else
        onOff.checked = true;

    data = {};

    data["status"] = this.newStatus;
    data["prd_name"] = this.game;
    data["prd_id"] = this.rowId;

    $.ajax({
        type: "POST",
        url: "/ajax/product/update",
        data: data,
        success: function(data) 
        {

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status, getMainData);
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

</script>

<style type="text/css">
    .onoffswitch {
        position: relative; width: 65px;
        -webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
    }
    .onoffswitch-checkbox {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .onoffswitch-label {
        display: block; overflow: hidden; cursor: pointer;
        border: 2px solid #999999; border-radius: 15px;
    }
    .onoffswitch-inner {
        display: block; width: 200%; margin-left: -100%;
        transition: margin 0.3s ease-in 0s;
    }
    .onoffswitch-inner:before, .onoffswitch-inner:after {
        display: block; float: left; width: 50%; height: 23px; padding: 0; line-height: 23px;
        font-size: 12px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold;
        box-sizing: border-box;
    }
    .onoffswitch-inner:before {
        content: "ON";
        padding-left: 8px;
        background-color: #34A7C1; color: #FFFFFF;
    }
    .onoffswitch-inner:after {
        content: "OFF";
        padding-right: 8px;
        background-color: #EEEEEE; color: #999999;
        text-align: right;
    }
    .onoffswitch-switch {
        display: block; width: 11px; margin: 6px;
        background: #FFFFFF;
        position: absolute; top: 0; bottom: 0;
        right: 38px;
        border: 2px solid #999999; border-radius: 15px;
        transition: all 0.3s ease-in 0s; 
    }
    .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
        margin-left: 0;
    }
    .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
        right: 0px; 
    }
</style>


<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item active">Product Setting</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">
        <div class="card">
            <div class="card-header">
                <strong>Product List</strong>
            </div>
            <div id="main-spinner" class="card-body"></div>
            <div class="card-body" id="main-data">
            </div>     
        </div>
    </div>
</div>


@endsection
