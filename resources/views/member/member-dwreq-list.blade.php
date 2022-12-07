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

    getBankList();

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
    locale['mainData.admin_username'] = "{!! __('app.banking.dw.maindata.agent') !!}";
    locale['mainData.id'] = "{!! __('app.banking.dw.maindata.id') !!}";
    locale['mainData.username'] = "{!! __('app.banking.dw.maindata.member') !!}";
    locale['mainData.type'] = "{!! __('app.banking.dw.maindata.type') !!}";
    locale['mainData.amount'] = "{!! __('app.banking.dw.maindata.amount') !!}";
    locale['mainData.status'] = "{!! __('app.banking.dw.maindata.status') !!}";
    locale['mainData.payment_type'] = "{!! __('app.banking.dw.maindata.payment_type') !!}";
    locale['mainData.ref_id'] = "{!! __('app.banking.dw.maindata.ref_id') !!}";
    locale['mainData.bank'] = "{!! __('app.banking.dw.maindata.bank_name') !!}";
    locale['mainData.member_name'] = "{!! __('app.banking.dw.maindata.member_name') !!}";
    locale['mainData.member_bank_acc'] = "{!! __('app.banking.dw.maindata.member_bank_acc') !!}";
    locale['mainData.datetime'] = "{!! __('app.banking.dw.maindata.dw_date') !!}";
    locale['mainData.created_at'] = "{!! __('app.banking.dw.maindata.created_at') !!}";
    locale['mainData.updated_at'] = "{!! __('app.banking.dw.maindata.updated_at') !!}";
    locale['mainData.action'] = "{!! __('app.banking.dw.maindata.actions') !!}";

    locale['mainData.duplicate_ip'] = "Duplicate IP";
    locale['mainData.duplicate_bank'] = "Duplicate Bank";
    locale['mainData.promo_name'] = "Promo Name";

    locale['mainData.deposit'] = "Deposit Detail";
    locale['mainData.withdraw'] = "Withdraw Detail";


    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
}

var refreshMainData = false;
var mainData;
var mainDataTotal;
var bankList;

function getBankList() 
{

    var data = [];

    $.ajax({
        type: "GET",
        url: "/ajax/banking/bankinfo/list",
        data: data,
        success: function(data) 
        {
            if(data.length > 0)
            {
                bankList = JSON.parse(data);
            }
            else
            {
                bankList = [];
            }

            console.log(bankList);
        }
    });
}


