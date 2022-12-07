@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function()
{
    prepareLocale();

    var date = utils.getToday();
    var c_date = utils.getParameterByName("c_date");
    var s_date = utils.getParameterByName("s_date");
    var e_date = utils.getParameterByName("e_date");

    $("#change_date").val(c_date);
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

    if (c_date == null)
    {
        $("#change_date").val(2).change();
    }

    utils.datepickerStart('s_date','e_date','s_date1','');

    utils.datepickerEnd('s_date','e_date','e_date1','');

    utils.createSpinner("main-spinner");

    createBreadcrumbs("breadcrumb-own");

    @foreach($data as $d)
        createBreadcrumbs("breadcrumb-tier-{{$d->level}}", "{{$d->initial_username}}", "{{$d->id}}");
    @endforeach

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    $(".breadcrumb-item a").on("contextmenu", function(){
        generateBreadcumbUrl(this);
    });

    $("#change_date").on('change',(function(e){
        e.preventDefault();

        var datefilter = $(this).children("option:selected").val();

        changeDate();
    }));


    $(document.body).on('change','#pageValues',function(){

    var pageSize = $(this).children("option:selected").val();

    sessionStorage.setItem("pageSize",pageSize);
    
    filterMainData();
    });
});

function createBreadcrumbs(id, initial_username,tier) 
{
    var a = document.createElement("a");
    a.innerHTML = initial_username;
    a.href = "javascript:void(0)";
    var tierId = '';

    if (id == "breadcrumb-own") 
    {
        a.innerHTML = document.getElementById(id).innerHTML;
    }
    else
    {
        tierId = tier;
    }
    
    a.setAttribute("onclick", "generateBreadcumbUrl(this)");
    a.setAttribute("tier", tierId);

    document.getElementById(id).innerHTML = "";
    document.getElementById(id).appendChild(a);
}

function generateBreadcumbUrl(a)
{
    var s_date = $("#s_date1").val();
    var e_date = $("#e_date1").val();
    var c_date = $("#change_date").val();

    var id = $(a).attr("tier");

    if (id != '')
    {
        if(id == "{{ $merc }}")
        {
             a.href = "/reports/winloss/member?id="+id+"&s_date="+s_date+"&e_date="+e_date+"&c_date="+c_date;
        }
        else
        {
             a.href = "/reports/winloss/?id="+id+"&s_date="+s_date+"&e_date="+e_date+"&c_date="+c_date;
        }
    }
    else
    {
        if(auth.getUserLevel() == 0)
            a.href = "/reports/winloss/agent?s_date="+s_date+"&e_date="+e_date+"&c_date="+c_date;
        else
            a.href = "/reports/winloss?s_date="+s_date+"&e_date="+e_date+"&c_date="+c_date;
    }
}

function changeDate()
{
    var today = utils.getToday();
    var oneWeek = utils.getOneWeek();
    var yesterday = utils.getYesterday();
    var thisWeek = utils.getThisWeek();
    var lastWeekFirstDay = utils.getLastWeekFirstDay();
    var lastWeekLastDay = utils.getLastWeekLastDay();
    var monthFirstDay = utils.getMonthFirstDay();
    var pastMonthFirstDay = utils.getPastMonthFirstDay();
    var pastMonthLastDay = utils.getPastMonthLastDay();

    var value = $("#change_date").val();

    if (value == 1)
    {
        utils.datepickerStart('s_date','e_date','s_date1',oneWeek);
        utils.datepickerEnd('s_date','e_date','s_date1',today);
    }
    else if (value == 2)
    {
        utils.datepickerStart('s_date','e_date','s_date1',today);
        utils.datepickerEnd('s_date','e_date','e_date1',today);
    }
    else if (value == 3)
    {
        utils.datepickerStart('s_date','e_date','s_date1',yesterday);
        utils.datepickerEnd('s_date','e_date','e_date1',yesterday);
    }
    else if (value == 4)
    {
        utils.datepickerStart('s_date','e_date','s_date1',thisWeek);
        utils.datepickerEnd('s_date','e_date','e_date1',today);
    }
    else if (value == 5)
    {
        utils.datepickerStart('s_date','e_date','s_date1',lastWeekFirstDay);
        utils.datepickerEnd('s_date','e_date','e_date1',lastWeekLastDay);
    }
    else if (value == 6)
    {
        utils.datepickerStart('s_date','e_date','s_date1',monthFirstDay);
        utils.datepickerEnd('s_date','e_date','e_date1',today);
    }
    else if (value == 7)
    {
        utils.datepickerStart('s_date','e_date','s_date1',pastMonthFirstDay);
        utils.datepickerEnd('s_date','e_date','e_date1',pastMonthLastDay);
    }
}

