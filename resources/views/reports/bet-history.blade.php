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

    $("#result_all").click(function()
    {
        document.getElementById("result_win").checked = false;
        document.getElementById("result_lose").checked = false;
        document.getElementById("result_tie").checked = false;
        document.getElementById("result_refund").checked = false;
        document.getElementById("result_pending").checked = false;

        if(!$('#result_win').prop('checked') && !$('#result_lose').prop('checked') 
         && !$('#result_refund').prop('checked') && !$('#result_pending').prop('checked') && !$('#result_tie').prop('checked'))
        {
            document.getElementById("result_all").checked = true;
        }

    });


     $("#result_win,#result_lose,#result_refund,#result_pending,#result_tie").click(function()
    {
        document.getElementById("result_all").checked = false;

        if(!$('#result_win').prop('checked') && !$('#result_lose').prop('checked') 
         && !$('#result_refund').prop('checked') && !$('#result_pending').prop('checked') && !$('#result_tie').prop('checked'))
        {
            document.getElementById("result_all").checked = true;
        }

    });
});

function prepareLocale()
{   
    locale['username'] = "{!! __('app.reports.txn.maindata.username') !!}";
    locale['txn_id'] = "{!! __('app.reports.txn.maindata.txn_id') !!}";
    locale['debit'] = "{!! __('app.reports.txn.maindata.debit') !!}";
    locale['credit'] = "{!! __('app.reports.txn.maindata.credit') !!}";
    locale['status'] = "{!! __('app.reports.txn.maindata.status') !!}";
    locale['game_id'] = "{!! __('app.reports.txn.maindata.gameid') !!}";
    locale['timestamp'] = "{!! __('app.reports.txn.maindata.timestamp') !!}";
    locale['admin'] = "Admin";

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
    var result = [];

    if($('#result_all').prop('checked'))
    {
        result.push('w','l','r','p','t');

    }
    else
    {
        if($('#result_win').prop('checked'))
        {
            result.push('w');
        }
        if($('#result_lose').prop('checked'))
        {
            result.push('l');
        }
        if($('#result_refund').prop('checked'))
        {
            result.push('r');
        }
        if($('#result_pending').prop('checked'))
        {
            result.push('p');
        }
        if($('#result_tie').prop('checked'))
        {
            result.push('t');
        }
    }

    data["txn_id"] = $("#txn_id").val();
    data["member_name"] = $("#member_name").val();
    data["prd_id"] = $("#prd_id").val();

    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();
    data['result'] = result;

    $.ajax({
        type: "GET",
        url: "/ajax/reports/bet_history/list",
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

    console.log(mainData);

    var fields = [  
                ["txn_id", locale['txn_id'],true,false]
                ,["username",locale['username'],true,false] 
                ,["admin_id",locale['admin'],true,false] 
                ,["debit",locale['debit'],true,true]
                ,["credit",locale['credit'],true,true]
                ,["bet_result",locale['status'],true,false]
                ,["game_id",locale['game_id'],true,false]
                ,["timestamp",locale['timestamp'],true,false]
            ];

    console.log(mainData);

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();

        var fieldTxnId  = utils.getDataTableFieldIdx("txn_id", fields);
        var fieldDebit = utils.getDataTableFieldIdx("debit", fields);
        var fieldCredit = utils.getDataTableFieldIdx("credit", fields);
        var fieldBetResult = utils.getDataTableFieldIdx("bet_result", fields);
        var fieldUsername = utils.getDataTableFieldIdx("username", fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {
            var txnId = mainData.results[i-1].txn_id;
            var betResult = mainData.results[i-1].bet_result;
            var betResultDesc = mainData.results[i-1].bet_result_desc;
            var status = mainData.results[i-1].status;
            var statusDesc = mainData.results[i-1].status_desc;
            var debit = mainData.results[i-1].debit;
            var credit = mainData.results[i-1].credit;
            var username = mainData.results[i-1].username;
            var id = mainData.results[i-1].member_id;

            row.cells[fieldUsername].innerHTML = username;

            b = document.createElement("a");
            b.href = "#/";
            $(b).attr("onclick","showDetailsModal("+i+")");
            b.innerHTML = txnId;
            row.cells[fieldTxnId].innerHTML = "";
            row.cells[fieldTxnId].appendChild(b);

  
            if(debit != null)
                row.cells[fieldDebit].innerHTML = utils.formatMoney(debit,2);
            else
                 row.cells[fieldDebit].innerHTML = '=';

            if(credit != null)
                row.cells[fieldCredit].innerHTML = utils.formatMoney(credit,2);
            else
              row.cells[fieldCredit].innerHTML = "-";  

            if(betResult == 'w')
                row.cells[fieldBetResult].innerHTML = '<span class="badge badge-success">'+betResultDesc+'</span>';
            else if(betResult == 'l')
                row.cells[fieldBetResult].innerHTML = '<span class="badge badge-danger">'+betResultDesc+'</span>';
            else if(betResult == 'r')
                row.cells[fieldBetResult].innerHTML = '<span class="badge badge-dark">'+betResultDesc+'</span>';
            else if(betResult == 'p')
                row.cells[fieldBetResult].innerHTML = '<span class="badge badge-warning">'+betResultDesc+'</span>';
            else if(betResult == 't')
                row.cells[fieldBetResult].innerHTML = '<span class="badge badge-primary">'+betResultDesc+'</span>';
        }
    }
}


var clicked = true;

function showDetailsModal(rowId)
{
    if(clicked)
    {
        // clicked = false;
        // var username = mainData.results[rowId - 1]["username"];
        // var txnId = mainData.results[rowId - 1]["txn_id"];
        // var roundId = mainData.results[rowId - 1]["round_id"];
        // var memberId = mainData.results[rowId - 1]["member_id"];
        // var prdId = mainData.results[rowId - 1]["prd_id"];
        // var creditDate = mainData.results[rowId - 1]["credit_date"];
        // var configId = mainData.results[rowId - 1]["config_id"];
        // var gameId = mainData.results[rowId - 1]["game_id"];

        // var data = {
        //             txn_id : txnId
        //             ,member_id: memberId
        //             ,prd_id: prdId
        //             ,round_id: roundId
        //             ,config_id: configId
        //             ,game_id: gameId
        //             ,credit_date: creditDate
        //             };

        // $.ajax({
        //     type: "GET",
        //     url: "/ajax/reports/winloss/products/get_results",
        //     data: data,
        //     success: function(data) 
        //     {
              
        //         $('#modalDetails .card').html("");
        //         var iframe = document.createElement('iframe');
        //         iframe.setAttribute('src',data);
        //         iframe.style.height = "480px";
        //         $('#modal-json').hide();
    
        //         $('#modalDetails .card').html(iframe);
        //         $('#modalDetails .modal-content').css('width', '850px');
        //         $("#modalDetails").modal('show');

        //     },
        //     complete: function()
        //     {
        //         clicked = true;
        //     }
        // });

            $('#modalDetails .card').html('Under Maintenance');
            $('#modalDetails .modal-content').css('width', '850px');
            $("#modalDetails").modal('show');
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
    $("#txn_id").val("");
    $("#member_name").val("");
    $("#e_date, #e_date1").val("");
    $("#s_date, #s_date1").val("");

    document.getElementById("result_win").checked = false;
    document.getElementById("result_lose").checked = false;
    document.getElementById("result_refund").checked = false;
    document.getElementById("result_pending").checked = false;
    document.getElementById("result_tie").checked = false;
    document.getElementById("result_all").checked = true;

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
    <li class="breadcrumb-item">{{ __('app.reports.txn.breadcrumb.reports') }}</li>
    <li class="breadcrumb-item active">{{ __('app.reports.txn.breadcrumb.txnhistory') }}</li>
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
                                <label for="provider">{{ __('app.reports.txn.filter.prdname') }}</label>
                                <select class="form-control form-control-sm" name="prd_id" id="prd_id" style="height: 34.8px">
                                        {{ Helper::generateOptions($optionProduct,'') }}
                                </select>
                            
                            </div>
                        </div>
                        <div class="col-sm-2">

                            <div class="form-group">
                                <label for="txn_id">{{ __('app.reports.txn.filter.txn_id') }}</label>
                                <input type="text" class="form-control" id="txn_id" autocomplete="">
                            </div>

                        </div>

                        <div class="col-sm-2">

                            <div class="form-group">
                                <label for="name">{{ __('app.reports.txn.filter.username') }}</label>
                                <input type="text" class="form-control" id="member_name" autocomplete="">
                            </div>

                        </div>

                    </div>
                    

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
                    </div>

                    <div class="row">

                        <div class="col-sm-8" style="padding-top: 10px">
                            <div class="form-group">
                                <label for="name">{{ __('app.reports.txn.filter.betresult') }}</label>

                                <input type="checkbox" id="result_all" class="ml-2" checked="">
                                <label for="result_all" class="ml-2">{{ __('app.reports.txn.filter.option.all') }}</label>

                                <input type="checkbox" id="result_win" class="ml-2">
                                <label for="result_win" class="ml-2">{{ __('app.reports.txn.filter.option.win') }}</label>

                                <input type="checkbox" id="result_lose" class="ml-2">
                                <label for="result_lose" class="ml-2">{{ __('app.reports.txn.filter.option.lose') }}</label>

                                <input type="checkbox" id="result_tie" class="ml-2">
                                <label for="result_tie" class="ml-2">{{ __('app.reports.txn.filter.option.tie') }}</label>

                                <input type="checkbox" id="result_refund" class="ml-2">
                                <label for="result_refund" class="ml-2">{{ __('app.reports.txn.filter.option.refund') }}</label>

                                <input type="checkbox" id="result_pending" class="ml-2">
                                <label for="result_pending" class="ml-2">{{ __('app.reports.txn.filter.option.pending') }}</label>
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

<div id="modalDetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.reports.txn.details') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>            
            </div>
            <div class="modal-body">
                <div class="card" id="modal-table"></div>
            </div>        
        </div>
    </div>
</div>



@endsection
