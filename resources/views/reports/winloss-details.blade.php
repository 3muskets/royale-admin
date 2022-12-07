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
    
    var tier = utils.getParameterByName('tier');
    var memberId = utils.getParameterByName('member_id');
    var username = utils.getParameterByName('username');
    var prdId = utils.getParameterByName('product');

/*    if(prdId == 1)
        prdName = 'Sxg';
    if(prdId == 2)
        prdName = 'Haba';
    if(prdId == 3)
        prdName = 'Prag';
    if(prdId == 4)
        prdName = 'Wm';*/

    createBreadcrumbs("breadcrumb-own");

    createBreadcrumbs("breadcrumb-mb",username,memberId); 
/*    createBreadcrumbs("breadcrumb-prd",prdName,memberId); */

    if (memberId != null) 
    {
        if('{{$upTier1}}'!= '')
        {
            createBreadcrumbs("breadcrumb-uptier1",'{{$upTierUsername1}}','{{$upTier1}}','upTier1'); 
        }

        if('{{$upTier2}}'!= '')
        {
            createBreadcrumbs("breadcrumb-uptier2",'{{$upTierUsername2}}','{{$upTier2}}','upTier2'); 
        }
        if('{{$upTier3}}'!= '')
        {
            createBreadcrumbs("breadcrumb-uptier3",'{{$upTierUsername3}}','{{$upTier3}}','upTier3'); 
        }

        
    }

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
    var prdId = utils.getParameterByName('product');
    a.innerHTML = username;

    if(id == "breadcrumb-own")
    {
        a.innerHTML = document.getElementById(id).innerHTML;
        document.getElementById(id).innerHTML = "";
    }

    if(id == "breadcrumb-prd")
    {
        //in case breadcrumb-prd username is product name,so for username parameter we use this utils.getParameterByName('username')
        a.href = "/reports/winloss/details?product="+prdId+"&member_id="+tier+"&username="+ utils.getParameterByName('username')+"&s_date="+s_date+"&e_date="+e_date;
        $(a).attr('id','prd');
    }
    else if(id == "breadcrumb-mb")
    {
        a.href = "/reports/winloss/products?member_id="+tier+"&username="+username+"&s_date="+s_date+"&e_date="+e_date;
        $(a).attr('id','mb');
    }
    else if ((id == "breadcrumb-uptier1" || id == "breadcrumb-uptier2" || id == "breadcrumb-uptier3" ) && auth.getUserLevel() != 3)
    {
        a.href = "/reports/winloss?tier="+tier+"&s_date="+s_date+"&e_date="+e_date;
        $(a).attr('id',hrefId);
    }
    else
    {
        a.href = "/reports/winloss?s_date="+s_date+"&e_date="+e_date;
        $(a).attr('id','own');
    }

    
    document.getElementById(id).appendChild(a);
    
    $("#" + id).addClass("d-md-block");

    $("#" + id).clone().attr('id',id + '-m').appendTo('#breadcrumb-m');

    $("#" + id + '-m').removeClass("d-none");
}


function prepareLocale()
{ 
    locale['stake'] = "{!! __('app.reports.winloss.details.maindata.stake') !!}"; 
    locale['status'] = "{!! __('app.reports.winloss.details.maindata.status') !!}";
    locale['mb_win_loss'] = "{!! __('app.reports.winloss.details.maindata.member.winloss') !!}";
    locale['company_pt_amt'] = "{!! __('app.reports.winloss.details.maindata.company.pt.amt') !!}";
    locale['sma_pt_amt'] = "{!! __('app.reports.winloss.details.maindata.sma.pt.amt') !!}";
    locale['ma_pt_amt'] = "{!! __('app.reports.winloss.details.maindata.ma.pt.amt') !!}";
    locale['ag_pt_amt'] = "{!! __('app.reports.winloss.details.maindata.ag.pt.amt') !!}";
    locale['company_pt'] = "{!! __('app.reports.winloss.details.maindata.company.pt') !!}";
    locale['sma_pt'] = "{!! __('app.reports.winloss.details.maindata.sma.pt') !!}";
    locale['ma_pt'] = "{!! __('app.reports.winloss.details.maindata.ma.pt') !!}";
    locale['ag_pt'] = "{!! __('app.reports.winloss.details.maindata.ag.pt') !!}";
    locale['ag_comm'] = "{!! __('app.reports.winloss.details.maindata.ag.comm') !!}";
    locale['ag_comm_amt'] = "{!! __('app.reports.winloss.details.maindata.ag.comm.amt') !!}";
    locale['inform'] = "{!! __('app.reports.winloss.details.maindata.info') !!}"; ;
    locale['selection_game'] = "{!! __('app.reports.winloss.details.maindata.selection.game') !!}"; ;

    if(auth.getUserLevel() == 0)
    { 
        /*locale['mainData.pt'] = locale['company_pt']  + "<br/>" + locale['sma_pt'] + "<br/>" + locale['ma_pt'] + "<br/>" + locale['ag_pt'];*/

        locale['mainData.pt'] = locale['company_pt'];
    }

    if(auth.getUserLevel() == 1)
    {
        /*locale['mainData.pt'] = locale['sma_pt'] + "<br/>" + locale['ma_pt'] + "<br/>" + locale['ag_pt'];*/
        locale['mainData.pt'] = locale['sma_pt'];
    }

    if(auth.getUserLevel() == 2)
    { 
        /*locale['mainData.pt'] = locale['ma_pt'] + "<br/>" + locale['ag_pt'] + "<br/>" ;*/
        locale['mainData.pt'] = locale['ma_pt'];
    }

    if(auth.getUserLevel() == 3)
    { 
        locale['mainData.pt'] = locale['ag_pt'];
    }
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

    var ownUrl = document.getElementById('own');
    var upTier1Url = document.getElementById('upTier1');
    var upTier2Url = document.getElementById('upTier2');
    var upTier3Url = document.getElementById('upTier3');
    var mbUrl = document.getElementById('mb');
    var prdUrl = document.getElementById('prd');

    var memberId = utils.getParameterByName('member_id');
    var username = utils.getParameterByName('username');
    var prdId = utils.getParameterByName('product');

/*    if(prdId == 1)
        prdName = 'Sxg';
    if(prdId == 2)
        prdName = 'Haba';
    if(prdId == 3)
        prdName = 'Prag';
    if(prdId == 4)
        prdName = 'Wm';*/

    ownUrl.href  = "/reports/winloss?s_date="+s_date+"&e_date="+e_date;
    
    mbUrl.href = "/reports/winloss/products?member_id="+memberId+"&username="+username+"&s_date="+s_date+"&e_date="+e_date;

/*    prdUrl.href = "/reports/winloss/details?product="+prdId+"&member_id="+memberId+"&username="+ username+"&s_date="+s_date+"&e_date="+e_date;*/
    
    if('{{$upTier1}}'!= '')
    {
        upTier1Url.href = "/reports/winloss?tier="+"{{$upTier1}}"+"&s_date="+s_date+"&e_date="+e_date;
    }
    if('{{$upTier2}}'!= '')
    {
        upTier2Url.href = "/reports/winloss?tier="+"{{$upTier2}}"+"&s_date="+s_date+"&e_date="+e_date;
    }
    if('{{$upTier3}}'!= '')
    {
       upTier3Url.href = "/reports/winloss?tier="+"{{$upTier3}}"+"&s_date="+s_date+"&e_date="+e_date; 
    }


    data["id"] = utils.getParameterByName('member_id');
    data["prd_id"] = utils.getParameterByName('product');
    data["start_date"] = s_date;
    data["end_date"] = e_date;
    data["tier"] = utils.getParameterByName('tier');

    $.ajax({
        type: "GET",
        url: "/ajax/reports/winloss/details",
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
                ["debit_date", locale['inform'],true,false]
/*                ,["credit_date", locale['selection_game'],true,false]*/
                ,["stake",locale['stake'],true,true]
                ,["bet_status",locale['status'],true,true]
                ,["win_loss",locale['mb_win_loss'] ,true,true]

                
            ];



    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        var fieldUsername = utils.getDataTableFieldIdx("debit_date",fields);
/*        var fieldSelection = utils.getDataTableFieldIdx("credit_date",fields);*/
        var fieldStake = utils.getDataTableFieldIdx("stake",fields);
        var fieldStatus = utils.getDataTableFieldIdx("bet_status",fields);
        var fieldWinLoss = utils.getDataTableFieldIdx("win_loss",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        { 
            var username = mainData.results[i - 1]["username"];
            var txnId = mainData.results[i - 1]["txn_id"];
            var debitDate = mainData.results[i - 1]["debit_date"];
            var creditDate = mainData.results[i - 1]["credit_date"];
            var gameName = mainData.results[i - 1]["game_name"];
            var stake = utils.formatMoney(mainData.results[i - 1]["stake"]);
            var status = mainData.results[i - 1]["bet_status"];
            var statusDesc = mainData.results[i - 1]["bet_status_desc"];
            var winLoss = mainData.results[i - 1]["win_loss"];
            var companyPtAmt = mainData.results[i - 1]["tier1_pt_amt"];
            var smaPtAmt = mainData.results[i - 1]["tier2_pt_amt"];
            var maPtAmt = mainData.results[i - 1]["tier3_pt_amt"];
            var agPtAmt = mainData.results[i - 1]["tier4_pt_amt"];
            var companyPt = utils.formatMoney(mainData.results[i - 1]["tier1_pt"],1);          
            var smaPt = utils.formatMoney(mainData.results[i - 1]["tier2_pt"],1);
            var maPt = utils.formatMoney(mainData.results[i - 1]["tier3_pt"],1);
            var agPt = utils.formatMoney(mainData.results[i - 1]["tier4_pt"],1);
            var agComm = utils.formatMoney(mainData.results[i - 1]["tier4_comm"],1);
            var agCommAmt = mainData.results[i - 1]["tier4_comm_amt"];

            row.cells[fieldUsername].innerHTML = '<b>' + username + '</b>'+ '<br/><a id="details-link" href="#/" onclick="showDetailsModal(' + i + ');">' + txnId +  '</a><br/>'+debitDate ;


/*            row.cells[fieldSelection].innerHTML = '<b>' + gameName + '</b>'+"<br>"+creditDate ;*/
            row.cells[fieldStake].innerHTML = stake ;

            if(status == 'w')
            {
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+statusDesc+'</span>';
            }
            if(status == 'l')
            {
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-danger">'+statusDesc+'</span>';
            }
            if(status == 't')
            {
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-primary">'+statusDesc+'</span>';
            }

            //tier Pt AMT

            if(winLoss < 0)
            {
                row.cells[fieldWinLoss].innerHTML = '<span style="color:red;">' + utils.formatMoney(winLoss) +'</span>';
            }
            else
            {
                row.cells[fieldWinLoss].innerHTML = utils.formatMoney(winLoss);
            }
            
           
        }

        var sumFields = [      
            "stake"
            ,"win_loss"

      
        ]; 


        utils.createSumForDataTable(table,mainData,mainDataTotal,fields,sumFields);

        for (var j = 0, row; row = table.tFoot.rows[j]; j++) 
        {
            var totalStake = parseFloat(row.cells[fieldStake].innerHTML);
            var totalWinLoss = parseFloat(row.cells[fieldWinLoss].innerHTML);



            table.tFoot.rows[j].style.backgroundColor = "#ffffe0";  

            if(totalWinLoss < 0)
            {   
                row.cells[fieldWinLoss].style.color="red";
            } 
        

            row.cells[fieldStake].innerHTML = "<b>" + utils.formatMoney(totalStake) + "</b>";
            row.cells[fieldWinLoss].innerHTML = "<b>" + utils.formatMoney(totalWinLoss) + "</b>";


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
        // var gameId = mainData.results[rowId - 1]["game_id"];
        // var configId = mainData.results[rowId - 1]["config_id"];
        // var prdId = utils.getParameterByName("product");
        // var creditDate = mainData.results[rowId - 1]["credit_date"];

        // var data = {
        //             txn_id : txnId
        //             ,member_id: memberId
        //             ,prd_id: prdId
        //             ,round_id: roundId
        //             ,credit_date: creditDate
        //             ,game_id: gameId
        //             ,config_id: configId
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
    <li class="breadcrumb-item">{{ __('app.reports.winloss.details.breadcrumb.reports') }}</li>
    <li class="breadcrumb-item active">{{ __('app.reports.winloss.details.breadcrumb.winloss.reports') }}</li>
    <li id="breadcrumb-own" class="breadcrumb-item d-none">{{ Auth::user()->username }}</li>
    <li id="breadcrumb-uptier3" class="breadcrumb-item d-none"></li>
    <li id="breadcrumb-uptier2" class="breadcrumb-item d-none"></li>
    <li id="breadcrumb-uptier1" class="breadcrumb-item d-none"></li>
    <li id="breadcrumb-mb" class="breadcrumb-item d-none"></li>
    <li id="breadcrumb-prd" class="breadcrumb-item d-none"></li>
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
