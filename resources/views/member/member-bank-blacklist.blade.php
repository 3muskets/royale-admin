@extends('layouts.app')

@section('head')
<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

    var date = utils.getToday();


    utils.createSpinner("main-spinner");

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

});

function prepareLocale()
{   
    locale['agent'] = "{!! __('app.reports.winloss.maindata.admin') !!}"; 
    locale['username'] = "{!! __('app.reports.winloss.maindata.username') !!}";
    locale['win_loss'] = "{!! __('app.reports.winloss.maindata.winloss.amt') !!}"
    locale['total_wager'] = "{!! __('app.reports.winloss.maindata.totalwager') !!}";
    locale['turnover'] = "{!! __('app.reports.winloss.maindata.turnover') !!}";
    locale['sma_pt_amt'] = "{!! __('app.reports.winloss.maindata.sma.pt.amt') !!}";
    locale['ag_comm_amt'] = "{!! __('app.reports.winloss.maindata.ag.comm.amt') !!}";
    locale['ttl_deposit'] = "{!! __('app.merchants.member.details.maindata.deposit') !!}";
    locale['ttl_withdraw'] = "{!! __('app.merchants.member.details.maindata.withdraw') !!}";

    locale['mainData.bank'] = "Bank";
    locale['mainData.bankuser'] = "Bank Username";
    locale['mainData.accno'] = "Account Number";

    locale['mainData.edit'] = "Edit";
    locale['mainData.delete'] = "Delete";

    locale['tooltip.edit'] = "Edit";
    locale['tooltip.delete'] = "Delete";
    
    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";


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
    
    var memberId = utils.getParameterByName("member_id");


    data['member_id'] = memberId;

    $.ajax({
        type: "GET",
        url: "/ajax/member/bank/blacklist",
        data: data,
        success: function(data) 
        {

            mainData = JSON.parse(data);
            
            loadMainData(containerId);
        }
    });
}

function loadMainData(containerId)
{ 
    $("#main-spinner").hide();
    $("#main-table").show();

    var fields = [
                ["id", 'ID',false,false] 
                ,["acc_no",'Bank Account No' ,true,true]
                @can('permissions.edit_member_blacklist_bank') 
                ,["delete",locale['mainData.delete'],false,false]
                @endcan
            ];

    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {

        var fieldDelete = utils.getDataTableFieldIdx("delete", fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            @can('permissions.edit_member_blacklist_bank')
            //delete
            var btnDelete = document.createElement("i");
            btnDelete.className = "fa fa-trash fa-2x ml-2";
            btnDelete.onclick = showDeleteModal;
            btnDelete.rowId = i;
            btnDelete.style.cursor = "pointer";
            btnDelete.style.color = "#11acf4";
            btnDelete.setAttribute("data-toggle", "tooltip");
            btnDelete.setAttribute("title", locale['tooltip.delete']);
            row.cells[fieldDelete].innerHTML = "";
            row.cells[fieldDelete].appendChild(btnDelete);
            row.cells[fieldDelete].className = "pb-0";
            @endcan
        }

    }
}

function showAddModal()
{
    $("#modalAction").modal('show');


    $("#modal-acc").prop('disabled', false);


    $("#modal-bank").val('');
    $("#modal-acc").val('');
    $("#modal-name").val('');



}

function showEditModal()
{
    $("#modalAction").modal('show');

    var accNo = mainData.results[this.rowId - 1]["acc_no"];
    var bankName = mainData.results[this.rowId - 1]["bank"];
    var holderName = mainData.results[this.rowId - 1]["name"];

    $("#modal-acc").prop('disabled', true);


    $("#modal-bank").val(bankName);
    $("#modal-acc").val(accNo);
    $("#modal-name").val(holderName);


}



function showDeleteModal()
{   
    var modal = document.createElement("div");
    modal.className = "modal fade modal-primary";
    modal.id = "modal-delete";
    modal.setAttribute("role", "dialog");     

    var dialog = document.createElement("div");
    dialog.className = "modal-dialog";
    
    dialog.setAttribute("role", "document");   
    modal.appendChild(dialog);              

    var content = document.createElement("div");
    content.className = "modal-content";
    dialog.appendChild(content);   

    var header = document.createElement("div");
    header.className = "modal-header";
    content.appendChild(header);   

    var title = document.createElement("h4");
    title.className = "modal-title";
    title.innerHTML = "Delete";
    header.appendChild(title);

    var btnX = document.createElement("button");
    btnX.className = "close";
    btnX.setAttribute("data-dismiss", "modal");
    btnX.innerHTML = "×";
    header.appendChild(btnX);

    var body = document.createElement("div");
    body.className = "modal-body";

    body.innerHTML = "Are you sure you want to delete?";

    content.appendChild(body); 

    var footer = document.createElement("div");
    footer.className = "modal-footer";
    content.appendChild(footer); 

    var btnDelete = document.createElement("button");
    btnDelete.className = "btn btn-primary";
    btnDelete.setAttribute("data-dismiss", "modal");
    btnDelete.innerHTML = "Delete";
    btnDelete.id = "btn-delete";
    btnDelete.rowId = this.rowId;
    btnDelete.onclick = deleteBank;
    footer.appendChild(btnDelete);

    $(modal).modal('show');

    // speed up focus on close btn
    setTimeout(function (){
        $(btnDelete).focus();
    }, 150);

    //fail safe to focus
    $(modal).on('shown.bs.modal', function() {
        $(btnDelete).focus();
    });
}

function deleteBank()
{
    utils.startLoadingBtn("btn-delete","modal-delete");

    var id = mainData.results[this.rowId - 1]["id"];


    $.ajax({
        url: "/ajax/member/bank/blacklist/delete",
        type: "POST",
        data:  {"id": id},
        success: function(data)
        {
            // console.log(data);

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.stopLoadingBtn("btn-delete","modal-delete");

                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);
                $("#modal-delete").modal('hide');

            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status, getMainData);
                $("#modal-delete").modal('hide');
            }
        },
        error: function(){}
    }); 
}


function submitAction()
{
  if($("#formAction").attr("enabled") == 0)
    {
        return;
    }

    $("#formAction").attr("enabled",0);

    utils.startLoadingBtn("btnSubmit","formAction");

    $.ajax({
        url: "/ajax/member/bank/blacklist/add",
        type: "POST",
        data:  new FormData($("#formAction")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            // console.log(data);

            utils.stopLoadingBtn("btnSubmit","formAction");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,onMainModalDismiss);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onMainModalDismissError);
            }
        },
        error: function(){}             
    }); 
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


function onMainModalDismiss()
{
    window.location.href = "/merchants/merchant/member/blacklistbank";
}



function onMainModalDismissError()
{
    $("#formAction").attr("enabled",1);
}


function resetMainData()
{
    $("#username, #tier4").val("");
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
    <li class="breadcrumb-item">Member Bank List</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">
            <div class="card-header">
                @can('permissions.edit_member_blacklist_bank')
                <button type="button" class="btn btn-sm btn-primary pull-right" onclick="showAddModal()"><i class="fa fa-plus"></i> Add BlackList Bank</button>
                @endcan
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
                <h4 class="modal-title">{{ __('app.banking.bank.modal.title') }}</h4>
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

                                    <label>{{ __('app.banking.bank.modal.accno') }}</label>

                                </div>

                                <div class="col-sm-8">

                                    <input type="text" id="modal-acc" name="acc_no" class="form-control">

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
