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
    locale['prd_id'] = "{!! __('app.reports.winlossbyprod.maindata.prdid') !!}" ;
    locale['prd_name'] = "{!! __('app.reports.winlossbyprod.maindata.prdname') !!}" ;
    locale['win_loss'] = "{!! __('app.reports.winlossbyprod.maindata.winloss.amt') !!}";
    locale['total_wager'] = "{!! __('app.reports.winlossbyprod.maindata.totalwager') !!}";
    locale['turnover'] = "{!! __('app.reports.winlossbyprod.maindata.turnover') !!}";
    locale['company_pt_amt'] = "{!! __('app.reports.winlossbyprod.maindata.company.pt.amt') !!}";
    locale['sma_pt_amt'] = "{!! __('app.reports.winlossbyprod.maindata.sma.pt.amt') !!}";
    locale['ma_pt_amt'] = "{!! __('app.reports.winlossbyprod.maindata.ma.pt.amt') !!}";
    locale['ag_pt_amt'] = "{!! __('app.reports.winlossbyprod.maindata.ag.pt.amt') !!}";
    locale['ag_comm_amt'] = "{!! __('app.reports.winlossbyprod.maindata.ag.comm.amt') !!}";
}

var mainData;
var mainDataTotal = [];
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

    data["prd_id"] =  $("#prd_id").val();
    data["start_date"] = s_date;
    data["end_date"] = e_date;
  
    $.ajax({
        type: "GET",
        url: "/ajax/reports/winloss_by_product/list",
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

    var fields = [  
                ["prd_name", locale['prd_name'],false,false]
                ,["total_wager",locale['total_wager'],true,true]
                ,["turnover",locale['turnover'] ,true,true]
                ,["win_loss",locale['win_loss'],true,true]
                ,["tier1_pt_amt",locale['company_pt_amt'],true,true]
                ,["tier2_pt_amt",locale['sma_pt_amt'],true,true]
                ,["tier3_pt_amt",locale['ma_pt_amt'],true,true]
                ,["tier4_pt_amt",locale['ag_pt_amt'],true,true]
                ,["tier4_comm_amt",locale['ag_comm_amt'],true,true]
            ];

    if(auth.getUserLevel() == 0)
    {
        for(var i = fields.length-1 ; i > 0; i--)
        {
            if( fields[i][0] == "tier3_pt_amt" || fields[i][0] == "tier2_pt_amt" || fields[i][0] == "tier4_pt_amt"  )
            {
                fields.splice(i,1);
            }
        }            
    }
    else if(auth.getUserLevel() == 1)
    {
        for(var i = fields.length-1 ; i > 0; i--)
        {
            if( fields[i][0] == "tier1_pt_amt" || fields[i][0] == "tier3_pt_amt" || fields[i][0] == "tier4_pt_amt"  )
            {
                fields.splice(i,1);
            }
        }            
    }
    else if(auth.getUserLevel() == 2)
    {
        for(var i = fields.length-1 ; i > 0; i--)
        {
            if( fields[i][0] == "tier1_pt_amt" || fields[i][0] == "tier2_pt_amt" || fields[i][0] == "tier4_pt_amt"  )
            {
                fields.splice(i,1);
            }
        }            
    }
    else if(auth.getUserLevel() == 3)
    {
        for(var i = fields.length-1 ; i > 0; i--)
        {
            if( fields[i][0] == "tier1_pt_amt" || fields[i][0] == "tier2_pt_amt" || fields[i][0] == "tier3_pt_amt"  )
            {
                fields.splice(i,1);
            }
        }            
    }


    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);


    if(table != null)
    {
        var fieldPrdName = utils.getDataTableFieldIdx("prd_name",fields);
        var fieldTotalWager = utils.getDataTableFieldIdx("total_wager",fields);
        var fieldWinLoss = utils.getDataTableFieldIdx("win_loss",fields);
        var fieldTurnover = utils.getDataTableFieldIdx("turnover",fields);
        var fieldCaPtAmt = utils.getDataTableFieldIdx("tier1_pt_amt",fields);
        var fieldSmaPtAmt = utils.getDataTableFieldIdx("tier2_pt_amt",fields);
        var fieldMaPtAmt = utils.getDataTableFieldIdx("tier3_pt_amt",fields);
        var fieldAgPtAmt = utils.getDataTableFieldIdx("tier4_pt_amt",fields);
        var fieldAgCommAmt = utils.getDataTableFieldIdx("tier4_comm_amt",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        { 
            var prdId = mainData.results[i-1]["prd_id"];
            var prdName = mainData.results[i-1]["prd_name"];
            var gameId = mainData.results[i-1]["game_id"];
            var username = mainData.results[i-1]["username"];
            var winLoss = mainData.results[i - 1]["win_loss"];
            var turnover = mainData.results[i - 1]["turnover"];
            var caPtAmt = mainData.results[i - 1]["tier1_pt_amt"];
            var smaPtAmt = mainData.results[i - 1]["tier2_pt_amt"];
            var maPtAmt = mainData.results[i - 1]["tier3_pt_amt"];
            var agPtAmt = mainData.results[i - 1]["tier4_pt_amt"];
            var agCommAmt = mainData.results[i - 1]["tier4_comm_amt"];

            var a = document.createElement("a");

            a.href = "/reports/winloss_by_product/details?prd_id=" + prdId +"&prdName="+prdName+"&s_date="+$("#s_date1").val()+"&e_date="+$("#e_date1").val();    

            a.innerHTML = prdName;
            row.cells[fieldPrdName].innerHTML =  "";
            row.cells[fieldPrdName].appendChild(a);


            row.cells[fieldTurnover].innerHTML = utils.formatMoney(turnover);

            if(auth.getUserLevel() == 0)
            {
                if(caPtAmt < 0)
                    row.cells[fieldCaPtAmt].innerHTML = '<span style="color:red;">' + utils.formatMoney(caPtAmt) +'</span>';
                else
                    row.cells[fieldCaPtAmt].innerHTML = utils.formatMoney(caPtAmt);
            }
            else if(auth.getUserLevel() == 1)
            {
                if(smaPtAmt < 0)
                    row.cells[fieldSmaPtAmt].innerHTML = '<span style="color:red;">' + utils.formatMoney(smaPtAmt) +'</span>';
                else
                    row.cells[fieldSmaPtAmt].innerHTML = utils.formatMoney(smaPtAmt);

            }
            else if(auth.getUserLevel() == 2)
            {
                if(maPtAmt < 0)
                    row.cells[fieldMaPtAmt].innerHTML = '<span style="color:red;">' + utils.formatMoney(maPtAmt) +'</span>';
                else
                    row.cells[fieldMaPtAmt].innerHTML = utils.formatMoney(maPtAmt);  
            }
            else if(auth.getUserLevel() == 3)
            {
                if(agPtAmt < 0)
                    row.cells[fieldAgPtAmt].innerHTML = '<span style="color:red;">' + utils.formatMoney(agPtAmt) +'</span>';
                else
                    row.cells[fieldAgPtAmt].innerHTML = utils.formatMoney(agPtAmt);
            }


            if(winLoss < 0)
                row.cells[fieldWinLoss].innerHTML = '<span style="color:red;">' + utils.formatMoney(winLoss) +'</span>';
            else
               row.cells[fieldWinLoss].innerHTML = utils.formatMoney(winLoss);

            if(agCommAmt < 0)
                row.cells[fieldAgCommAmt].innerHTML = '<span style="color:red;">' + utils.formatMoney(agCommAmt) +'</span>';
            else
               row.cells[fieldAgCommAmt].innerHTML = utils.formatMoney(agCommAmt);
        }


        var sumFields = [      
            "total_wager"
            ,"win_loss"
            ,"turnover"
            ,"tier1_pt_amt"
            ,"tier2_pt_amt"
            ,"tier3_pt_amt"
            ,"tier4_pt_amt"
            ,"tier4_comm_amt"
      
        ]; 

        if(auth.getUserLevel() == 0)
        {
            for(var i = sumFields.length-1 ; i > 0; i--)
            {
                if( sumFields[i][0] == "tier3_pt_amt" || sumFields[i][0] == "tier2_pt_amt" || sumFields[i][0] == "tier4_pt_amt"  )
                {
                    sumFields.splice(i,1);
                }
            }            
        }
        else if(auth.getUserLevel() == 1)
        {
            for(var i = sumFields.length-1 ; i > 0; i--)
            {
                if( sumFields[i][0] == "tier1_pt_amt" || sumFields[i][0] == "tier3_pt_amt" || sumFields[i][0] == "tier4_pt_amt"  )
                {
                    sumFields.splice(i,1);
                }
            }            
        }
        else if(auth.getUserLevel() == 2)
        {
            for(var i = sumFields.length-1 ; i > 0; i--)
            {
                if( sumFields[i][0] == "tier1_pt_amt" || sumFields[i][0] == "tier2_pt_amt" || sumFields[i][0] == "tier4_pt_amt"  )
                {
                    sumFields.splice(i,1);
                }
            }            
        }
        else if(auth.getUserLevel() == 3)
        {
            for(var i = sumFields.length-1 ; i > 0; i--)
            {
                if( sumFields[i][0] == "tier1_pt_amt" || sumFields[i][0] == "tier2_pt_amt" || sumFields[i][0] == "tier3_pt_amt"  )
                {
                    sumFields.splice(i,1);
                }
            }            
        }


        utils.createSumForDataTable(table,mainData,mainDataTotal,fields,sumFields);

        for (var j = 0, row; row = table.tFoot.rows[j]; j++) 
        {
            var totalTurnover = parseFloat(row.cells[fieldTurnover].innerHTML);
            var totalWinLoss = parseFloat(row.cells[fieldWinLoss].innerHTML);
            var totalWager = parseFloat(row.cells[fieldTotalWager].innerHTML);
            var totalCaAmt = parseFloat(row.cells[fieldCaPtAmt].innerHTML);
            var totalSmaAmt = parseFloat(row.cells[fieldSmaPtAmt].innerHTML);
            var totalMaAmt = parseFloat(row.cells[fieldMaPtAmt].innerHTML);
            var totalAgAmt = parseFloat(row.cells[fieldAgPtAmt].innerHTML);
            var totalAgCommAmt = parseFloat(row.cells[fieldAgCommAmt].innerHTML);


            table.tFoot.rows[j].style.backgroundColor = "#ffffe0";  

            if(totalWinLoss < 0)
            {   
                row.cells[fieldWinLoss].style.color="red";
            } 
            if(totalCaAmt < 0)
            {   
                row.cells[fieldCaPtAmt].style.color="red";
            } 
            if(totalSmaAmt < 0)
            {   
                row.cells[fieldSmaPtAmt].style.color="red";
            } 
            if(totalMaAmt < 0)
            {   
                row.cells[fieldMaPtAmt].style.color="red";
            } 
            if(totalAgAmt < 0)
            {   
                row.cells[fieldAgPtAmt].style.color="red";
            } 
            if(totalAgCommAmt < 0)
            {   
                row.cells[fieldAgCommAmt].style.color="red";
            } 

            if(auth.getUserLevel() == 0)
            {
               row.cells[fieldCaPtAmt].innerHTML = "<b>" + utils.formatMoney(totalCaAmt) + "</b>"; 
            }
            else if(auth.getUserLevel() == 1)
            {
                row.cells[fieldSmaPtAmt].innerHTML = "<b>" + utils.formatMoney(totalSmaAmt) + "</b>";
            }
            else if(auth.getUserLevel() == 2)
            {
                row.cells[fieldMaPtAmt].innerHTML = "<b>" + utils.formatMoney(totalMaAmt) + "</b>";
            }
            else if(auth.getUserLevel() == 3)
            {
                row.cells[fieldAgPtAmt].innerHTML = "<b>" + utils.formatMoney(totalAgAmt) + "</b>";
            }
   
            row.cells[fieldTotalWager].innerHTML = "<b>" +totalWager + "</b>";
            row.cells[fieldTurnover].innerHTML = "<b>" + utils.formatMoney(totalTurnover) + "</b>";
            row.cells[fieldWinLoss].innerHTML = "<b>" + utils.formatMoney(totalWinLoss) + "</b>";            
            row.cells[fieldAgCommAmt].innerHTML = "<b>" + utils.formatMoney(totalAgCommAmt) + "</b>";

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
    <li class="breadcrumb-item">{{ __('app.reports.winlossbyprod.breadcrumb.reports') }}</li>
    <li class="breadcrumb-item active">{{ __('app.reports.winlossbyprod.breadcrumb.winlossbyprod.reports') }}</li>
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
                                <label for="provider">{{ __('app.reports.winlossbyprod.filter.prdname') }}</label>
                                <select class="form-control form-control-sm" name="prd_id" id="prd_id" style="height: 34.8px">
                                    <option value="" selected="">{{ __('app.reports.winlossbyprod.filter.all') }}</option>
                                    {{ Helper::generateOptions($optionProduct,'') }}
                                </select>
                            
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
