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

function createBreadcrumbs(id, username,tier,hrefId) 
{
    var s_date = $("#s_date1").val();
    var e_date = $("#e_date1").val();

    var a = document.createElement("a");
    a.innerHTML = username;

    if(id == "breadcrumb-own")
    {
        a.innerHTML = document.getElementById(id).innerHTML;
        document.getElementById(id).innerHTML = "";
    }

    if (tier != null) 
    {
        a.href = "/reports/winloss?tier="+tier+"&s_date="+s_date+"&e_date="+e_date;
        $(a).attr('id',hrefId);
    }
    else
    {
        a.href = "/reports/winloss?s_date="+s_date+"&e_date="+e_date;
        $(a).attr('id',"own");
    }
    
    document.getElementById(id).appendChild(a);
    
    $("#" + id).addClass("d-md-block");

    $("#" + id).clone().attr('id',id + '-m').appendTo('#breadcrumb-m');

    $("#" + id + '-m').removeClass("d-none");
}


function prepareLocale()
{   

    locale['username'] = "{!! __('app.reports.winloss.maindata.username') !!}";
    locale['win_loss'] = "{!! __('app.reports.winloss.maindata.winloss.amt') !!}"
    locale['total_wager'] = "{!! __('app.reports.winloss.maindata.totalwager') !!}";
    locale['turnover'] = "{!! __('app.reports.winloss.maindata.turnover') !!}";
    locale['company_pt_amt'] = "{!! __('app.reports.winloss.maindata.company.pt.amt') !!}";
    locale['sma_pt_amt'] = "{!! __('app.reports.winloss.maindata.sma.pt.amt') !!}";
    locale['ma_pt_amt'] = "{!! __('app.reports.winloss.maindata.ma.pt.amt') !!}";
    locale['ag_pt_amt'] = "{!! __('app.reports.winloss.maindata.ag.pt.amt') !!}";
    locale['ag_comm_amt'] = "{!! __('app.reports.winloss.maindata.ag.comm.amt') !!}";


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
    var s_date = $("#s_date1").val();
    var e_date = $("#e_date1").val();

/*    if($("#s_date").val() == '')
    {
        document.getElementById('s_date1').value = "";
    }

     if($("#e_date").val() == '')
    {
        document.getElementById('e_date1').value = "";
    }*/

    
    data["txn_id"] = $("#txn_id").val();
    data["username"] = $("#username").val();
    data["prd_id"] = $("#prd_id").val();
    data["start_date"] = s_date;
    data["end_date"] = e_date;
    data["agent_id"] = $("#agent_id").val();


    $.ajax({
        type: "GET",
        url: "/ajax/reports/winloss/list",
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

    console.log(mainData);

    var fields = [
                ["agent",'Agent',true,false]   
                ,["username", locale['username'],true,false]
                ,["total_wager",locale['total_wager'],true,true]
                ,["total_turnover",locale['turnover'],true,true]
                ,["member_winloss",locale['win_loss'],true,true]

            ];

 
    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        var fieldUsername = utils.getDataTableFieldIdx("username",fields);
        var fieldTotalWager = utils.getDataTableFieldIdx("total_wager",fields);
        var fieldWinLoss = utils.getDataTableFieldIdx("member_winloss",fields);
        var fieldTurnover = utils.getDataTableFieldIdx("total_turnover",fields);


        for (var i = 1, row; row = table.rows[i]; i++) 
        { 
            var username = mainData.results[i - 1]["username"];
            var id = mainData.results[i - 1]["id"];
            var winLoss = mainData.results[i - 1]["member_winloss"];
            var turnover = mainData.results[i - 1]["total_turnover"];

            //Turnover
            row.cells[fieldTurnover].innerHTML = utils.formatMoney(turnover);

            //username
            var a = document.createElement("a");

            a.href = "/reports/winloss/products?member_id=" + id+"&username=" +username+"&s_date="+$("#s_date1").val()+"&e_date="+$("#e_date1").val();  


            console.log(winLoss);
            if(winLoss < 0)
                row.cells[fieldWinLoss].innerHTML = '<span style="color:red;">' + utils.formatMoney(winLoss) +'</span>';
            else
               row.cells[fieldWinLoss].innerHTML = utils.formatMoney(winLoss);


               

            a.innerHTML = username;

            row.cells[fieldUsername].innerHTML =  "";
            row.cells[fieldUsername].appendChild(a);
        }

        var sumFields = [      
            "total_wager"
            ,"member_winloss"
            ,"total_turnover"
      
        ]; 


       

        utils.createSumForDataTable(table,mainData,mainDataTotal,fields,sumFields);

        for (var j = 0, row; row = table.tFoot.rows[j]; j++) 
        {
            var totalTurnover = parseFloat(row.cells[fieldTurnover].innerHTML);
            var totalWinLoss = parseFloat(row.cells[fieldWinLoss].innerHTML);
            var totalWager = parseFloat(row.cells[fieldTotalWager].innerHTML);


            table.tFoot.rows[j].style.backgroundColor = "#ffffe0";  

            if(totalWinLoss < 0)
            {   
                row.cells[fieldWinLoss].style.color="red";
            } 
        
                     
            row.cells[fieldTotalWager].innerHTML = "<b>" +totalWager + "</b>";
            row.cells[fieldTurnover].innerHTML = "<b>" + utils.formatMoney(totalTurnover) + "</b>";
            row.cells[fieldWinLoss].innerHTML = "<b>" + utils.formatMoney(totalWinLoss) + "</b>";            


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
    $("#txn_id").val("");
    $("#username").val("");
    $("#e_date, #e_date1").val("");
    $("#s_date, #s_date1").val("");
    $("#agent_id").val("");
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
    <li class="breadcrumb-item">{{ __('app.reports.winloss.breadcrumb.reports') }}</li>
    <li class="breadcrumb-item active">{{ __('app.reports.winloss.breadcrumb.winloss.reports') }}</li>
    <li id="breadcrumb-own" class="breadcrumb-item d-none">{{ Auth::user()->username }}</li>
    <li id="breadcrumb-uptier3" class="breadcrumb-item d-none"></li>
    <li id="breadcrumb-uptier2" class="breadcrumb-item d-none"></li>
    <li id="breadcrumb-uptier1" class="breadcrumb-item d-none"></li>

    
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
                                <label for="name">{{ __('app.reports.winloss.filter.username') }}</label>
                                <input type="text" class="form-control" id="username" autocomplete="">
                            </div>
                        </div>

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