function prepareLocale()
{
    locale['mainData.username'] = "{!! __('app.reports.winloss.bet.maindata.member') !!}";
    locale['mainData.agentname'] = "{!! __('app.reports.winloss.bet.maindata.agent') !!}";
    locale['mainData.company'] = "{!! __('app.reports.winloss.bet.maindata.company') !!}";
    locale['mainData.txn_id'] = "{!! __('app.reports.winloss.bet.maindata.txnid') !!}";
    locale['mainData.createdat'] = "{!! __('app.reports.winloss.bet.maindata.createdat') !!}";
    locale['mainData.table_id'] = "{!! __('app.reports.winloss.bet.maindata.tableid') !!}";
    locale['mainData.type'] = "{!! __('app.reports.winloss.bet.maindata.type') !!}";    
    locale['mainData.status'] = "{!! __('app.reports.winloss.bet.maindata.status') !!}";
    locale['mainData.turnover'] = "{!! __('app.reports.winloss.bet.maindata.turnover') !!}";
    locale['mainData.member_winloss'] = "{!! __('app.reports.winloss.bet.maindata.memberwl') !!}";
    locale['mainData.total_member'] = "{!! __('app.reports.winloss.bet.maindata.totalmember') !!}";
    locale['mainData.member_comm_amt'] = "{!! __('app.reports.winloss.bet.maindata.membercomm') !!}";
    locale['mainData.total_agent'] = "{!! __('app.reports.winloss.bet.maindata.totalagent') !!}";
    locale['mainData.agent_winloss'] = "{!! __('app.reports.winloss.bet.maindata.agentwl') !!}";
    locale['mainData.agent_comm_amt'] = "{!! __('app.reports.winloss.bet.maindata.agentcomm') !!}";
    locale['mainData.total_com'] = "{!! __('app.reports.winloss.bet.maindata.totalcom') !!}";
    locale['mainData.com_winloss'] = "{!! __('app.reports.winloss.bet.maindata.comwl') !!}";
    locale['mainData.agent_comm'] = "{!! __('app.reports.winloss.bet.maindata.mbrcomm') !!}" + "<br/>" + "{!! __('app.reports.winloss.bet.maindata.agcomm') !!}"
    locale['mainData.agent_pt'] = "{!! __('app.reports.winloss.bet.maindata.compt') !!}" + "<br/>" + "{!! __('app.reports.winloss.bet.maindata.agpt') !!}"
    locale['mainData.number'] = "#";
    locale['mainData.login_name'] = "{!! __('app.reports.winloss.bet.maindata.login') !!}";
    locale['mainData.winloss'] = "{!! __('app.reports.winloss.bet.maindata.winloss') !!}";
    locale['mainData.commission'] = "{!! __('app.reports.winloss.bet.maindata.commission') !!}";
    locale['mainData.total'] = "{!! __('app.reports.winloss.bet.maindata.total') !!}";
    locale['mainData.bet_place'] = "{!! __('app.reports.txn.details.bet') !!}";

    locale['modal.name'] = "{!! __('app.reports.winloss.bet.modal.name') !!}";
    locale['modal.on'] = "{!! __('app.reports.winloss.bet.modal.on') !!}";
}

var mainData;
var pSize = "";

