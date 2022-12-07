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
    locale['member'] = "{!! __('app.reports.referral.member.username') !!}";
    locale['tier'] = "{!! __('app.reports.referral.member.tier') !!}";
    locale['amount'] = "{!! __('app.reports.referral.member.amount') !!}";
    locale['date'] = "{!! __('app.reports.referral.member.date') !!}";
    locale['created_at'] = "{!! __('app.reports.referral.member.created_at') !!}";

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
        url: "/ajax/reports/member_referral/list",
        data: data,
        success: function(data) 
        {
            if(data.length > 0)
                mainData = JSON.parse(data);
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
                ["member_id",locale['member_id'],true,false]  
                ,["member_name", locale['member'],true,false]
                ,["tier", locale['tier'],true,false]
                ,["amount", locale['amount'],true,false]
                ,["date", locale['date'],true,false]
                ,["is_reject",'Reject',false,false]
                ,["created_at",locale['created_at'],true,false]

            ];


    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();
        

        var fieldReject = utils.getDataTableFieldIdx("is_reject", fields);
        var fieldAmount = utils.getDataTableFieldIdx("amount", fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {
            row.cells[fieldAmount].innerHTML = utils.formatMoney(mainData.results[i - 1]["amount"],2);

            row.cells[fieldReject].innerHTML = mainData.results[i - 1]["is_reject"];
            row.cells[fieldReject].style.textAlign = "center";

            if(mainData.results[i - 1]["is_reject"] == 1)
                row.cells[fieldReject].innerHTML = 'Yes';
            else
                row.cells[fieldReject].innerHTML = 'No';


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
    <li class="breadcrumb-item active">{{ __('app.reports.referral.member.breadcrumb.membercredit') }}</li>
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
