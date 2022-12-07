@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{

    prepareLocale();

    utils.createSpinner("main-spinner");

     var tier = utils.getParameterByName('tier');

    createBreadcrumbs("breadcrumb-own");

    if (tier != null) 
    {
        if('{{$upTier1}}'!= '')
        {
            createBreadcrumbs("breadcrumb-uptier1",'{{$upTierUsername1}}','{{$upTier1}}'); 
        }

        if('{{$upTier2}}'!= '')
        {
            createBreadcrumbs("breadcrumb-uptier2",'{{$upTierUsername2}}','{{$upTier2}}'); 
        }
        if('{{$upTier3}}'!= '')
        {
            createBreadcrumbs("breadcrumb-uptier3",'{{$upTierUsername3}}','{{$upTier3}}'); 
        }
    }   

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    $("#modalAction").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));
});

function createBreadcrumbs(id, username,tier) 
{
    var a = document.createElement("a");
    a.innerHTML = username;

    if(id == "breadcrumb-own")
    {   
        a.innerHTML = document.getElementById(id).innerHTML;
        document.getElementById(id).innerHTML = "";
    }

    if (tier != null) 
    {
        a.href = "/merchants/merchant/credit?tier="+tier;
    }
    else
    {
        a.href = "/merchants/merchant/credit";
    }
    
    document.getElementById(id).appendChild(a);
    
    $("#" + id).addClass("d-md-block");

    $("#" + id).clone().attr('id',id + '-m').appendTo('#breadcrumb-m');

    $("#" + id + '-m').removeClass("d-none");
}

function prepareLocale()
{
    if('{{$levelByTier}}' == 3)
    {    

        locale['mainData.merchantcode'] = "{!! __('app.merchants.credit.member') !!}";
         
    }
    else
    {
        locale['mainData.merchantcode'] = "{!! __('app.merchants.credit.merchantcode') !!}";
    }

    locale['mainData.credit'] = "{!! __('app.merchants.credit.credit') !!}";
    locale['mainData.action'] = "{!! __('app.merchants.credit.action') !!}";
    locale['mainData.action.withdraw'] = "{!! __('app.merchants.credit.action.withdraw') !!}";
    locale['mainData.action.deposit'] = "{!! __('app.merchants.credit.action.deposit') !!}";
    locale['mainData.given'] = "{!! __('app.merchants.credit.total_credit') !!}";
    locale['mainData.available'] = "{!! __('app.merchants.credit.available') !!}";
    locale['mainData.amount.deposit'] = "{!! __('app.merchants.credit.amount.deposit') !!}";
    locale['mainData.amount.withdraw'] = "{!! __('app.merchants.credit.amount.withdraw') !!}";

    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
}

var refreshMainData = false;
var mainData;

function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();

    var data = utils.getDataTableDetails(containerId);

    data["username"] = $("#f_username").val();
    data["tier"] = utils.getParameterByName('tier');

    $.ajax({
        type: "GET",
        url: "/ajax/merchants/merchant/credit",
        data: data,
        success: function(data) 
        {
            if(data.length > 0)
            {
                var tmpData = JSON.parse(data);
                mainData = tmpData[0];
                ownAvailable = tmpData[1];
            }
            else
            {
                mainData = [];
                ownAvailable = '';
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
                    ["username",locale['mainData.merchantcode'],true,false]             
                    ,["available",locale['mainData.credit'],false,true]
                    @can('permissions.edit_agent_credit') 
                    ,["",locale['mainData.action'],false,false]                   
                    @endcan
                ];

    if(utils.getParameterByName('tier'))
    {    

        for(var i = fields.length-1 ; i > 0; i--)
        {
            if(fields[i][0] == "")
            {
                fields.splice(i,1);
            }
        }
         
    } 

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();

        var fieldMerchant = utils.getDataTableFieldIdx("username",fields);
        var fieldCredit = utils.getDataTableFieldIdx("available",fields);
        var fieldActions = utils.getDataTableFieldIdx("",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            var level = mainData.results[i - 1]["level"];
            var merchant = mainData.results[i - 1]["username"];
            var id = mainData.results[i - 1]["id"];

            if(auth.getUserLevel() != 3 && '{{$levelByTier}}' != 3) 
            {  
                //username
                var a = document.createElement("a");
                a.href = "/merchants/merchant/credit?tier=" + id;
                a.innerHTML = merchant;
                row.cells[fieldMerchant].innerHTML =  "";
                row.cells[fieldMerchant].appendChild(a);
            }

            row.cells[fieldCredit].innerHTML =  utils.formatMoney(mainData.results[i - 1]["available"]);

            @can('permissions.edit_agent_credit') 

            if(!utils.getParameterByName('tier'))
            {
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

                row.cells[fieldActions].innerHTML =  "";
                row.cells[fieldActions].appendChild(btnDeposit);
                row.cells[fieldActions].appendChild(btnWithdraw);
            }

            @endcan
        }
    }
}

function showModal()
{
    var id = mainData.results[this.rowId - 1]["id"];

    var merchant = mainData.results[this.rowId - 1]["username"];
    var available = utils.formatMoney(mainData.results[this.rowId - 1]["available"]);

    $("#modalMessage").hide();

    $("#modal-id").val(id);
    $("#modal-username").val(merchant);
    $("#modal-available").val(available);
    $("#type").val(this.value);
    $("#modal-amount").val("");
    $("#modal-remarks").val("");
    $("#available").html(locale['mainData.available'] + ': '+ utils.formatMoney(ownAvailable));

    if(this.value == '1')
    {
        $("#title").html('<b>'+locale['mainData.action.deposit']+'</b>');
        $("#title-amount").html(locale['mainData.amount.deposit']);
    }
    else
    {
        $("#title").html('<b>'+locale['mainData.action.withdraw']+'</b>');
        $("#title-amount").html(locale['mainData.amount.withdraw']);
    }

    $("#modalAction").modal('show');

    refreshMainData = false;

    checkCredit();
    
}

function submitAction()
{ 
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmit","modalAction");


    $("#formAction").attr("enabled",0);

    $.ajax({
        url: "/ajax/merchants/merchant/credit_transfer",
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

function getMember()
{
    var merchant = mainData.results[this.rowId - 1]["id"];

    window.location.href = "/merchants/merchant/member?id=" + merchant;
}

function onModalDismiss() 
{
    if(refreshMainData)
    {
        getMainData();
    }
}

function checkCredit()
{
    var input = $("#modal-amount");
    utils.formatCurrencyInput(input);
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
    $("#f_username").val("");

    filterMainData();
}
</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.merchants.credit.breadcrumbs.agentmanagement') }}</li>
    <li class="breadcrumb-item">{{ __('app.merchants.credit.breadcrumbs.credit') }}</li>
    <li id="breadcrumb-own" class="breadcrumb-item d-none d-md-block">{{ Auth::user()->username }}</li>
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
                        <div class="col-sm-2">

                            <div class="form-group">
                                @if($levelByTier == 3)
                                    <label for="merchantcode">{{ __('app.merchants.credit.filter.member') }}</label>
                                @else
                                    <label for="merchantcode">{{ __('app.merchants.credit.filter.merc') }}</label>
                                @endif

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

            <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>

        </div>
    </div>
</div>

<div id="modalAction" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
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

                                    <label>{{ __('app.merchants.credit.merchantcode') }}</label>

                                </div>

                                <div class="col-sm-6">

                                    <input type="text" id="modal-username" name="merchant_code" class="form-control" readonly="">

                                </div>
                            </div>

                           <div class="form-group row">

                                <div class="col-sm-6">

                                    <label>{{ __('app.merchants.credit.credit') }}</label>

                                </div>

                                <div class="col-sm-6">

                                    <input type="text" id="modal-available" name="available" class="form-control" disabled="">

                                </div>
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-6">

                                    <label id="title-amount"></label>

                                </div>

                                <div class="col-sm-6">

                                    <input type="text" id="modal-amount" name="amount" class="form-control">
                                        <b style="float: right;" id="available"></b>
                                </div>
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-6">

                                    <label>Remarks (Optional)</label>

                                </div>

                                <div class="col-sm-6">

                                    <input type="text" id="modal-remarks" name="remarks" class="form-control">
                                </div>
                            </div>
                           
                        </div>

                        <div class="card-footer">

                            <button id="btnSubmit" type="button" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitAction()">
                                <i class="fa fa-dot-circle-o"></i> {{ __('common.modal.submit') }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection
