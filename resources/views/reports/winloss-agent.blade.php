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
    locale['mainData.username'] = "{!! __('app.reports.winloss.agent.maindata.username') !!}";
    locale['mainData.total_wager'] = "{!! __('app.reports.winloss.agent.maindata.wagercount') !!}";
    locale['mainData.total_winloss'] = "{!! __('app.reports.winloss.agent.maindata.winloss') !!}";
    locale['mainData.total_turnover'] = "{!! __('app.reports.winloss.agent.maindata.turnover') !!}";
    locale['mainData.valid_turnover'] = "{!! __('app.reports.winloss.agent.maindata.validturnover') !!}";
    locale['mainData.member_comm'] = "{!! __('app.reports.winloss.agent.maindata.membercomm') !!}";
    locale['mainData.member_winloss'] = "{!! __('app.reports.winloss.agent.maindata.memberwl') !!}";
    locale['mainData.agent_comm'] = "{!! __('app.reports.winloss.agent.maindata.agentcomm') !!}";
    locale['mainData.agent_winloss'] = "{!! __('app.reports.winloss.agent.maindata.agentwl') !!}";
    locale['mainData.com_winloss'] = "{!! __('app.reports.winloss.agent.maindata.comwl') !!}";
    locale['mainData.com_total'] = "{!! __('app.reports.winloss.agent.maindata.comtotal') !!}";
    locale['mainData.down_winloss'] = "{!! __('app.reports.winloss.agent.maindata.downwl') !!}";
    locale['mainData.down_comm'] = "{!! __('app.reports.winloss.agent.maindata.downcomm') !!}";
    locale['mainData.total_agent'] = "{!! __('app.reports.winloss.agent.maindata.totalagent') !!}";
    locale['mainData.total_down'] = "{!! __('app.reports.winloss.agent.maindata.totaldown') !!}";
    locale['mainData.number'] = "#";
    locale['mainData.winloss'] = "{!! __('app.reports.winloss.agent.maindata.wl') !!}";
    locale['mainData.commission'] = "{!! __('app.reports.winloss.agent.maindata.commission') !!}";
    locale['mainData.total'] = "{!! __('app.reports.winloss.agent.maindata.total') !!}";
    locale['mainData.member'] = "{!! __('app.reports.winloss.agent.maindata.member') !!}";
    locale['mainData.agentdownline'] = "{!! __('app.reports.winloss.agent.maindata.agentdownline') !!}";
    locale['mainData.company'] = "{!! __('app.reports.winloss.agent.maindata.company') !!}";
    locale['mainData.ag_winloss'] = "AG W/L";
    locale['mainData.ma_winloss'] = "MA W/L";
    locale['mainData.sma_winloss'] = "SMA W/L";
    locale['mainData.com_winloss'] = "{!! __('app.reports.winloss.agent.maindata.comwl') !!}";

}

var mainData;
var pSize="";

function getMainData()
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var data = utils.getDataTableDetails(containerId);

    if($("#s_date").val() == '')
    {
        document.getElementById('s_date1').value = "";
    }

    if($("#e_date").val() == '')
    {
        document.getElementById('e_date1').value = "";
    }
    
    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();

    data["id"] = utils.getParameterByName('id');
    
    $.ajax({
        type: "GET",
        url: "/ajax/reports/winloss/agent",
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
    var s_date = $("#s_date1").val();
    var e_date = $("#e_date1").val();

    $("#main-spinner").hide();
    $("#main-table").show();

    var fields = [
                    ["number",locale['mainData.number'],false,false]
                    ,["username",locale['mainData.username'],true,false]
                    ,["total_wager",locale['mainData.total_wager'],true,true]
                    ,["total_turnover",locale['mainData.total_turnover'],true,true]  
                    ,["member_winloss",locale['mainData.member_winloss'],false,true]
                    ,["ag_winloss",locale['mainData.ag_winloss'],false,true]
                    ,["ma_winloss",locale['mainData.ma_winloss'],false,true]
                    ,["sma_winloss",locale['mainData.sma_winloss'],false,true]
                    ,["com_winloss",locale['mainData.com_winloss'],false,true]

                ];

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {   
        $('#notes').show();
           
        var fieldAgent = utils.getDataTableFieldIdx("username",fields);
        var fieldTurnover = utils.getDataTableFieldIdx("total_turnover",fields);
        var fieldTotalWager = utils.getDataTableFieldIdx("total_wager",fields);
        var fieldMemberWinloss = utils.getDataTableFieldIdx("member_winloss",fields);
        var fieldAgWinloss = utils.getDataTableFieldIdx("ag_winloss",fields);
        var fieldMaWinloss = utils.getDataTableFieldIdx("ma_winloss",fields);
        var fieldSmaWinloss = utils.getDataTableFieldIdx("sma_winloss",fields);
        var fieldComWinloss = utils.getDataTableFieldIdx("com_winloss",fields);
        var fieldNumber = utils.getDataTableFieldIdx("number",fields);

        for (var i = 1, row; row = table.rows[i]; i++)
        {   
            var turnover = mainData.results[i - 1]["total_turnover"];
            var memberWinloss = mainData.results[i - 1]["member_winloss"];
            var agWinloss = mainData.results[i - 1]["ag_winloss"];
            var maWinloss = mainData.results[i - 1]["ma_winloss"];
            var smaWinloss = mainData.results[i - 1]["sma_winloss"];
            var comWinloss = mainData.results[i - 1]["com_winloss"];

            row.cells[fieldNumber].innerHTML = i;
            row.cells[fieldAgent].innerHTML = mainData.results[i - 1]["username"].toLowerCase();

            //disabled the redirect if no wager
            var a = document.createElement("a");
            a.href = "/reports/winloss?id="+ mainData.results[i - 1]["id"]+"&s_date="+s_date+"&e_date="+e_date;
            a.innerHTML = mainData.results[i - 1]["username"].toLowerCase();

            row.cells[fieldAgent].innerHTML =  "";
            row.cells[fieldAgent].appendChild(a);
          
            if(memberWinloss < 0)
            {   
                row.cells[fieldMemberWinloss].style.color="red";

            }

            if(agWinloss < 0)
            {   
                row.cells[fieldAgWinloss].style.color="red";

            }

            if(maWinloss < 0)
            {   
                row.cells[fieldMaWinloss].style.color="red";

            }

            if(smaWinloss < 0)
            {   
                row.cells[fieldSmaWinloss].style.color="red";

            }

            if(comWinloss < 0)
            {   
                row.cells[fieldComWinloss].style.color="red";

            }

            row.cells[fieldTurnover].innerHTML =  utils.formatMoney(turnover);
            row.cells[fieldMemberWinloss].innerHTML =  utils.formatMoney(memberWinloss);
            row.cells[fieldAgWinloss].innerHTML =  utils.formatMoney(agWinloss);
            row.cells[fieldMaWinloss].innerHTML =  utils.formatMoney(maWinloss);
            row.cells[fieldSmaWinloss].innerHTML =  utils.formatMoney(smaWinloss);
            row.cells[fieldComWinloss].innerHTML =  utils.formatMoney(comWinloss);
        }

        var sumFields = [      
                    "total_wager"
                    ,"total_turnover"
                    ,"member_winloss"
                    ,"ag_winloss"
                    ,"ma_winloss"
                    ,"sma_winloss"
                    ,"com_winloss"            
                ]; 

        utils.createSumForDataTable(table,mainData,mainDataTotal,fields,sumFields);

        for (var j = 0, row; row = table.tFoot.rows[j]; j++) 
        {
            var totalTurnover = parseFloat(row.cells[fieldTurnover].innerHTML);
            var totalWager = parseFloat(row.cells[fieldTotalWager].innerHTML);
            var memberWinloss = parseFloat(row.cells[fieldMemberWinloss].innerHTML);
            var agWinloss = parseFloat(row.cells[fieldAgWinloss].innerHTML);
            var maWinloss = parseFloat(row.cells[fieldMaWinloss].innerHTML);
            var smaWinloss = parseFloat(row.cells[fieldSmaWinloss].innerHTML);
            var comWinloss = parseFloat(row.cells[fieldComWinloss].innerHTML);

            if(memberWinloss < 0)
            {   
                row.cells[fieldMemberWinloss].style.color="red";

            }

            if(agWinloss < 0)
            {   
                row.cells[fieldAgWinloss].style.color="red";

            }

            if(maWinloss < 0)
            {   
                row.cells[fieldMaWinloss].style.color="red";

            }

            if(smaWinloss < 0)
            {   
                row.cells[fieldSmaWinloss].style.color="red";

            }

            if(comWinloss < 0)
            {   
                row.cells[fieldComWinloss].style.color="red";

            }

            row.cells[fieldTotalWager].innerHTML = "<b>" + totalWager + "</b>";

            row.cells[fieldTurnover].innerHTML = "<b>" + utils.formatMoney(totalTurnover) + "</b>";

            row.cells[fieldMemberWinloss].innerHTML = "<b>" + utils.formatMoney(memberWinloss) + "</b>";

            row.cells[fieldAgWinloss].innerHTML = "<b>" + utils.formatMoney(agWinloss) + "</b>";

            row.cells[fieldMaWinloss].innerHTML = "<b>" + utils.formatMoney(maWinloss) + "</b>";

           row.cells[fieldSmaWinloss].innerHTML = "<b>" + utils.formatMoney(smaWinloss) + "</b>";

            row.cells[fieldComWinloss].innerHTML = "<b>" + utils.formatMoney(comWinloss) + "</b>";

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
    $("#e_date, #e_date1").val("");
    $("#s_date, #s_date1").val("");

    filterMainData();
}


</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.reports.winloss.agent.breadcrumb.reports') }}</li>
    <li class="breadcrumb-item active">{{ __('app.reports.winloss.agent.breadcrumb.winlossdetails') }}</li>
    <li id="breadcrumb-own" class="breadcrumb-item">
    @if(Auth::user()->level == 0)
         <a href="/reports/winloss?id=&s_date={{$start_date}}&e_date={{$end_date}}">
            {{ Auth::user()->username }}
        </a>
    @endif   
    </li>
     @foreach($data as $d)
        @if( Auth::user()->admin_id != $d->admin_id)
        <li id="breadcrumb-tier-{{$d->level}}" class="breadcrumb-item">
            <a href="/reports/winloss?id={{$d->admin_id}}&s_date={{$start_date}}&e_date={{$end_date}}">
                {{ $d->username }} 
            </a>
        </li>
        @endif
        @if( Auth::user()->admin_id != $d->admin_id)
        <li id="breadcrumb-tier-{{$d->level}}" class="breadcrumb-item">
            <a href="/reports/winloss/agent?id={{$d->admin_id}}&s_date={{$start_date}}&e_date={{$end_date}}">
                {{ $d->username }} (Mix)
            </a>
        </li>

        @endif
    @endforeach
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="filterForm">

                <div class="card-header">
                    <strong>{{ __('app.reports.winloss.agent.breadcrumb.winlossdetails') }}</strong>
                </div>

                <div class="card-body">
                    
                    <div class="row">

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('common.filter.fromdate') }}</label>
                                <input type="text" class="form-control" name="s_date" id="s_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                <input type="hidden" name="s_date1" id="s_date1">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>{{ __('common.filter.todate') }}</label>
                                <input type="text" class="form-control" name="e_date" id="e_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                <input type="hidden" name="e_date1" id="e_date1">
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

            <!-- <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div> -->

         </div>
    </div>
</div>

@endsection
