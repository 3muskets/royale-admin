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
    locale['id'] = "ID";
    locale['deposit_amount'] = "Deposit Amount";
    locale['promo_amount'] = "Promotion Amount";
    locale['turnover'] = "Turnover";
    locale['target_turnover'] = "Target Turnover";
    locale['target_winover'] = "Target Winover";
    locale['win_loss'] = "Win Loss";
    locale['status'] = "Status";
    locale['promo_name'] = "Promotion Name";
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
        url: "/ajax/reports/promotion/list",
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
                ["id", locale['id'],true,false]
                ,["promo_name", locale['promo_name'],false,false]
                ,["member_id",locale['member_id'],true,false]  
                ,["username",locale['member_name'],true,false]  
                ,["deposit_amount", locale['deposit_amount'],true,false]
                ,["promo_amount", locale['promo_amount'],true,false]
                ,["turnover", locale['turnover'],true,false]
                ,["target_turnover", locale['target_turnover'],true,false]
               /* ,["target_winover", locale['target_winover'],true,false]*/
                ,["win_loss", locale['win_loss'],true,false]
                ,["status_desc", locale['status'],false,false]
                ,["created_at",locale['created_at'],true,false]

            ];


    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();
        

        var fieldsDepositAmount = utils.getDataTableFieldIdx("deposit_amount", fields);
        var fieldsPromoAmount = utils.getDataTableFieldIdx("promo_amount", fields);
        var fieldsTurnover = utils.getDataTableFieldIdx("turnover", fields);
        var fieldsTargetTurnover = utils.getDataTableFieldIdx("target_turnover", fields);
        var fieldsWinloss = utils.getDataTableFieldIdx("win_loss", fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {
            $provider = mainData.results[i - 1]["provider"];

            row.cells[fieldsDepositAmount].innerHTML = utils.formatMoney(mainData.results[i - 1]["deposit_amount"],2);
            row.cells[fieldsPromoAmount].innerHTML = utils.formatMoney(mainData.results[i - 1]["promo_amount"],2);
            row.cells[fieldsTurnover].innerHTML = utils.formatMoney(mainData.results[i - 1]["turnover"],2);
            row.cells[fieldsTargetTurnover].innerHTML = utils.formatMoney(mainData.results[i - 1]["target_turnover"],2);
            row.cells[fieldsWinloss].innerHTML = utils.formatMoney(mainData.results[i - 1]["win_loss"],2);


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
    <li class="breadcrumb-item active">Promotion Report</li>
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
