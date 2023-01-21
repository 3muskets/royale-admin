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
    locale['txn_id'] = "{!! __('app.reports.credit.member.txn_id') !!}";
    locale['type'] = "{!! __('app.reports.credit.member.type') !!}";
    locale['operator'] = "{!! __('app.reports.credit.member.operator') !!}";
    locale['credit_before'] = "{!! __('app.reports.credit.member.credit_before') !!}";
    locale['credit_after'] = "{!! __('app.reports.credit.member.credit_after') !!}";
    locale['created_at'] = "{!! __('app.reports.credit.member.createdat') !!}";
    locale['remark'] = "{!! __('app.reports.credit.member.remark') !!}";
    locale['member'] = "{!! __('app.reports.credit.member.username') !!}";
    locale['agent'] = "{!! __('app.reports.credit.agent.username') !!}";
    locale['transfer_in'] = "{!! __('app.reports.credit.member.transfer_in') !!}";
    locale['transfer_out'] = "{!! __('app.reports.credit.member.transfer_out') !!}";
    locale['payment_type'] = "{!! __('app.reports.credit.member.payment_type') !!}";
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
    data["agent_id"] = $("#agent_id").val();

    $.ajax({
        type: "GET",
        url: "/ajax/reports/member_credit/list",
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
                ["txn_id", locale['txn_id'],true,false]
                ,["agent",'Agent',false,false] 
                ,["member",locale['member'],false,false]   
                ,["txn_type_desc",'Transaction Type',false,false]        
                ,["type_details",locale['type'],false,false]
                ,["transfer_detail",'Transfer Detail',false,false]
                ,["credit_before",locale['credit_before'],false,true]     
                ,["transfer_in",locale['transfer_in'],false,true]
                ,["transfer_out",locale['transfer_out'],false,true]
                ,["credit_after",locale['credit_after'],false,true] 
                ,["payment_type_text",locale['payment_type'],false,false] 
                ,["created_at",locale['created_at'],true,false] 
                ,["remark",locale['remark'],false,false]  
                ,["operator",locale['operator'],false,false]         
            ];


    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();

        var fieldCreditBefore = utils.getDataTableFieldIdx("credit_before", fields);
        var fieldCreditAfter = utils.getDataTableFieldIdx("credit_after", fields);
        var fieldTransferIn = utils.getDataTableFieldIdx("transfer_in", fields);
        var fieldTransferOut = utils.getDataTableFieldIdx("transfer_out", fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {
            row.cells[fieldCreditBefore].innerHTML = utils.formatMoney(mainData.results[i - 1]["credit_before"]);
            row.cells[fieldCreditAfter].innerHTML = utils.formatMoney(mainData.results[i - 1]["credit_after"]);
            row.cells[fieldTransferIn].innerHTML = utils.formatMoney(mainData.results[i - 1]["transfer_in"]);
            row.cells[fieldTransferOut].innerHTML = utils.formatMoney(mainData.results[i - 1]["transfer_out"]);

            if(mainData.results[i - 1]["transfer_out"] < 0)
               row.cells[fieldTransferOut].style.color = "red";

            if(mainData.results[i - 1]["credit_after"] < 0)
                row.cells[fieldCreditAfter].style.color = "red";

            if(mainData.results[i - 1]["transfer_in"] == 0)
                    row.cells[fieldTransferIn].innerHTML = '-';

            if(mainData.results[i - 1]["transfer_out"] == 0)
                row.cells[fieldTransferOut].innerHTML = '-';
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
    $("#agent_id").val("");

    filterMainData();
}

</script>

<style type="text/css">
    
</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.reports.credit.member.breadcrumb.report') }}</li>
    <li class="breadcrumb-item active">{{ __('app.reports.credit.member.breadcrumb.membercredit') }}</li>
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
                        @if (Auth::user()->admin_id == 1)
                        <div class="col-sm-2">
                            <label>Agent</label>
                            <select id="agent_id" name="agent_id" class="form-control">
                                <option value="">All</option>
                                <option value="2">Agent 1</option>
                                <option value="3">Agent 2</option>
                            
                            </select>
                        </div>
                        @endif

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
