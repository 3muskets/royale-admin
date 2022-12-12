@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{

    prepareLocale();

    checkCredit();

    utils.createSpinner("main-spinner");

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    $("#modalAction").on('submit',(function(e){
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
    locale['mainData.username'] = "{!! __('app.member.credit.username') !!}";
    locale['mainData.agent'] = "Agent";
    locale['mainData.given'] = "{!! __('app.member.credit.total_credit') !!}";
    locale['mainData.credit'] = "{!! __('app.member.credit.credit') !!}";
    locale['mainData.action'] = "{!! __('app.member.credit.action') !!}";
    locale['mainData.action.deposit'] = "{!! __('app.member.credit.action.deposit') !!}";
    locale['mainData.action.withdraw'] = "{!! __('app.member.credit.action.withdraw') !!}";
    locale['mainData.action.adjustment']  = "{!! __('app.member.credit.action.adjustment') !!}";
    locale['mainData.available'] = "{!! __('app.member.credit.available') !!}";
    locale['tooltip.check'] = "<input type='checkbox' id='checkAll' onclick='checkAll(this);'>";
    locale['error.check'] = "{!! __('app.member.credit.error.check') !!}";

    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
}

var refreshMainData = false;
var mainData;
var arrStatus = [];

function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();
    $("#amount-check").hide();
    $("#credit").val("");

    var data = utils.getDataTableDetails(containerId);

    data["username"] = $("#f_username").val();
    data["tier4"] = $("#f_tier4").val();
    data["agent_id"] = $("#agent_id").val();

    $.ajax({
        type: "GET",
        url: "/ajax/merchants/merchant/member/credit",
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
                                      
                    ["username",locale['mainData.username'],true,false]
                    ,["agent",locale['mainData.agent'],true,false]   
                    // ,["check",locale['tooltip.check'],false,false]                 
                    ,["available",locale['mainData.credit'],false,true]
                    @can('permissions.edit_member_credit')
                    ,["",locale['mainData.action'],false,false]
                    @endcan

                ];

    if(auth.getUserLevel() == 3)
    {    

        for(var i = fields.length-1 ; i > 0; i--)
        {
            if(fields[i][0] == "admin")
            {
                fields.splice(i,1);
            }
        }
         
    }

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();

        var fieldCredit = utils.getDataTableFieldIdx("available",fields);
        var fieldActions = utils.getDataTableFieldIdx("",fields);
        var member = "";

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            var available = mainData.results[i - 1]["available"];
            member = mainData.results[i - 1]["id"];
           
            row.cells[fieldCredit].innerHTML =  utils.formatMoney(available);

            @can('permissions.edit_member_credit')
                var btnDeposit = document.createElement("BUTTON");
                btnDeposit.innerHTML = locale['mainData.action.deposit'];
                btnDeposit.className = "btn btn-sm btn-primary";
                btnDeposit.onclick = showModal;
                btnDeposit.rowId = i;
                btnDeposit.style.margin = "0 15px 0 0";
                btnDeposit.value = "1";

                var btnWithdraw = document.createElement("BUTTON");
                btnWithdraw.innerHTML = locale['mainData.action.withdraw'];
                btnWithdraw.className = "btn btn-sm btn-primary";
                btnWithdraw.onclick = showModal;
                btnWithdraw.rowId = i;
                btnWithdraw.style.margin = "0 15px 0 0";
                btnWithdraw.value = "2";

                var btnAdjustment = document.createElement("BUTTON");
                btnAdjustment.innerHTML = locale['mainData.action.adjustment'];
                btnAdjustment.className = "btn btn-sm btn-primary";
                btnAdjustment.onclick = showModal;
                btnAdjustment.rowId = i;
                btnAdjustment.style.margin = "0 15px 0 0";
                btnAdjustment.value = "3";


                row.cells[fieldActions].innerHTML = '';

                if(auth.getUserLevel() == mainData.results[i - 1]["level"] || auth.getUserLevel() == 0)
                {
                    row.cells[fieldActions].appendChild(btnDeposit);
                    row.cells[fieldActions].appendChild(btnWithdraw);
                    row.cells[fieldActions].appendChild(btnAdjustment);
                }
            @endcan


                if(arrStatus.length != 0)
                { 
                    for(var j =0; j < arrStatus.length; j++)
                    {    
                        if(arrStatus[j]["member"] == member) 
                        {   
                            if(arrStatus[j]["status"]  == 1 )
                            {
                                row.cells[fieldCredit].innerHTML = '<span class="badge badge-success">'+ utils.formatMoney(available) +'</span>'
                            }
                            else if(arrStatus[j]["status"]  == 0 )
                            {
                                row.cells[fieldCredit].innerHTML = '<span class="badge badge-danger">'+ utils.formatMoney(available) +'</span>'
                            }

                            row.cells[fieldCredit].setAttribute("data-toggle", "tooltip");
                            row.cells[fieldCredit].style.cursor = 'pointer';
                            row.cells[fieldCredit].setAttribute("title", arrStatus[j]["message"]);
                        }      
                    } 
                }

                $("#amount-check").show();
            }
        // }
    }
}

function checkAll(e)
{
    var checked = e.checked;
    if (checked) 
    {
        $('input[name="check[]"]').prop('checked', true);
    } 
    else 
    {
        $('input[name="check[]"]').prop('checked', false);
    }
}

function submitAll(type)
{
    var data = {};
    var check = [];
    arrStatus = [];

    $.each($("input[name='check[]']:checked"), function(){

        var id = $(this).attr('id');
        var i = id.split('_')[1];
        check.push(mainData.results[i - 1]["id"]);

    });

    if (check.length == 0) 
    {
        alert(locale['error.check']);
        return false;
    }

    var credit = $("#credit").val();
   
    data['check'] = check;
    data['credit'] = credit;
    data['type'] = type;
    
    utils.startLoadingBtn("","main-table");

    $.ajax({
        type: "POST",
        url: "/ajax/merchants/merchant/member/all/credit_transfer",
        data: data,
        success: function(data) 
        {   
            $('input[name="check[]"],#checkAll').prop('checked', false);
            utils.stopLoadingBtn("","main-table");

            var statusData = JSON.parse(data);

            if(statusData.error)
            {
                utils.showModal(locale['error'],statusData.error,statusData.status);                
            }
            else
            {
                for(var j = 0; j < statusData.length; j++)
                {   
                    arrStatus.push({'status':statusData[j].status,'member':statusData[j].member,'message':statusData[j].message});

                }
            }

            getMainData();

        },
        error: function(data){

        } 
    });
}

function showModal()
{
    var id = mainData.results[this.rowId - 1]["id"];

    var member = mainData.results[this.rowId - 1]["username"];
    var available = utils.formatMoney(mainData.results[this.rowId - 1]["available"]);

    $("#modalMessage").hide();
    $("#form-adj-type").hide();

    $("#modal-id").val(id);
    $("#modal-username").val(member);
    $("#modal-available").val(available);
    $("#type").val(this.value);
    $("#modal-amount").val("");
    $("#modal-remarks").val("");
    $("#available").html(locale['mainData.available'] + ': ' + utils.formatMoney(mainData.results[this.rowId - 1]["admin_credit"]));

    if(this.value == '1')
    {
        $("#title").html('<b>'+locale['mainData.action.deposit']+'</b>');
        
    }
    else if(this.value == '2')
    {
        $("#title").html('<b>'+locale['mainData.action.withdraw']+'</b>');
    }
    else if(this.value == '3')
    {
        $("#title").html('<b>'+locale['mainData.action.adjustment']+'</b>');

        $("#form-adj-type").show();
    }

    $("#modalAction").modal('show');

    checkCredit();

    refreshMainData = false;
    
}

function submitAction()
{ 
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmit","modalAction");

    $("#formAction").attr("enabled",0);

    $.ajax({
        url: "/ajax/merchants/merchant/member/credit_transfer",
        type: "POST",
        data:  new FormData($("#formAction")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            utils.stopLoadingBtn("btnSubmit","modalAction");
            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $("#modalAction").modal('hide');
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onModalDismissError);
            }
        },
        error: function(){}             
    }); 
}

