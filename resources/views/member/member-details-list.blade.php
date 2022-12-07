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
    locale['agent'] = "{!! __('app.reports.winloss.maindata.admin') !!}"; 
    locale['username'] = "{!! __('app.reports.winloss.maindata.username') !!}";
    locale['win_loss'] = "{!! __('app.reports.winloss.maindata.winloss.amt') !!}"
    locale['total_wager'] = "{!! __('app.reports.winloss.maindata.totalwager') !!}";
    locale['turnover'] = "{!! __('app.reports.winloss.maindata.turnover') !!}";
    locale['sma_pt_amt'] = "{!! __('app.reports.winloss.maindata.sma.pt.amt') !!}";
    locale['ag_comm_amt'] = "{!! __('app.reports.winloss.maindata.ag.comm.amt') !!}";
    locale['ttl_deposit'] = "{!! __('app.merchants.member.details.maindata.deposit') !!}";
    locale['ttl_withdraw'] = "{!! __('app.merchants.member.details.maindata.withdraw') !!}";
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
    
    data["tier4"] = $("#tier4").val();
    data["username"] = $("#username").val();
    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();

    $.ajax({
        type: "GET",
        url: "/merchants/merchant/sma/member/list",
        data: data,
        success: function(data) 
        {
            if(data.length > 0)
            {
                var tmpData = JSON.parse(data);
                mainData = tmpData[0];
                mainDataTotal = tmpData[1];
            }
            else
            {
                mainData = [];
                mainDataTotal = [];
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
                ["agent", locale['agent'],false,false] 
                ,["username", locale['username'],true,false]
                ,["total_wager",locale['total_wager'],true,true]
                ,["turnover",locale['turnover'],true,true]
                ,["win_loss",locale['win_loss'],true,true]
                ,["tier2_pt_amt",locale['sma_pt_amt'] ,true,true]
                ,["tier4_comm_amt",locale['ag_comm_amt'],true,true]
                ,["total_deposit",locale['ttl_deposit'] ,true,true]
                ,["total_withdraw",locale['ttl_withdraw'],true,true]
            ];

    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        var fieldUsername = utils.getDataTableFieldIdx("username",fields);
        var fieldTotalWager = utils.getDataTableFieldIdx("total_wager",fields);
        var fieldWinLoss = utils.getDataTableFieldIdx("win_loss",fields);
        var fieldTurnover = utils.getDataTableFieldIdx("turnover",fields);
        var fieldSmaPtAmt = utils.getDataTableFieldIdx("tier2_pt_amt",fields);
        var fieldsAgCommAmt = utils.getDataTableFieldIdx("tier4_comm_amt",fields);
        var fieldTtlDeposit = utils.getDataTableFieldIdx("total_deposit",fields);
        var fieldTtlWithdraw = utils.getDataTableFieldIdx("total_withdraw",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        { 
            var username = mainData.results[i - 1]["username"];
            var id = mainData.results[i - 1]["id"];
            var winLoss = mainData.results[i - 1]["win_loss"];
            var turnover = mainData.results[i - 1]["turnover"];
            var totalWager = mainData.results[i - 1]["total_wager"];
            var smaPtAmt = mainData.results[i - 1]["tier2_pt_amt"];
            var agCommAmt = mainData.results[i - 1]["tier4_comm_amt"];
            var ttlDeposit = mainData.results[i - 1]["total_deposit"];
            var ttlWithdraw = mainData.results[i - 1]["total_withdraw"];


            //username
            if(totalWager > 0)
            {
                var a = document.createElement("a");

                a.href = "/reports/winloss/products?member_id=" + id+"&username=" +username+"&s_date="+$("#s_date1").val()+"&e_date="+$("#e_date1").val(); 

                a.innerHTML = username;

                row.cells[fieldUsername].innerHTML =  "";
                row.cells[fieldUsername].appendChild(a);
            }

            if(winLoss < 0)
            {   
                row.cells[fieldWinLoss].style.color="red";
            }
            if(smaPtAmt < 0)
            {   
                row.cells[fieldSmaPtAmt].style.color="red";
            }
            if(agCommAmt < 0)
            {   
                row.cells[fieldsAgCommAmt].style.color="red";
            }

            if(ttlWithdraw < 0)
            {   
                row.cells[fieldTtlWithdraw].style.color="red";
            }

            row.cells[fieldTtlDeposit].innerHTML = utils.formatMoney(ttlDeposit);
            row.cells[fieldTtlWithdraw].innerHTML = utils.formatMoney(ttlWithdraw);        

            row.cells[fieldTurnover].innerHTML = utils.formatMoney(turnover);
            row.cells[fieldSmaPtAmt].innerHTML = utils.formatMoney(smaPtAmt);
            row.cells[fieldWinLoss].innerHTML = utils.formatMoney(winLoss);
            row.cells[fieldsAgCommAmt].innerHTML = utils.formatMoney(agCommAmt);
        }

        var sumFields = [      
            "total_wager"
            ,"win_loss"
            ,"turnover"
            ,"tier2_pt_amt"
            ,"tier4_comm_amt"
            ,"total_deposit"
            ,"total_withdraw"
        ]; 

        utils.createSumForDataTable(table,mainData,mainDataTotal,fields,sumFields);

        for (var j = 0, row; row = table.tFoot.rows[j]; j++) 
        {
            var totalTurnover = parseFloat(row.cells[fieldTurnover].innerHTML);
            var totalWinLoss = parseFloat(row.cells[fieldWinLoss].innerHTML);
            var totalWager = parseFloat(row.cells[fieldTotalWager].innerHTML);
            var totalSmaAmt = parseFloat(row.cells[fieldSmaPtAmt].innerHTML);
            var totalAgCommAmt = parseFloat(row.cells[fieldsAgCommAmt].innerHTML);
            var totalDeposit = parseFloat(row.cells[fieldTtlDeposit].innerHTML);
            var totalWithdraw = parseFloat(row.cells[fieldTtlWithdraw].innerHTML);

            table.tFoot.rows[j].style.backgroundColor = "#ffffe0";  

            if(totalWinLoss < 0)
            {   
                row.cells[fieldWinLoss].style.color="red";
            }
            if(totalSmaAmt < 0)
            {   
                row.cells[fieldSmaPtAmt].style.color="red";
            }
            if(totalAgCommAmt < 0)
            {   
                row.cells[fieldsAgCommAmt].style.color="red";
            }
            if(totalWithdraw < 0)
            {   
                row.cells[fieldTtlWithdraw].style.color="red";
            }

            row.cells[fieldTtlDeposit].innerHTML = "<b>" + utils.formatMoney(totalDeposit) + "</b>";;
            row.cells[fieldTtlWithdraw].innerHTML = "<b>" + utils.formatMoney(totalWithdraw) + "</b>";;  

            row.cells[fieldSmaPtAmt].innerHTML = "<b>" + utils.formatMoney(totalSmaAmt) + "</b>"; 
            row.cells[fieldTotalWager].innerHTML = "<b>" +totalWager + "</b>";
            row.cells[fieldTurnover].innerHTML = "<b>" + utils.formatMoney(totalTurnover) + "</b>";
            row.cells[fieldWinLoss].innerHTML = "<b>" + utils.formatMoney(totalWinLoss) + "</b>";         
            row.cells[fieldsAgCommAmt].innerHTML = "<b>" + utils.formatMoney(totalAgCommAmt) + "</b>";
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
    $("#username, #tier4").val("");
    $("#e_date, #e_date1").val("");
    $("#s_date, #s_date1").val("");

    filterMainData();
}

</script>

<style type="text/css">

    table, th, td 
    {
      border: 1px solid black;
      border-collapse: collapse;
    }

    th, td 
    {
      padding: 5px;
    }

    .fields
    {
      font-weight: bolder;
    }

    .border-less 
    {
        border-top: 1px solid #FFFFFF;
    }

</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.merchants.merchant.breadcrumb.agentmanagement') }}</li>
    <li class="breadcrumb-item">{{ __('app.sidebar.agentmanagement.member.details') }}</li>
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
                                <label for="name">{{ __('app.reports.winloss.filter.admin') }}</label>
                                <input type="text" class="form-control" id="tier4" autocomplete="">
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('app.reports.winloss.filter.username') }}</label>
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