function getMainData()
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var isCustomPageSize = 0;
    
    if((sessionStorage.getItem('pageSize') != null) && (pSize != sessionStorage.getItem('pageSize')))
    {
        isCustomPageSize = 1;
    }

    if(sessionStorage.getItem('pageSize'))
    {
        pSize = sessionStorage.getItem('pageSize');
    }
    else
    {
        pSize = $("#pageValues").children("option:selected").val();
    }

    var data = utils.getDataTableDetails(containerId);

    if($("#s_date").val() == '')
    {
        document.getElementById('s_date1').value = "";
    }

    if($("#e_date").val() == '')
    {
        document.getElementById('e_date1').value = "";
    }
    
    data["txn_id"] = $("#txn_id").val();
    data["member_name"] = $("#member_name").val();
    data["agent_initial_username"] = $("#agent_initial_username").val();
    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();
    data["id"] = utils.getParameterByName('id');
    data['pageSize'] = pSize;
    data['isCustomPageSize'] = isCustomPageSize;

    $.ajax({
        type: "GET",
        url: "/ajax/reports/winloss/bet",
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
    var s_date = $("#s_date1").val();
    var e_date = $("#e_date1").val();

    $("#main-spinner").hide();
    $("#main-table").show();

    var fields1 = [
                    ["",locale['mainData.number'],true,false]
                    ,["",locale['mainData.agentname'],true,false]
                    ,["",locale['mainData.username'],true,false]
                    ,["",locale['mainData.txn_id'],true,false]   
                    ,["",locale['mainData.createdat'],true,false]      
               //     ,["table_id",locale['mainData.table_id'],false,false]
                    ,["",locale['mainData.type'],true,false]
                    ,["",locale['mainData.turnover'],true,false]
                    ,["",locale['mainData.bet_place'],true,false]
                    ,["",locale['mainData.status'],true,false]
                    ,["member_tittle",locale['mainData.username'],false,true]
                    ,["agent_tittle",locale['mainData.agentname'],false,true]
                    ,["company_tittle",locale['mainData.company'],false,true]
                    ,["",locale['mainData.agent_comm'],true,false]
                    ,["",locale['mainData.agent_pt'],true,false]
                ];

    var fields2 = [
                    ["",locale['mainData.winloss']]
                    ,["",locale['mainData.commission']]
                    ,["",locale['mainData.total']]
                    ,["",locale['mainData.winloss']]
                    ,["",locale['mainData.commission']]
                    ,["",locale['mainData.total']]
                    ,["",locale['mainData.winloss']]
                    ,["",locale['mainData.total']]
                ];

    var fields = [
                    ["number",locale['mainData.number'],false,false]
                    ,["agent_initial_name",locale['mainData.agentname'],true,false]
                    ,["member",locale['mainData.username'],true,false]
                    ,["txn_id",locale['mainData.txn_id'],true,false]   
                    ,["created_at",locale['mainData.createdat'],true,false]      
               //     ,["table_id",locale['mainData.table_id'],false,false]
                    ,["game_desc",locale['mainData.type'],false,false]
                    ,["total_turnover",locale['mainData.turnover'],false,true]
                    ,["bet_place",locale['mainData.bet_place'],false,true]
                    ,["status_desc",locale['mainData.status'],false,false]
                    ,["total_winloss",locale['mainData.member_winloss'],false,true]
                    ,["member_comm_amt",locale['mainData.member_comm_amt'],false,true]
                    ,["member_winloss",locale['mainData.total_member'],false,true]
                    ,["agent_winloss",locale['mainData.agent_winloss'],false,true]
                    ,["agent_comm_amt",locale['mainData.agent_comm_amt'],false,true]
                    ,["total_agent",locale['mainData.total_agent'],false,true]
                    ,["com_winloss",locale['mainData.com_winloss'],false,true]
                    ,["com_total",locale['mainData.total_com'],false,true]
                    ,["agent_comm",locale['mainData.agent_comm'],false,true]
                    ,["agent_pt",locale['mainData.agent_pt'],false,true]
                ];

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {   
        $('#notes').show();

        //create header
        var tHead = table.createTHead();
        var row = tHead.insertRow(0); 
        var row1 = tHead.insertRow(1);

        //header - row 1
        for(var j = 0; j < fields1.length; j++)
        {
            var fieldName = fields1[j][0];
            var fieldTitle = fields1[j][1];
            var fieldRowSpan= fields1[j][2];
            var fieldColSpan= fields1[j][3];

            var th = document.createElement('th');

            th.style.textAlign = 'center'; 
            th.innerHTML = fieldTitle;  

            if(fieldRowSpan == true)
            {
                th.setAttribute('rowspan',2);
                th.style.padding = '10px';
            }

            if(fieldColSpan == true)
            {
                if(fields1[j][0] == "member_tittle")
                {
                    th.setAttribute('colspan',3);
                }

                if(fields1[j][0] == "agent_tittle")
                {
                    th.setAttribute('colspan',3);
                }

                if(fields1[j][0] == "company_tittle")
                {
                    th.setAttribute('colspan',2);
                }

            }
            
            row.appendChild(th);
        }

        //header - row 2
        for (var i = 0; i < fields2.length; i++)
        {
            var fieldName1 = fields2[i][0];
            var fieldTitle1 = fields2[i][1];
            var fieldTable1 = fields2[i][2];

            var th1 = document.createElement('th');
            th1.innerHTML = fieldTitle1; 
            th1.style.textAlign = 'center';  

            row1.appendChild(th1);
        }

        table.style.width = "100%";
        table.rows[2].style.display = "none";
           
        var fieldTurnover = utils.getDataTableFieldIdx("total_turnover",fields);
        var fieldMemberWinloss = utils.getDataTableFieldIdx("total_winloss",fields);
        var fieldMemberComm = utils.getDataTableFieldIdx("member_comm_amt",fields);
        var fieldTotalMember = utils.getDataTableFieldIdx("member_winloss",fields);
        var fieldAgentComm = utils.getDataTableFieldIdx("agent_comm_amt",fields);
        var fieldTotalAgent = utils.getDataTableFieldIdx("total_agent",fields);
        var fieldAgentWinloss = utils.getDataTableFieldIdx("agent_winloss",fields);
        var fieldComWinloss = utils.getDataTableFieldIdx("com_winloss",fields);
        var fieldComTotal = utils.getDataTableFieldIdx("com_total",fields);
        var fieldStatus = utils.getDataTableFieldIdx("status_desc", fields);
        var fieldTxnId = utils.getDataTableFieldIdx("txn_id", fields);
        var fieldNumber = utils.getDataTableFieldIdx("number",fields);
        var fieldPt = utils.getDataTableFieldIdx("agent_pt", fields);
        var fieldComm = utils.getDataTableFieldIdx("agent_comm",fields);
        var fieldInitialName = utils.getDataTableFieldIdx("agent_initial_name",fields);
        var fieldMember = utils.getDataTableFieldIdx("member",fields);

        for (var i = 3, row; row = table.rows[i]; i++)
        {   
            var turnover = mainData.results[i - 3]["total_turnover"];
            var memberWinloss = mainData.results[i - 3]["total_winloss"];
            var memberComm = mainData.results[i - 3]["member_comm_amt"];
            var totalMember = mainData.results[i - 3]["member_winloss"];
            var agentComm = mainData.results[i - 3]["agent_comm_amt"];
            var agentWinloss = mainData.results[i - 3]["agent_winloss"];
            var comWinloss = mainData.results[i - 3]["com_winloss"];
            var comTotal = mainData.results[i - 3]["com_total"];
            var totalAgent = mainData.results[i - 3]["total_agent"];

            row.cells[fieldInitialName].innerHTML = mainData.results[i - 3]["agent_initial_name"].toLowerCase();
            row.cells[fieldMember].innerHTML = mainData.results[i - 3]["member"].toLowerCase();

            row.cells[fieldNumber].innerHTML = i;

            if(mainData.results[i - 3]["type"])
            {
                row.cells[fieldTxnId].innerHTML = "";

                var aDetails = document.createElement("a");
                aDetails.innerHTML = mainData.results[i-3]['txn_id'];
                aDetails.setAttribute('href','javascript:void(0)');
                aDetails.setAttribute('onclick','showDetailsTableModal("'+mainData.results[i - 3]["txn_id"]+ '",' +mainData.results[i - 3]["member_id"]+ ',' +mainData.results[i - 3]["round_id"]+')');
                row.cells[fieldTxnId].appendChild(aDetails);
            }

                row.cells[fieldPt].innerHTML = mainData.results[i - 3]["com_pt"] + '% <br/>' + parseInt(mainData.results[i - 3]["agent_pt"]) + '%' ;
                row.cells[fieldComm].innerHTML = mainData.results[i - 3]["member_comm"] + '% <br/>' +  mainData.results[i - 3]["agent_comm"] + '%';
            
            if(turnover < 0)
            {   
                row.cells[fieldTurnover].style.color="red";

            }

            if(memberWinloss < 0)
            {   
                row.cells[fieldMemberWinloss].style.color="red";

            }

            if(agentWinloss < 0)
            {   
                row.cells[fieldAgentWinloss].style.color="red";

            }

            if(comWinloss < 0)
            {   
                row.cells[fieldComWinloss].style.color="red";

            }

            if(comTotal < 0)
            {   
                row.cells[fieldComTotal].style.color="red";

            }

            if(totalAgent < 0)
            {   
                row.cells[fieldTotalAgent].style.color="red";

            }

            if(totalMember < 0)
            {   
                row.cells[fieldTotalMember].style.color="red";

            }

            row.cells[fieldTurnover].innerHTML =  utils.formatMoney(turnover);
            row.cells[fieldMemberWinloss].innerHTML =  utils.formatMoney(memberWinloss);
            row.cells[fieldMemberComm].innerHTML =  utils.formatMoney(memberComm);
            row.cells[fieldTotalMember].innerHTML =  utils.formatMoney(totalMember);
            row.cells[fieldAgentWinloss].innerHTML =  utils.formatMoney(agentWinloss);
            row.cells[fieldAgentComm].innerHTML =  utils.formatMoney(agentComm);
            row.cells[fieldAgentWinloss].innerHTML =  utils.formatMoney(agentWinloss);
            row.cells[fieldComWinloss].innerHTML =  utils.formatMoney(comWinloss);
            row.cells[fieldComTotal].innerHTML =  utils.formatMoney(comTotal);
            row.cells[fieldTotalAgent].innerHTML =  utils.formatMoney(totalAgent);


            if(mainData.results[i - 3]["status"]== 'w')
                row.cells[fieldStatus].innerHTML = '<span style="color:#11b835; font-weight:700">'+mainData.results[i - 3]["status_desc"]+'</span>';
            else if(mainData.results[i - 3]["status"]== 'l')
                row.cells[fieldStatus].innerHTML = '<span style="color:red; font-weight:700">'+mainData.results[i - 3]["status_desc"]+'</span>';
            else if(mainData.results[i - 3]["status"]== 't')
                row.cells[fieldStatus].innerHTML = '<span style="color:#1b7ff7; font-weight:700">'+mainData.results[i - 3]["status_desc"]+'</span>';
            else if(mainData.results[i - 3]["status"]== 'p')
                row.cells[fieldStatus].innerHTML = '<span style="color:#dbce0f; font-weight:700">'+mainData.results[i - 3]["status_desc"]+'</span>';
            else if(mainData.results[i - 3]["status"]== 'r')
                row.cells[fieldStatus].innerHTML = '<span style="color:#54544f; font-weight:700">'+mainData.results[i - 3]["status_desc"]+'</span>';
            
        }
    }
}

function showDetailsTableModal(txnId,memberId, roundId)
{
    $("#modalDetails").modal('show');

    $("#main-details-spinner").show();
    $("#main-details-table").hide();

    $.ajax({
        type: "GET",
        url: "/ajax/reports/winloss/bet/details",
        data: {"txn_id" : txnId,"member_id" : memberId, "round_id" : roundId},
        success: function(data) 
        {
            if(data.length > 0)
            {
                mainHistoryData = JSON.parse(data);
                loadHistoryMainData();

            }
            else
            {
                mainHistoryData = [];
            }

        }
    });
}

function loadHistoryMainData()
{
    $("#main-details-spinner").hide();
    $("#main-details-table").show();

    //player details
    var memberName = mainHistoryData['initial_username'];
    var game = mainHistoryData['game_desc'];
    var startTime = mainHistoryData['created_at'];

    $("#details-member-name").html(memberName);
    $("#details-game").html(game);
    $("#details-start-time").html(startTime);

     //round details
    var roundId = mainHistoryData['id'];
    var tableId = mainHistoryData['table_name'];
    var status = mainHistoryData['status_desc'];
    var type = mainHistoryData['type'];

    if(mainHistoryData['status'] == 'CANCEL')
    {
        var endTime = mainHistoryData['canceled_at'];

    }
    else
    {
        if(mainHistoryData['status'] == 'COMPLETED')
        {
            var endTime = mainHistoryData['settled_at'];
        }
        else
        {
            var endTime = "-";
        }
    }

    $("#details-round-id").html(roundId);
    $("#details-table-id").html(tableId);
    $("#details-start-time").html(startTime);
    $("#details-end-time").html(endTime);
    $("#details-status").html(status);
    
    var bCard1 = mainHistoryData['b_card_1'];
    var bCard2 = mainHistoryData['b_card_2'];
    var bCard3 = mainHistoryData['b_card_3'];
    var arrayBCards = [];

    arrayBCards.push(bCard1, bCard2, bCard3);

    var pCard1 = mainHistoryData['p_card_1'];
    var pCard2 = mainHistoryData['p_card_2'];
    var pCard3 = mainHistoryData['p_card_3'];

    var arrayPCards = [];

    arrayPCards.push(pCard1, pCard2, pCard3);


    var bCardsField = document.getElementById("b-cards");
    var pCardsField = document.getElementById("p-cards");

    bCardsField.innerHTML = "";
    pCardsField.innerHTML = "";

    for (var i = 0; i < arrayBCards.length; i++) 
    {
        if(arrayBCards[i] != '-')
        {
            var img = arrayBCards[i];

            var imageBcards = document.createElement('img');

            imageBcards.setAttribute('src', '/images/avacard/' + img+"_v1" + '.png');

            imageBcards.style.width = "55px";
            imageBcards.style.margin = "5px 3px";
            
            if (i == 1) 
            {
                imageBcards.style.marginRight = "11px";
            }

            if (i == 2) 
            {
                imageBcards.style.marginRight = "5px";
                imageBcards.style.marginLeft = "6px";
                imageBcards.style.marginTop = "1px";
                imageBcards.style.transform = "rotate(90deg)";
            }

            bCardsField.appendChild(imageBcards);
        }
    }

    var bScoreSpan = document.createElement('span');
    bScoreSpan.setAttribute("class","circle mr-3");
    bScoreSpan.style.marginTop = "35px";
    bScoreSpan.innerHTML = mainHistoryData['banker_sum'];
    bCardsField.appendChild(bScoreSpan);

    for (var i = 0; i < arrayPCards.length; i++) 
    {
        if(arrayPCards[i] != '-')
        {
            var img = arrayPCards[i];

            var imagePcards = document.createElement('img');

            imagePcards.setAttribute('src', '/images/avacard/' + img+"_v1" + '.png');
            
            imagePcards.style.width = "55px";
            imagePcards.style.margin = "5px 3px";

            if (i == 1) 
            {
                imagePcards.style.marginRight = "11px";
            }

            if (i == 2) 
            {
                imagePcards.style.transform = "rotate(90deg)";
                imagePcards.style.marginLeft = "6px";
                imagePcards.style.marginTop = "1px";
                imagePcards.style.marginRight = "5px";
            }

            pCardsField.appendChild(imagePcards);
        }
        
    }

    var pScoreSpan = document.createElement('span');
    pScoreSpan.setAttribute("class","circle mr-3");
    pScoreSpan.style.marginTop = "35px";
    pScoreSpan.innerHTML = mainHistoryData['player_sum'];
    pCardsField.appendChild(pScoreSpan);

    // var totalBet = 0;
    // var totalPayout = 0;
    // var totalNet = 0;

    // var table = document.getElementById("round-results-table");
    // var rowNo = table.getElementsByTagName("tr");

    // //remove previous row
    // for(var j = rowNo.length-1; j > 0; j--)
    // {
    //     table.deleteRow(j);
    // }
    // //insert row
    // for(var i=0; i < mainHistoryData['participants']['bets'].length; i++)
    // {
    //     var rowIndex = i + 1;

    //     var row = table.insertRow(rowIndex);
    //     var cell1 = row.insertCell(0);
    //     var cell2 = row.insertCell(1);
    //     var cell3 = row.insertCell(2);
    //     var cell4 = row.insertCell(3);
    //     var cell5 = row.insertCell(4);

    //     cell1.className = 'is-breakable';
    //     cell2.className = 'is-breakable';
    //     cell3.className = 'is-breakable';
    //     cell4.className = 'is-breakable';
    //     cell5.className = 'is-breakable';

    //     cell1.innerHTML = mainHistoryData['participants']['bets'][i]['transactionId'];
    //     cell2.innerHTML = mainHistoryData['participants']['bets'][i]['bettype_desc'];
    //     cell3.innerHTML = utils.formatMoney(mainHistoryData['participants']['bets'][i]['stake']);
    //     var net = mainHistoryData['participants']['bets'][i]['payout'] - mainHistoryData['participants']['bets'][i]['stake'];
    //     cell4.innerHTML = utils.formatMoney(net);
    //     cell5.innerHTML = mainHistoryData['participants']['bets'][i]['placedOn'];

    //     if(net < 0)
    //     {
    //         cell4.style.color = 'red';
    //     }

    //     totalBet = totalBet + parseFloat(mainHistoryData['participants']['bets'][i]['stake']);
    //     totalPayout = totalPayout + parseFloat(mainHistoryData['participants']['bets'][i]['payout']);
    // }

    // totalNet = totalPayout - totalBet;
    // totalBet = utils.formatMoney(totalBet);
    // totalPayout = utils.formatMoney(totalPayout);

    // $("#total-bet").html(totalBet);
    // $("#total-payout").html(totalPayout);
    // $("#total-net").html(utils.formatMoney(totalNet));

    // if(totalNet < 0)
    // {
    //     $("#total-net").css("color", "red");
    // }
    // else
    // {
    //     $("#total-net").css("color", "");
    // }

    var betAmount = mainHistoryData['stake'];
    var payout = mainHistoryData['payout'];
    var net = parseFloat(payout) - parseFloat(betAmount);

    $("#details-place-time").html(mainHistoryData['placedOn']);
    $("#details-txnid").html(mainHistoryData['transactionId']);
    $("#details-bet-place").html(mainHistoryData['bettype_desc']);
    $("#details-bet-result").html(mainHistoryData['result_desc']);
    $("#bet-amount").html(utils.formatMoney(betAmount));
    $("#payout").html(utils.formatMoney(payout));
    $("#net").html(utils.formatMoney(net));

    if(net < 0)
    {
        $("#net").css("color", "red");
    }
    else
    {
        $("#net").css("color", "");
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

</script>

<style type="text/css">

    .modal-lg
    {
        max-width: 630px;
    }

    th, td 
    {
      padding: 5px;
    }

    .fields
    {
      font-weight: bolder;
    }

    .is-breakable {
      word-break: break-word;
    }

    .circle
    {
        float: right;
        width: 22px;
        background: rgb(170, 170, 170);
        color: rgb(255, 255, 255);
        font-size: 15px;
        text-align: center;
        margin: 15px 0px 15px 15px;
        border-radius: 8px;
    }

</style> 

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.reports.winloss.bet.breadcrumb.reports') }}</li>
    <li class="breadcrumb-item active">{{ __('app.reports.winloss.bet.breadcrumb.betrecord') }}</li>
    <li id="breadcrumb-own" class="breadcrumb-item">{{ Auth::user()->initial_username }}</li>
     @foreach($data as $d)
        <li id="breadcrumb-tier-{{$d->level}}" class="breadcrumb-item">{{ $d->initial_username }}</li>
    @endforeach
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="filterForm">

                <div class="card-header">
                    <strong>{{ __('app.reports.winloss.bet.breadcrumb.betrecord') }}</strong>
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('common.filter.datefilter') }}</label>
                                <select class="form-control" id="change_date" name="change_date" autocomplete="off">
                                    <option value="1">{{ __('common.filter.pastseven') }}</option>
                                    <option value="2">{{ __('common.filter.today') }}</option>
                                    <option value="3">{{ __('common.filter.ytd') }}</option>
                                    <option value="4">{{ __('common.filter.thisweek') }}</option>
                                    <option value="5">{{ __('common.filter.lastweek') }}</option>
                                    <option value="6">{{ __('common.filter.thismonth') }}</option>
                                    <option value="7">{{ __('common.filter.lastmonth') }}</option>
                                </select>
                            </div>
                        </div>
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
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('app.reports.winloss.bet.filter.txnid') }}</label>
                                <input type="text" class="form-control" id="txn_id" placeholder="" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('app.reports.winloss.bet.filter.membername') }}</label>
                                <input type="text" class="form-control" id="member_name" placeholder="" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('app.reports.winloss.bet.filter.agentname') }}</label>
                                <input type="text" class="form-control" id="agent_initial_username" placeholder="" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-auto" style="text-align: center;">
                            <button class="btn-filter" type="button" id="submit" onclick="filterMainData()"> {{ __('common.filter.submit') }}</button>
                        </div>
                    </div>
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

