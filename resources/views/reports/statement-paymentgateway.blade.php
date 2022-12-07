@extends('layouts.app')

@section('head')
<script type="text/javascript">

var date = utils.getToday();

$(document).ready(function() 
{
    prepareLocale();

    utils.datepickerStart('s_date','e_date','s_date1',date);
    utils.datepickerEnd('s_date','e_date','e_date1',date);
    
    utils.createSpinner("main-spinner");

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    if(auth.getUserLevel() == 3)
    {
        $("#fg_tier4").css("display","none");
    }

});

function prepareLocale()
{   


    locale['member_id'] = "{!! __('app.reports.referral.member.member_id') !!}";
    locale['member_name'] = "Member Name";
    locale['txn_id'] = "Txn ID";
    locale['amount'] = "Amount";
    locale['status'] = "Status";
    locale['provider'] = "Provider Type";
    locale['created_at'] = "Created At";

}

var mainData;
var refreshMainData = false;

function getMainData() 
{
    var containerId = "main-table";
    
    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var data = utils.getDataTableDetails(containerId);

    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();
    data['username'] = $("#username").val();
    data["tier4"] = $("#f_tier4").val();

    $.ajax({
        type: "GET",
        url: "/ajax/reports/statement/paymentgateway/list",
        data: data,
        success: function(data) 
        {
            if(data.length > 0)
                mainData = JSON.parse(data);
            else
                mainData = [];
            

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
                ["txn_id", locale['txn_id'],true,false]
                ,["member_id",locale['member_id'],true,false]  
                ,["username",locale['member_name'],true,false]  
                ,["provider_text", locale['provider'],false,false]
                ,["amount", locale['amount'],true,false]
                ,["status_desc", locale['status'],false,false]
                ,["created_at",locale['created_at'],true,false]

            ];


    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();
        

        var fieldAmount = utils.getDataTableFieldIdx("amount", fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {
            $provider = mainData.results[i - 1]["provider"];

            row.cells[fieldAmount].innerHTML = utils.formatMoney(mainData.results[i - 1]["amount"],2);

            if($provider == 'cw')
                row.cells[fieldAmount].innerHTML += ' USDT';
            else
                row.cells[fieldAmount].innerHTML += ' MYR';


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
{;
    $("#e_date, #e_date1").val("");
    $("#s_date, #s_date1").val("");
    $("#username, #f_tier4").val("");

    filterMainData();
}

</script>

<style type="text/css">
    
</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.reports.referral.member.breadcrumb.report') }}</li>
    <li class="breadcrumb-item active">Statement By Payment Gateway</li>
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
                                <label for="name">{{ __('common.filter.fromdate') }}</label>
                                <input type="text" class="form-control" name="s_date" id="s_date" placeholder="dd/mm/yyyy" autocomplete="">
                                <input type="hidden" name="s_date1" id="s_date1">
                            </div>

                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>{{ __('common.filter.todate') }}</label>
                                <input type="text" class="form-control" name="e_date" id="e_date" placeholder="dd/mm/yyyy" autocomplete="">
                                <input type="hidden" name="e_date1" id="e_date1">
                            </div>

                        </div>


                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('app.reports.credit.member.filter.member') }}</label>
                                <input type="text" class="form-control" id="username" autocomplete="">
                            </div>
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

@endsection
