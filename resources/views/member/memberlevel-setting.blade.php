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
        /*filterMainData();*/
    }));

});



var mainData;

function prepareLocale()
{
    locale['mainData.level'] = "Member Level";
    locale['mainData.turnover'] = "Turnover";
    locale['mainData.updated_at'] = "Updated At";
    locale['mainData.edit'] = "Edit";

    locale['mainData.level.new'] = "New";
    locale['mainData.level.regular'] = "Regular";
    locale['mainData.level.bronze'] = "Bronze";
    locale['mainData.level.silver'] = "Silver";
    locale['mainData.level.gold'] = "Gold";
    locale['mainData.level.platinum'] = "Platinum";

    locale['tooltip.edit'] = "Edit";
    locale['tooltip.save'] = "Save";
    locale['tooltip.cancel'] = "Cancel";

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

    $.ajax({
        type: "GET",
        url: "/ajax/merchants/merchant/memberlevel/settings/list",
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

    var fields = [   ["id", locale['mainData.level'],true,false]
                    ,["min_deposit_amt", 'Min Deposit Amount',true,true]
                    ,["updated_at", locale['mainData.updated_at'],true, false]
                    @can('permissions.edit_member_levelsetting') 
                    ,["edit",locale['mainData.edit'],false,false]
                    @endcan    
                ];

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();
        var fieldId = utils.getDataTableFieldIdx("id", fields);
        var fieldMinDeposit = utils.getDataTableFieldIdx("min_deposit_amt", fields);
                        var fieldEdit = utils.getDataTableFieldIdx("edit", fields);
        
        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            var level = mainData.results[i - 1]["id"];
            if (level == 1)
                row.cells[fieldId].innerHTML =  locale['mainData.level.new'];
            else if (level == 2)
                row.cells[fieldId].innerHTML =  locale['mainData.level.regular'];
            else if (level == 3)
                row.cells[fieldId].innerHTML =  locale['mainData.level.bronze'];
            else if (level == 4)
                row.cells[fieldId].innerHTML =  locale['mainData.level.silver'];
            else if (level == 5)
                row.cells[fieldId].innerHTML =  locale['mainData.level.gold'];
            else if (level == 6)
                row.cells[fieldId].innerHTML =  locale['mainData.level.platinum'];
            // row.cells[fieldId].innerHTML =  mainData.results[i - 1]["level"];

            
            var minAmount = mainData.results[i - 1]["min_deposit_amt"];
            row.cells[fieldMinDeposit].id = mainData.results[i - 1]["id"] + "_min";
            row.cells[fieldMinDeposit].innerHTML =  utils.formatMoney(minAmount);

            @can('permissions.edit_member_levelsetting') 

            if(level != 1)
            {
                //Edit button
                var btnEdit = document.createElement("i");
                btnEdit.className = "fa fa-edit fa-2x";
                btnEdit.onclick = showEdit;
                btnEdit.rowId = i;
                btnEdit.style.cursor = "pointer";
                btnEdit.style.color = "#11acf4";
                btnEdit.setAttribute("data-toggle", "tooltip");
                btnEdit.setAttribute("title", locale['tooltip.edit']);
                row.cells[fieldEdit].innerHTML = "";
                row.cells[fieldEdit].id = mainData.results[i - 1]["id"] + "_edit";
                row.cells[fieldEdit].appendChild(btnEdit);
                row.cells[fieldEdit].className = "pb-0";
            }
            else
            {
                row.cells[fieldEdit].innerHTML = "";
            }
            @endcan
        }
    }
}

function showEdit() 
{
    var containerId = "main-table";
    loadMainData(containerId);

    rowId = this.rowId;
    var id = mainData.results[this.rowId - 1]["id"];
    var level = mainData.results[this.rowId - 1]["level"];
    var minAmount = mainData.results[this.rowId - 1]["min_deposit"];
    var updatedAt = mainData.results[this.rowId - 1]["updated_at"];

    var a = document.createElement('a');
    a.className = "iconBtn";
    a.setAttribute("data-toggle", "tooltip");
    a.setAttribute("title", locale['tooltip.save']);
    a.style.cursor = "pointer";
    a.rowId = rowId;
    a.onclick = saveEdit;

    var i = document.createElement('i');
    i.className = "fa fa-save fa-2x";
    a.append(i);

    var a1 = document.createElement('a');
    a1.className = "iconBtn";
    a1.setAttribute("data-toggle", "tooltip");
    a1.setAttribute("title", locale['tooltip.cancel']);
    a1.rowId = rowId;
    a1.onclick = cancelEdit;
    a1.style.cursor = "pointer";

    var i1 = document.createElement('i');
    i1.className = "fa fa-times-circle fa-2x";
    a1.append(i1);

    var input = document.createElement("input");
    input.id = "new_min";
    input.type = "text";
    input.value = utils.formatMoney(minAmount);

    $('.table-responsive > table > tbody > tr:nth-child(' + rowId + ') > td:nth-child(2)').html("");
    $('.table-responsive > table > tbody > tr:nth-child(' + rowId + ') > td:nth-child(2)').append(input);
    $('.table-responsive > table > tbody > tr:nth-child(' + rowId + ') > td:nth-child(4)').html("");
    $('.table-responsive > table > tbody > tr:nth-child(' + rowId + ') > td:nth-child(4)').append(a);
    $('.table-responsive > table > tbody > tr:nth-child(' + rowId + ') > td:nth-child(4)').append(a1);

}

function saveEdit(rowId) 
{
    var id = mainData.results[this.rowId - 1]["id"];
    var level = mainData.results[this.rowId - 1]["level"];
    var newMin = $('#new_min').val();

    var data = {};

    data["id"] = id;
    data["level"] = level;
    data["minAmount"] = newMin;

    $.ajax({
        type: "POST",
        url: "/ajax/merchants/merchant/memberlevel/settings/update",
        data: data,
        success: function(data) 
        {
            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);

            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,"");

            }
        }
    });

}

function cancelEdit() 
{
    var containerId = "main-table";
    loadMainData(containerId);
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

@endsection

@section('content')

<ol class="breadcrumb">
    <li class="breadcrumb-item">Member Management</li>
    <li class="breadcrumb-item active">Member Level Settings</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <div id="main-spinner" class="card-body"></div>

            <div id="main-table" class="card-body"></div>

            <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>

        </div>
    </div>
</div>

@endsection