function checkCredit()
{
    var input = $("#modal-amount, #credit");
    utils.formatCurrencyInput(input);
}

function onModalDismiss() 
{
    if(refreshMainData)
    {
        getMainData();
    }
}

function onModalDismissError()
{
    $("#formAction").attr("enabled",1);
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
    $("#f_username, #f_tier4").val("");
    $("#agent_id").val("");
    filterMainData();
}
</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.member.credit.breadcrumbs.membermanagement') }}</li>
    <li class="breadcrumb-item active">{{ __('app.member.credit.breadcrumbs.credit') }}</li>
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
                                <label>{{ __('app.member.credit.filter.search') }}</label>
                                <input type="text" class="form-control" id="f_username" placeholder="">
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

            <!-- <div id="amount-check" style="border:none; padding: 0 1.25rem;">
                <table class="table-sm">
                    <tbody>
                        <tr>
                            <td colspan="2">{{ __('app.member.credit.amount') }}</td>
                            <td><input type="text" id="credit" name="credit" class="form-control input-sm"></td>
                            <td>
                                <button onclick="submitAll(1);" type="button" class="btn btn-primary btn-ladda btn-sm" style="margin:0 15px 0 0;">
                                    {!! __('app.member.credit.action.deposit') !!}
                                </button>
                                <button onclick="submitAll(2);" type="button" class="btn btn-primary btn-ladda btn-sm" style="margin:0 15px 0 0;">
                                    {!! __('app.member.credit.action.withdraw') !!}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div> -->

            <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>

        </div>
    </div>
