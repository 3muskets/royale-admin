@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

    checkAmount();

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

});

function prepareLocale()
{
    locale['mainData.agent'] = "{!! __('app.banking.bank.maindata.agent') !!}";
    locale['mainData.name'] = "{!! __('app.banking.bank.maindata.holdername') !!}";
    locale['mainData.bank'] = "{!! __('app.banking.bank.maindata.bankname') !!}";
    locale['mainData.acc_no'] = "{!! __('app.banking.bank.maindata.accno') !!}";
    locale['mainData.status'] = "{!! __('app.banking.bank.maindata.status') !!}";
    locale['mainData.action'] = "{!! __('app.banking.bank.maindata.action') !!}";
    locale['tooltip.edit'] = "{!! __('app.banking.bank.maindata.edit') !!}";

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

    $.ajax({
        type: "GET",
        url: "/ajax/banking/bankinfo/list",
        data: data,
        success: function(data) 
        {
            // console.log(data);
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
                    ["bank_id",'Bank ID',false,false] 
                    ,["bank",locale['mainData.bank'],false,false]                        
                    ,["acc_no",locale['mainData.acc_no'],false,false]
                    ,["name",locale['mainData.name'],false,false]
                    ,["min_deposit_amt",'Min Deposit Amount',true,false]
                    ,["max_deposit_amt",'Max Deposit Amount',true,false]
                    ,["status",locale['mainData.status'],false,false]
                    ,["suspended",locale['mainData.status'],false,false]
                    ,["created_at",'Created At',false,false]
                    ,["updated_at",'Updated At',false,false]
                    ,["",locale['mainData.action'],false,false]                   
                ];

    if(auth.getUserLevel() > 0)
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

        if(auth.getUserLevel() == 0)
        {
            var fieldActions = utils.getDataTableFieldIdx("",fields);
        }

        var fieldStatus = utils.getDataTableFieldIdx("status",fields);

        var fieldMinAmt = utils.getDataTableFieldIdx("min_deposit_amt",fields);
        var fieldMaxAmt = utils.getDataTableFieldIdx("max_deposit_amt",fields);
        var fieldSuspended = utils.getDataTableFieldIdx("suspended", fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
             //status
            if(mainData.results[i - 1]["status"] == "a")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+mainData.results[i - 1]["status_desc"] +'</span>';
            else 
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+mainData.results[i - 1]["status_desc"] +'</span>';



            if (mainData.results[i - 1]["suspended"] == '0') 
            {
                row.cells[fieldSuspended].innerHTML = '<span class="badge badge-success">'+mainData.results[i - 1]["suspended_desc"]+'</span>';
            }
            else
            {
                row.cells[fieldSuspended].innerHTML = '<span class="badge badge-warning">'+mainData.results[i - 1]["suspended_desc"]+'</span>';
            }

            row.cells[fieldMinAmt].innerHTML = utils.formatMoney(mainData.results[i - 1]["min_deposit_amt"],2);
            row.cells[fieldMaxAmt].innerHTML = utils.formatMoney(mainData.results[i - 1]["max_deposit_amt"],2);

            if(auth.getUserLevel() == 0)
            {
                row.cells[fieldActions].innerHTML = '';

                var btnEdit = document.createElement("i");
                btnEdit.className = "fa fa-edit fa-2x";
                btnEdit.onclick = showModal;
                btnEdit.rowId = i;
                btnEdit.value = 1;
                btnEdit.style.cursor = "pointer";
                btnEdit.style.color = "#11acf4";
                btnEdit.setAttribute("data-toggle", "tooltip");
                btnEdit.setAttribute("title", locale['tooltip.edit']);
                row.cells[fieldActions].innerHTML = "";
                row.cells[fieldActions].appendChild(btnEdit);
                row.cells[fieldActions].className = "pb-0";               
            }
        }
    }
}

function showModal()
{

    $("#modal-id").val('');
    $("#modal-acc").val('');
    $("#modal-name").val('');

    $("#modal-min-amt").val('');
    $("#modal-max-amt").val('');
    $("#modal-sequence").val('');
    $("#modal-traget-threshold").val('');


    if(this.value == 1)
    {
        var infoId = mainData.results[this.rowId - 1]["info_id"];
        var bankName = mainData.results[this.rowId - 1]["bank"];
        var bankId = mainData.results[this.rowId - 1]["bank_id"];
        var accNo = mainData.results[this.rowId - 1]["acc_no"];
        var holderName = mainData.results[this.rowId - 1]["name"];
        var status = mainData.results[this.rowId - 1]["status"];
        var suspended = mainData.results[this.rowId - 1]["suspended"];

        var sequence = mainData.results[this.rowId - 1]["sequence"];
        var tartgetThreshold = mainData.results[this.rowId - 1]["target_threshold"];
        var minDepositAmt = mainData.results[this.rowId - 1]["min_deposit_amt"];
        var maxDepositAmt = mainData.results[this.rowId - 1]["max_deposit_amt"];


        $("#modal-id").val(infoId);
        $("#modal-bank").val(bankId);
        $("#modal-acc").val(accNo);
        $("#modal-name").val(holderName);

        $("#modal-min-amt").val(utils.formatMoney(minDepositAmt,2));
        $("#modal-max-amt").val(utils.formatMoney(maxDepositAmt,2));
        $("#modal-sequence").val(sequence);
        $("#modal-traget-threshold").val(utils.formatMoney(tartgetThreshold,2));        

        $('#status').append('<option>' + '</option>').children().remove();
        $('#status').append('{{ Helper::generateOptions($optionsStatus,'') }}');

        $('#suspended').append('<option>' + '</option>').children().remove();
        $('#suspended').append('{{ Helper::generateOptions($optionsSuspended,'') }}');


        document.getElementById("status").value = status;
        document.getElementById("suspended").value = suspended;
    }


    $("#modalMessage").hide();
    $("#modalAction").modal('show');

    refreshMainData = false;    
}

function checkAmount()
{
    var input = $("form").find("#modal-min-amt,#modal-max-amt,#modal-sequence,#modal-traget-threshold");

    utils.formatCurrencyInputWithoutDecimal(input);
}


function submitAction()
{ 
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmit","modalAction");

    $("#formAction").attr("enabled",0);

    $.ajax({
        url: "/ajax/banking/bankinfo/update",
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

function onModalDismiss() 
{
    window.location.href = "/banking/bankinfo";
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
    filterMainData();
}
</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.banking.bank.breadcrumbs.banking') }}</li>
    <li class="breadcrumb-item active">{{ __('app.banking.bank.breadcrumbs.bankinfo') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">
            <div class="card-body">
                <button type="button" onclick="showModal()">New Bank Account</button>
            </div>

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
                <h4 class="modal-title">Bank Account</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formAction">
                        
                        <input type="hidden" id="modal-id" name="id">

                        <div class="card-body">

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.banking.bank.modal.bankname') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <select id="modal-bank" name="bank_name_id" class="form-control">
                                        {{ Helper::generateOptions($optionsBankList,'') }}
                                    </select>


                                </div>
                            </div>

                           <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.banking.bank.modal.accno') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" id="modal-acc" name="acc_no" class="form-control">

                                </div>
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>{{ __('app.banking.bank.modal.holdername') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" id="modal-name" name="holder_name" class="form-control">
                                </div>
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>Min Deposit Amount</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" id="modal-min-amt" name="min_amt" class="form-control">
                                </div>
                            </div>

                            <div class="form-group row">

                                <div class="col-sm-4">

                                    <label>Min Deposit Amount</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" id="modal-max-amt" name="max_amt" class="form-control">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4">

                                    <label>{{ __('app.banking.bank.modal.status') }}</label>
                                </div>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        {{ Helper::generateOptions($optionsStatus,'') }}
                                    </select>
                                </div>

                            </div>
                   
                            <div class="form-group row">
                                <div class="col-sm-4">

                                    <label>Suspended</label>
                                </div>
                                <div class="col-sm-8">
                                    <select class="form-control" id="suspended" name="suspended">
                                        {{ Helper::generateOptions($optionsSuspended,'') }}
                                    </select>
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