function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();

    var data = utils.getDataTableDetails(containerId);

    data["member_name"] = $("#f_member").val();
    data["status"] = $("#f_status").val();
    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();
    data["type"] = $("#f_type").val();

    $.ajax({
        type: "GET",
        url: "/ajax/member/dwreq/list",
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
                    ["id",locale['mainData.id'],true,false]
                    ,["username",locale['mainData.username'],true,false]                        
                    ,["type_text",locale['mainData.type'],false,false]
                    ,["amount",locale['mainData.amount'],true,true]
                    ,["payment_type_text",locale['mainData.payment_type'],false,false]
                    ,["ref_id",locale['mainData.ref_id'],false,false]
                    ,["dw_date",locale['mainData.datetime'],false,false]
                    ,["bank",locale['mainData.bank'],false,false]
                    ,["member_name",locale['mainData.member_name'],false,false]
                    ,["member_bank_acc",locale['mainData.member_bank_acc'],false,false]
                    ,["is_duplicate_ip",locale['mainData.duplicate_ip'],false,false]
                    ,["is_duplicate_bank",locale['mainData.duplicate_bank'],false,false]
                    ,["status_text",locale['mainData.status'],false,false]
                    ,["promo_name",locale['mainData.promo_name'],false,false]
                    ,["pymt_gateway_status_text",'Payment Gateway Status',false,false]
                    ,["created_at",locale['mainData.created_at'],true,false]
                    ,["updated_at",locale['mainData.updated_at'],true,false]
                    /*,["",locale['mainData.action'],false,false]   */                
                ];

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();


        var fieldAmt = utils.getDataTableFieldIdx("amount",fields);
        var fieldRefId = utils.getDataTableFieldIdx("ref_id",fields);
        var fieldBankName = utils.getDataTableFieldIdx("bank",fields);
        var fieldMemberName = utils.getDataTableFieldIdx("member_name",fields);
        var fieldMemberBankAcc = utils.getDataTableFieldIdx("member_bank_acc",fields);
        var fieldDWDate = utils.getDataTableFieldIdx("dw_date",fields);
        var fieldpaymentGatewayStatus = utils.getDataTableFieldIdx("pymt_gateway_status_text",fields);
        var fieldId = utils.getDataTableFieldIdx("id",fields);
        var fieldDuplicateIp = utils.getDataTableFieldIdx("is_duplicate_ip", fields);
        var fieldDuplicateBank = utils.getDataTableFieldIdx("is_duplicate_bank", fields);

/*        if(auth.getUserLevel() == 3 || auth.getUserLevel() == 1  || auth.getUserLevel() == 0)
        {
            var fieldActions = utils.getDataTableFieldIdx("",fields);
        }*/

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   

            row.cells[fieldAmt].innerHTML =  utils.formatMoney(mainData.results[i - 1]["amount"]);
            
            var status = mainData.results[i - 1]["status"];
            var paymentGatewayStatus = mainData.results[i - 1]["payment_gateway_status"];
            var refId = mainData.results[i - 1]["ref_id"];
            var bankName = mainData.results[i - 1]["bank"];
            var memberName = mainData.results[i - 1]["member_name"];
            var memberBankAcc = mainData.results[i - 1]["member_bank_acc"];
            var dwDate = mainData.results[i - 1]["dw_date"];
            var paymentType = mainData.results[i - 1]["payment_type"];
            var type = mainData.results[i - 1]["type"];
            var id = mainData.results[i - 1]["id"];

            var a = document.createElement("a");
            a.href = "#";
            $(a).attr("onclick","showDetailsModal("+i+")");
            a.innerHTML = id;
            row.cells[fieldId].innerHTML =  "";
            row.cells[fieldId].appendChild(a);



            if (mainData.results[i-1]["is_duplicate_ip"] == '0') 
            {
                row.cells[fieldDuplicateIp].innerHTML = '<span class="badge badge-success">'+mainData.results[i-1]["duplicate_ip_desc"]+'</span>';
            }
            else
            {
                row.cells[fieldDuplicateIp].innerHTML = '<span class="badge badge-warning">'+mainData.results[i-1]["duplicate_ip_desc"]+'</span>';
            }

            if (mainData.results[i-1]["is_duplicate_bank"] == '0') 
            {
                row.cells[fieldDuplicateBank].innerHTML = '<span class="badge badge-success">'+mainData.results[i-1]["duplicate_bank_desc"]+'</span>';
            }
            else
            {
                row.cells[fieldDuplicateBank].innerHTML = '<span class="badge badge-warning">'+mainData.results[i-1]["duplicate_bank_desc"]+'</span>';
            }




            if(refId == null) 
            {
                row.cells[fieldRefId].innerHTML = '-';
            }

            if(paymentGatewayStatus == null)
            {
                row.cells[fieldpaymentGatewayStatus].innerHTML = '-';
            }

            if(dwDate == null) 
            {
                row.cells[fieldDWDate].innerHTML = '-';
            }

            if(bankName == null) 
            {
                row.cells[fieldBankName].innerHTML = '-';
            }

            if(memberName == null) 
            {
                row.cells[fieldMemberName].innerHTML = '-';
            }

            if(memberBankAcc == null) 
            {
                row.cells[fieldMemberBankAcc].innerHTML = '-';
            }


/*            if(auth.getUserLevel() == 3 || auth.getUserLevel() == 1 || auth.getUserLevel() == 0)
            {


                if(status != 'n')
                {
                    row.cells[fieldActions].innerHTML = '';

                }
                else
                {

                    if (auth.getUserLevel() == 3 || auth.getUserLevel() == 0) 
                    {
                        if (paymentType != 'x') 
                        {
                            var displayButton = true;
                        }
                        else
                        {
                            var displayButton = false;
                        }
                    }

                    if (auth.getUserLevel() == 1) 
                    {   


                        if (paymentType == 'x') 
                        {
                            var displayButton = true;
                        }
                        else
                        {
                            var displayButton = false;
                        }
                    }



                    if (displayButton) 
                    {
                        var btnApprove = document.createElement("BUTTON");
                        btnApprove.innerHTML = "{!! __('app.banking.dw.actions.approve') !!}";
                        btnApprove.className = "btn btn-sm btn-primary";
                        btnApprove.onclick = doApprove;
                        btnApprove.rowId = i;
                        btnApprove.style.margin = "0 15px 0 0";
                        btnApprove.value = "1";

                        var btnReject = document.createElement("BUTTON");
                        btnReject.innerHTML = "{!! __('app.banking.dw.actions.reject') !!}";
                        btnReject.className = "btn btn-sm btn-primary";
                        btnReject.onclick = doReject;
                        btnReject.rowId = i;
                        btnReject.style.margin = "0 15px 0 0";
                        btnReject.value = "2";

                        row.cells[fieldActions].innerHTML = '';
                        row.cells[fieldActions].appendChild(btnApprove);
                        row.cells[fieldActions].appendChild(btnReject);
                    }
                    else
                    {
                        row.cells[fieldActions].innerHTML = '';
                    }
                }
            }*/
        }

        var sumFields = [      
            "amount"
      
        ]; 


        utils.createSumForDataTable(table,mainData,mainDataTotal,fields,sumFields);

        for (var j = 0, row; row = table.tFoot.rows[j]; j++) 
        {
            var totalAmount = parseFloat(row.cells[fieldAmt].innerHTML);

            row.cells[fieldAmt].innerHTML = "<b>" + utils.formatMoney(totalAmount) + "</b>";

        }

    }
}

function showDetailsModal(rowId)
{


    //remove modal footer when modal-footer is created before
    if(document.getElementById('modal-footer') != null)
        document.getElementById('modal-footer').remove();

    var txnId = mainData.results[rowId - 1]["id"];
    var status = mainData.results[rowId - 1]["status_text"];
    var requestDate = mainData.results[rowId - 1]["created_at"];
    var comfirmDate = mainData.results[rowId - 1]["updated_at"];
    var reqId = mainData.results[rowId - 1]["id"];
    var amount = mainData.results[rowId - 1]["amount"];
    var adminBank = mainData.results[rowId - 1]["admin_bank"];
    var adminAccName = mainData.results[rowId - 1]["admin_acc_name"];
    var adminAccNo = mainData.results[rowId - 1]["admin_acc_no"];
    var comfirmBy = mainData.results[rowId - 1]["confirm_by"];
    var paymentType = mainData.results[rowId - 1]["payment_type"];
    var paymentTypeText = mainData.results[rowId - 1]["payment_type_text"];
    var promoName = mainData.results[rowId -1 ]["promo_name"];
    var adminBankId = mainData.results[rowId - 1]["admin_bank_id"];
    var isDuplicateIpDesc = mainData.results[rowId - 1]["duplicate_ip_desc"];
    var isDuplicateBankDesc = mainData.results[rowId - 1]["duplicate_bank_desc"];
    var type = mainData.results[rowId - 1]["type"];

    var memberId = mainData.results[rowId - 1]["member_id"];

    var remark = mainData.results[rowId - 1]["remark"];


    var userBank = mainData.results[rowId - 1]["bank"];
    var userAccName = mainData.results[rowId - 1]["member_name"];
    var userAccNo = mainData.results[rowId - 1]["member_bank_acc"];


    if(type == 'd')
    {
        $("#title").html('<b>'+locale['mainData.deposit']+'</b>');
        
    }
    else if(type == 'w')
    {
        $("#title").html('<b>'+locale['mainData.withdraw']+'</b>');
    }



    //hidden input
    $("#req_id").val(reqId);
    $("#date").val(requestDate);
    $("#confirm_date").val(comfirmDate);
    $("#confirm_by").val(comfirmBy);
    $("#amount").val(amount);
    $("#status").val(status);
    $("#promo_name").val(promoName);

    $("#duplicate_ip").val(isDuplicateIpDesc);
    $("#duplicate_bank").val(isDuplicateBankDesc);
    $("#payment_type").val(paymentTypeText);

    $("#bank-form").show();


    $("#remark").val(remark);

    if(mainData.results[rowId - 1]['status'] != 'n')
    {
        $("#remark").prop('disabled', true);

    }
    else
    {
        $("#remark").prop('disabled', false);
    }    

    if(type == 'd')
    {
        $("#bank-header").html('Admin Bank Details');


        if(paymentType == 'b')
        {

            $("#receipt-form").show();

            $("#admin_bank").show();
            $("#admin_acc_name").show();
            $("#admin_acc_no").show();
        }
        else
        {
            $("#receipt-form").hide();

            $("#admin_bank").hide();
            $("#admin_acc_name").hide();
            $("#admin_acc_no").hide();     
            
            $("#bank-form").hide();    
        }

        $("#user_bank").hide();
        $("#user_acc_name").hide();
        $("#user_acc_no").hide();

        $("#admin_bank").val(adminBank);
        $("#admin_acc_name").val(adminAccName);

        $('#admin_acc_no').append('<option>' + '</option>').children().remove();
        

        for (i = 0; i < bankList.results.length; i++ ) 
        {
            $('#admin_acc_no').append($('<option />').attr('value', bankList.results[i]['acc_no']).html(bankList.results[i]['acc_no']));
        
        }

        $('#admin_bank_id').val(adminBankId);
        $("#admin_acc_no").val(adminAccNo);
        $("#admin_bank").val(adminBank);
        $("#admin_acc_name").val(adminAccName);

        $('#admin_acc_no').on('change', function() {
            
            for (i = 0; i < bankList.results.length; i++ ) 
            {
                if(bankList.results[i]['acc_no'] == this.value)
                {
                    $("#admin_bank").val(bankList.results[i]['bank']);
                    $("#admin_acc_name").val(bankList.results[i]['name']);
                    $('#admin_bank_id').val(bankList.results[i]['info_id']);                
                }
            }

        });


        document.getElementById("image").src = mainData.results[rowId - 1]["image"];
        document.getElementById("image").style.cursor = "pointer";
        
        //add onclick image
        document.getElementById("image").onclick = () => {
            var modal = document.getElementById("ModalReceipt");

            var img = document.getElementById("myImg");
            var modalImg = document.getElementById("img01");
            var captionText = document.getElementById("caption");
            
            modal.style.display = "flex";
            modalImg.src = mainData.results[rowId - 1]["image"];

            // Get the <span> element that closes the modal
            var span = document.getElementsByClassName("close-receipt")[0];

            // When the user clicks on <span> (x), close the modal
            span.onclick = function() { 
              modal.style.display = "none";
            }
        } 

    }   
    else
    {
        $("#bank-header").html('Member Bank Details');

        $("#receipt-form").hide();

        $("#admin_bank").hide();
        $("#admin_acc_name").hide();
        $("#admin_acc_no").hide();

        $("#user_bank").show();
        $("#user_acc_name").show();
        $("#user_acc_no").show();

        $("#user_bank").val(userBank);
        $("#user_acc_name").val(userAccName);
        $("#user_acc_no").val(userAccNo);

    }


    $("#modalDetails").modal('show');


    if(mainData.results[rowId - 1]["status"] == 'n' && paymentType == 'b')
    {
        var modalCard = document.getElementById("modalCard");

        var modalFooter = document.createElement("div");
        modalFooter.id = "modal-footer";
        modalFooter.className = "card-footer";

        var btnApprove = document.createElement("BUTTON");
        btnApprove.innerHTML = "{!! __('app.banking.dw.actions.approve') !!}";
        btnApprove.className = "btn btn-primary btn-ladda";
        btnApprove.onclick = doApprove;
        btnApprove.rowId = rowId;
        btnApprove.style.margin = "0 15px 0 0";
        btnApprove.value = "1";

        var btnReject = document.createElement("BUTTON");
        btnReject.innerHTML = "{!! __('app.banking.dw.actions.reject') !!}";
        btnReject.className = "btn btn-primary btn-ladda";
        btnReject.onclick = doReject;
        btnReject.rowId = i;
        btnReject.style.margin = "0 15px 0 0";
        btnReject.value = "2";

        
        modalCard.appendChild(modalFooter);
        modalFooter.appendChild(btnApprove);
        modalFooter.appendChild(btnReject);
    }
}

    
function getWalletBalance(prdId,memberId)
{

    $("#get-prd-"+prdId).addClass("fa-spin");


    $.ajax({
        url: '/ajax/member/wallet/balance',
        type: 'GET',
        data: {prd_id:prdId,member_id:memberId},
        success:function(data)
        {
            $("#balance-"+prdId).html(utils.formatMoney(data,2));
            $("#get-prd-"+prdId).removeClass("fa-spin");

        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
        } 
    });


}


function hideReceipt()
{
    var modal = document.getElementById("ModalReceipt");
    modal.style.display = "none";
}


function doApprove()
{
    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();
    
    var txnId = mainData.results[this.rowId - 1]["id"];
    
    var adminBankId = $('#admin_bank_id').val();

    var remark = $('#remark').val();
    
    if(adminBankId == '')
        adminBankId = mainData.results[this.rowId - 1]["admin_bank_id"];

    $.ajax({
        url: "/ajax/member/dwreq/approve",
        type: "POST",
        data: {id:txnId,admin_bank_id:adminBankId,remark:remark},
        success: function(data)
        {
            // console.log(data);
            
            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onModalDismissError);
            }
        },
        error: function(){}             
    }); 
}

function doReject()
{
    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();
    
    var txnId = mainData.results[this.rowId - 1]["id"];

    var remark = $('#remark').val();

    $.ajax({
        url: "/ajax/member/dwreq/reject",
        type: "POST",
        data: {id:txnId,remark:remark},
        success: function(data)
        {
            // console.log(data);
            
            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);
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
    $("#modalDetails").modal('hide');
    $("#modalDetails-Withdraw").modal('hide');

    if(refreshMainData)
    {
        refreshMainData = false;

        getMainData();
    }
}

function onModalDismissError()
{
    $("#main-spinner").hide();
    $("#main-table").show();
    $("#notes").show();
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

    $("#f_member").val("");
    $("#f_status").val("n");
    $("#f_type").val("");
    $("#e_date, #e_date1").val("");
    $("#s_date, #s_date1").val("");

    filterMainData();
}
</script>

<style>

    .heading 
    {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .modal-receipt 
    {
        display: none; 
        align-items: center;
        background-color: rgba(12,13,13,.8);
        height: 100%;
        justify-content: center;
        left: 0;
        overflow: hidden;
        position: fixed;
        top: 0;
        transition: all .25s ease-in;
        width: 100%;
        z-index: 5000;
    }


    /* The Close Button */
    .close-receipt 
    {
      position: absolute;
      right: 35px;
      color: #f1f1f1;
      font-size: 40px;
      font-weight: bold;
      transition: 0.3s;
      top: 0px;
    }

    .close-receipt:hover,
    .close-receipt:focus 
    {
      color: #bbb;
      text-decoration: none;
      cursor: pointer;
    }

    @media (min-width: 1200px) {
        .modal-lg-dw {
            max-width: 1080px; 
        } 
    }

    .fa-refresh
    {
        cursor:pointer;font-size:15px;
    }


</style>


@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.banking.dw.breadcrumbs.banking') }}</li>
    <li class="breadcrumb-item active">{{ __('app.banking.dw.breadcrumbs.dwrequest') }}</li>
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
                                <label for="member">{{ __('app.banking.dw.filter.member') }}</label>
                                <input type="text" class="form-control" id="f_member" placeholder="" autocomplete="">
                            </div>

                        </div>
                        <div class="col-sm-2">

                            <div class="form-group">
                                <label for="status">{{ __('app.banking.dw.filter.status') }}</label>
                                <select class="form-control form-control-sm" id="f_status" style="height: 34.8px">
                                    <option value="">{{ __('app.banking.dw.filter.status.all') }}</option>
                                    <option value="n" selected>{{ __('app.banking.dw.filter.status.new') }}</option>
                                    <option value="a">{{ __('app.banking.dw.filter.status.approved') }}</option>
                                    <option value="p">{{ __('app.banking.dw.filter.status.processing') }}</option>
                                    <option value="r">{{ __('app.banking.dw.filter.status.rejected') }}</option>
                                    <option value="c">{{ __('app.banking.dw.filter.status.cancelled') }}</option>
                                </select>
                            </div>

                        </div>

                        <div class="col-sm-2">
                             <label for="type">{{ __('app.banking.dw.filter.type') }}</label>
                                <select class="form-control form-control-sm" id="f_type" style="height: 34.8px">
                                    <option value="" selected>{{ __('app.banking.dw.filter.type.all') }}</option>
                                    <option value="d">{{ __('app.banking.dw.filter.type.deposit') }}</option>
                                    <option value="w">{{ __('app.banking.dw.filter.type.withdraw') }}</option>
                                </select>

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
    <div class="modal-dialog modal-primary modal-lg modal-lg-dw" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="title">Deposit Details</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>            
            </div>
            <div class="modal-body">
                <div class="card" id="modalCard">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="heading"> 
                                    Transaction Details
                                </div>

                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Request Id</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="req_id" class="form-control" readonly="readonly">
                                    </div>

                                </div>


                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Deposit Amount</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="amount" class="form-control" readonly="readonly">
                                    </div>

                                </div>

                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Payment Type</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="payment_type" class="form-control" readonly="readonly">
                                    </div>

                                </div>


                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Deposit Date</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="date" class="form-control" readonly="readonly">
                                    </div>

                                </div>

                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Confirmed On</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="confirm_date" class="form-control" readonly="readonly">
                                    </div>

                                </div>

                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Confirmed By</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="confirm_by" class="form-control" readonly="readonly">
                                    </div>

                                </div>


                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Status</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="status" class="form-control" readonly="readonly">
                                    </div>

                                </div>


                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Promotion Name</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="promo_name" class="form-control" readonly="readonly">
                                    </div>

                                </div>


                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Duplicate Ip</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="duplicate_ip" class="form-control" readonly="readonly">
                                    </div>

                                </div>

                                <div class="form-group row">

                                    <div class="col-sm-4">

                                        <label>Duplicate Bank</label>
                                    </div>

                                    <div class="col-sm-8">
                                        <input type='text' id="duplicate_bank" class="form-control" readonly="readonly">
                                    </div>

                                </div>

                                <div id="bank-form">
                                    <div class="heading" id="bank-header"> 
                                        Bank Details
                                    </div>

                                    <div class="form-group row">

                                        <div class="col-sm-4">

                                            <label>Bank Name</label>
                                        </div>

                                        <div class="col-sm-8">
                                            <input type='text' id="admin_bank" class="form-control" readonly="readonly">
                                            <input type='text' id="user_bank" class="form-control" readonly="readonly">
                                        </div>

                                    </div>


                                    <div class="form-group row">

                                        <div class="col-sm-4">

                                            <label>Bank Account Name</label>
                                        </div>

                                        <div class="col-sm-8">
                                            <input type='text' id="admin_acc_name" class="form-control" readonly="readonly">
                                            <input type='text' id="user_acc_name" class="form-control" readonly="readonly">
                                        </div>

                                    </div>

                                    <div class="form-group row">

                                        <div class="col-sm-4">

                                            <label>Bank Account No.</label>
                                        </div>

                                        <div class="col-sm-8">
                                            <select class="form-control" id="admin_acc_no">
                                            </select>
                                            <input type="hidden" id="admin_bank_id">

                                            <input type='text' id="user_acc_no" class="form-control" readonly="readonly">
                                        </div>

                                    </div>
                                </div>

                            </div>
                            <div class="col-sm-6"  id="receipt-form">
                                <div class="heading"> 
                                    <br/>
                                </div>
                                 <div class="form-group row">

                                    <div class="col-sm-2">

                                        <label>Receipt</label>
                                    </div>

                                    <div class="col-sm-12">
                                        <img id="image" width="100%" height="100%">
                                    </div>

                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="heading"> 
                                    <br/>
                                </div>
                                 <div class="form-group row">

                                    <div class="col-sm-2">

                                        <label>Remark</label>
                                    </div>

                                    <div class="col-sm-12">
                                        <input type='text' id="remark" class="form-control">
                                    </div>

                                </div>
                            </div>




                        </div>
                    </div>
                </div>
            </div>        
        </div>
    </div>
</div>




<div id="ModalReceipt" class="modal-receipt" onclick="hideReceipt();">
  <span class="close-receipt">&times;</span>
  <img  id="img01" style="height:100%;">
  <div id="caption"></div>
</div>

@endsection