</div>

<div id="modalAction" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="title"></h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formAction">
                        
                        <input type="hidden" id="modal-id" name="id">
                        <input type="hidden" name="type" id="type">

                        <div class="card-body">

                            <div class="form-group row">

                                <div class="col-sm-6">

                                    <label>{{ __('app.member.credit.username') }}</label>

                                </div>

                                <div class="col-sm-6">

                                    <input type="text" id="modal-username" name="username" class="form-control" readonly="">

                                </div>
                            </div>

                           <div class="form-group row">

                                <div class="col-sm-6">

                                    <label>{{ __('app.member.credit.credit') }}</label>

                                </div>

                                <div class="col-sm-6">

                                    <input type="text" id="modal-available" name="available" class="form-control" disabled="">

                                </div>
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-6">

                                    <label>{{ __('app.member.credit.amount') }}</label>

                                </div>

                                <div class="col-sm-6">

                                    <input type="text" id="modal-amount" name="amount" class="form-control">
                                    <b style="float: right;" id="available"></b>
                                </div>
                            </div>

                            <div class="form-group row" id="form-adj-type">

                                <div class="col-sm-6">
                                    <label>+/-</label>
                                </div>
                                <div class="col-sm-6">
                                    <select id="modal-adj-type" name="adj_type" class="form-control">
                                        <option value="1">{{ __('app.member.credit.action.add') }}</option>
                                        <option value="2">{{ __('app.member.credit.action.deduct') }}</option>
                                    </select>
                                </div>
                            </div>


                            <div class="form-group row">

                                <div class="col-sm-6">

                                    <label>Remarks (Optional)</label>

                                </div>

                            </div>

                            <div class="form-group row">

                                <div class="col-sm-12">

                                    <input type="text" id="modal-remarks" name="remarks" class="form-control">
                                </div>
                            </div>

                        </div>

                        <div class="card-footer">

                            <button id="btnSubmit" type="button" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitAction()">
                                <i class="fa fa-dot-circle-o"></i>  {{ __('common.modal.submit') }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection
