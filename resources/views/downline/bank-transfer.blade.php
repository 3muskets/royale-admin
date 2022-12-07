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
    locale['mainData.agent'] = "Company/Agent";
    locale['mainData.given'] = "{!! __('app.member.credit.total_credit') !!}";
    locale['mainData.credit'] = "{!! __('app.member.credit.credit') !!}";
    locale['mainData.action'] = "{!! __('app.member.credit.action') !!}";
    locale['mainData.action.deposit'] = "{!! __('app.member.credit.action.deposit') !!}";
    locale['mainData.action.withdraw'] = "{!! __('app.member.credit.action.withdraw') !!}";
    locale['mainData.available'] = "{!! __('app.member.credit.available') !!}";
    locale['tooltip.check'] = "<input type='checkbox' id='checkAll' onclick='checkAll(this);'>";
    locale['error.check'] = "{!! __('app.member.credit.error.check') !!}";
    locale['mainData.agent'] = "{!! __('app.banking.bank.maindata.agent') !!}";
    locale['mainData.name'] = "{!! __('app.banking.bank.maindata.holdername') !!}";
    locale['mainData.bank'] = "{!! __('app.banking.bank.maindata.bankname') !!}";
    locale['mainData.acc_no'] = "{!! __('app.banking.bank.maindata.accno') !!}";
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

    $.ajax({
        type: "GET",
        url: "/ajax/banking/bankinfo/list",
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
                    ["sequence",'Sequence',true,false]
                    ,["bank",locale['mainData.bank'],false,false]                        
                    ,["acc_no",locale['mainData.acc_no'],false,false]
                    ,["name",locale['mainData.name'],false,false]
                    ,["current_threshold",'Current Threshold',true,false]
                    ,["target_threshold",'Target Threshold',true,false]
                    ,["",locale['mainData.action'],false,false]                   
                ];


    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();

        var fieldAmount = utils.getDataTableFieldIdx("amount",fields);
        var fieldTargetThreshold = utils.getDataTableFieldIdx("target_threshold",fields);
        var fieldCurrentThreshold = utils.getDataTableFieldIdx("current_threshold",fields);
        var fieldActions = utils.getDataTableFieldIdx("",fields);
        var member = "";

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            var curentThreshold = mainData.results[i - 1]["current_threshold"];
            var member = mainData.results[i - 1]["id"];
            var targetThreshold = mainData.results[i - 1]["target_threshold"];
           
            row.cells[fieldCurrentThreshold].innerHTML =  utils.formatMoney(curentThreshold);
            row.cells[fieldTargetThreshold].innerHTML =  utils.formatMoney(targetThreshold);

            var btnDebit = document.createElement("BUTTON");
            btnDebit.innerHTML = 'Debit';
            btnDebit.className = "btn btn-sm btn-primary";
            btnDebit.onclick = showModal;
            btnDebit.rowId = i;
            btnDebit.style.margin = "0 15px 0 0";
            btnDebit.value = "1";

            var btnCredit = document.createElement("BUTTON");
            btnCredit.innerHTML = 'Credit';
            btnCredit.className = "btn btn-sm btn-primary";
            btnCredit.onclick = showModal;
            btnCredit.rowId = i;
            btnCredit.style.margin = "0 15px 0 0";
            btnCredit.value = "2";

            var btnTransfer = document.createElement("BUTTON");
            btnTransfer.innerHTML = 'Transfer';
            btnTransfer.className = "btn btn-sm btn-primary";
            btnTransfer.onclick = showModal;
            btnTransfer.rowId = i;
            btnTransfer.style.margin = "0 15px 0 0";
            btnTransfer.value = "3";

            row.cells[fieldActions].innerHTML = '';

            row.cells[fieldActions].appendChild(btnDebit);
            row.cells[fieldActions].appendChild(btnCredit);
            row.cells[fieldActions].appendChild(btnTransfer);

        }
       
    }
}


function showModal()
{

    $('#modal-transfer-amount').val('');
    $('#modal-credit-amount').val('');
    $('#modal-debit-amount').val('');

    var bankName = mainData.results[this.rowId - 1]["bank"];
    var accNo = mainData.results[this.rowId - 1]["acc_no"];
    var holderName = mainData.results[this.rowId - 1]["name"];
    var CurrentThreshold = mainData.results[this.rowId - 1]["current_threshold"];

    $("#modal-bank").val(bankName);
    $("#modal-acc").val(accNo);
    $("#modal-name").val(holderName);
    $("#modal-current-threshold").val(utils.formatMoney(CurrentThreshold,2));
    $("#type").val(this.value);

    $("#title").html('<b>'+'Action'+'</b>');

    $('#modal-transfer-to-bank').append('<option>' + '</option>').children().remove();
    

    for (i = 0; i < mainData.results.length; i++ ) 
    {
        if(mainData.results[i]['acc_no'] != accNo)
        $('#modal-transfer-to-bank').append($('<option />').attr('value', mainData.results[i]['acc_no']).html(mainData.results[i]['acc_no']));

    }

    $("#modal-transfer-to-bankname").html(bankName);
    $("#modal-transfer-to-bankholder").html(holderName);

    $('#modal-transfer-to-bank').on('change', function() {
     
        for (i = 0; i < mainData.results.length; i++ ) 
        {
            if(mainData.results[i]['acc_no'] == this.value)
            {
                $("#modal-transfer-to-bankname").html(mainData.results[i]['bank']);
                $("#modal-transfer-to-bankholder").html(mainData.results[i]['name']);                
            }
        }

    });


    $("#modalAction").modal('show');

    if(this.value == '1')
    {
        $("#debit-form").show();
        $("#credit-form").hide();
        $("#transfer-form").hide();
        $("#transfer-form-to-bank").hide();
        $("#transfer-form-to-bankdetail").hide();
        
    }
    else if(this.value == '2')
    {
        $("#debit-form").hide();
        $("#credit-form").show();
        $("#transfer-form").hide();
        $("#modal-transfer-to-bank").hide();
        $("#transfer-form-to-bank").hide();
        $("#transfer-form-to-bankdetail").hide();

    }
    else if(this.value == '3')
    {
        $("#debit-form").hide();
        $("#credit-form").hide();
        $("#transfer-form").show();
        $("#modal-transfer-to-bank").show();
        $("#transfer-form-to-bank").show();
        $("#transfer-form-to-bankdetail").show();      
    }

}



function submitAction()
{ 
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmit","modalAction");

    $("#formAction").attr("enabled",0);

    $.ajax({
        url: "/ajax/banking/bankinfo/credit/transfer",
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
    var input = $("#modal-debit-amount, #modal-credit-amount, #modal-transfer-amount");

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

    filterMainData();
}
</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">Banking</li>
    <li class="breadcrumb-item active">Bank Statement Transfer</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

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
                            
                            <input type="hidden" name="type" id="type">

                            <div class="card-body">

                                <div class="form-group row">

                                    <div class="col-sm-6">

                                        <label>{{ __('app.banking.bank.modal.bankname') }}</label>

                                    </div>

                                    <div class="col-sm-6">

                                        <input type="text" id="modal-bank" name="bank_name" class="form-control" readonly="">

                                    </div>
                                </div>

                               <div class="form-group row">

                                    <div class="col-sm-6">

                                        <label>{{ __('app.banking.bank.modal.accno') }}</label>

                                    </div>

                                    <div class="col-sm-6">

                                        <input type="text" id="modal-acc" name="acc_no" class="form-control" readonly="">

                                    </div>
                                </div>

                                <div class="form-group row">

                                    <div class="col-sm-6">

                                        <label>{{ __('app.banking.bank.modal.holdername') }}</label>

                                    </div>

                                    <div class="col-sm-6">

                                        <input type="text" id="modal-name" name="holder_name" class="form-control"  readonly="">
                                        <b style="float: right;" id="available"></b>
                                    </div>
                                </div>

                                <div class="form-group row">

                                    <div class="col-sm-6">

                                        <label>Current Threshold</label>

                                    </div>

                                    <div class="col-sm-6">

                                        <input type="text" id="modal-current-threshold" name="current_threshold" class="form-control" readonly="">
                                    </div>
                                </div>
                                <div class="form-group row" id="debit-form">

                                    <div class="col-sm-6">

                                        <label>Debit Amount</label>

                                    </div>

                                    <div class="col-sm-6">

                                        <input type="text" id="modal-debit-amount" name="debit_amount" class="form-control">
                                    </div>
                                </div>

                                <div class="form-group row" id="credit-form">

                                    <div class="col-sm-6">

                                        <label>Credit Amount</label>

                                    </div>

                                    <div class="col-sm-6">

                                        <input type="text" id="modal-credit-amount" name="credit_amount" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row" id="transfer-form">

                                    <div class="col-sm-6">

                                        <label>Transfer Amount</label>

                                    </div>

                                    <div class="col-sm-6">

                                        <input type="text" id="modal-transfer-amount" name="transfer_amount" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row" id="transfer-form-to-bank">

                                    <div class="col-sm-6">

                                        <label>Transfer To Bank Account</label>

                                    </div>

                                    <div class="col-sm-6">
                                        <select class="form-control" id="modal-transfer-to-bank" name="to_bank">
                                        </select>
                                    </div>

                                </div>
                                <div class="form-group row" id="transfer-form-to-bankdetail">

                                    <div class="col-sm-6">

                                        <label>Transfer To Bank Name</label>

                                    </div>

                                    <div class="col-sm-6">
                                        <label id="modal-transfer-to-bankname"></label>
                                    </div>
                                    <div class="col-sm-6">

                                        <label>Transfer To Bank Account Holder</label>

                                    </div>

                                    <div class="col-sm-6">
                                        <label id="modal-transfer-to-bankholder"></label>
                                    </div>
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