<div id="modalDetails" class="modal fade" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content" style="margin-top: 156px;">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.reports.txn.details') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="main-details-spinner" class="card-body"></div>

                    <div id="main-details-table" class="card-body table-responsive">
                        <table id="table-details" class="table-bordered" style="width: 100%;">
                            <col width="25%">
                            <col width="25%">
                            <col width="50%">
                            <tr>
                                <td class="fields">
                                   {{ __('app.reports.txn.details.name') }}
                                </td>
                                <td id="details-member-name" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                   {{ __('app.reports.txn.details.game') }}
                                </td>
                                <td id="details-game" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                   {{ __('app.reports.txn.details.round_id') }}
                                </td>
                                <td id="details-round-id" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.table_id') }}
                                </td>
                                <td id="details-table-id" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.status') }}
                                </td>
                                <td id="details-status" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                   {{ __('app.reports.txn.details.starttime') }}
                                </td>
                                <td id="details-start-time" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.endtime') }}
                                </td>
                                <td id="details-end-time" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.placetime') }}
                                </td>
                                <td id="details-place-time" colspan="2">
                                </td>
                            </tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.amount') }}
                                </td>
                                <td id="bet-amount" colspan="2">
                                </td>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.txnid') }}
                                </td>
                                <td id="details-txnid" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.bet') }}
                                </td>
                                <td id="details-bet-place" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.result') }}
                                </td>
                                <td id="details-bet-result" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.totalpayout') }}
                                </td>
                                <td id="payout" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td class="fields">
                                    {{ __('app.reports.txn.details.totalnet') }}
                                </td>
                                <td id="net" colspan="2">
                                </td>
                            </tr>
                            <tr>
                                <td align="center"  colspan="2">
                                    <b>{{ __('app.reports.txn.details.banker') }}</b>
                                </td>
                                <td align="center" colspan="2">
                                    <b>{{ __('app.reports.txn.details.player') }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td id="b-cards" align="left" colspan="2">
                                </td>
                                <td id="p-cards" align="left" colspan="2">
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection
