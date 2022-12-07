@extends('layouts.app')

@section('head')

<script type="text/javascript">

    var date = utils.getToday();

$(document).ready(function() 
{
    prepareLocale();

    utils.createSpinner("main-spinner");

    getMainData();


    /*getMainData();*/

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        /*filterMainData();*/
    }));

    $("#modalEditPromo").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));


});

function prepareLocale()
{   
    locale['txn_id'] = "{!! __('app.reports.credit.member.txn_id') !!}";
    locale['type'] = "{!! __('app.reports.credit.member.type') !!}";
    locale['operator'] = "{!! __('app.reports.credit.member.operator') !!}";
    locale['credit_before'] = "{!! __('app.reports.credit.member.credit_before') !!}";
    locale['credit_after'] = "{!! __('app.reports.credit.member.credit_after') !!}";
    locale['created_at'] = "{!! __('app.reports.credit.member.createdat') !!}";
    locale['remark'] = "{!! __('app.reports.credit.member.remark') !!}";
    locale['member'] = "{!! __('app.reports.credit.member.username') !!}";
    locale['agent'] = "{!! __('app.reports.credit.agent.username') !!}";
    locale['transfer_in'] = "{!! __('app.reports.credit.member.transfer_in') !!}";
    locale['transfer_out'] = "{!! __('app.reports.credit.member.transfer_out') !!}";

    locale['success'] = "Success";
}


var mainData;
var mainDataDetail;
var refreshMainData = false;

function getMainData() 
{

    var containerId = "main-table";
    
    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var data = utils.getDataTableDetails(containerId);

    data["category_id"] = $("#category_id").val();

    

    $.ajax({
        type: "GET",
        url: "/ajax/referral/getList",
        data: data,
        success: function(data) 
        {

            if(data.length > 0)
            {
                mainData = JSON.parse(data);
                console.log(mainData);
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
    $("#main-table").show();
    $("#main-spinner").hide();


    var fields = [  
                ["id", 'ID',false,false]
                ,["tier1_rate", 'Tier 1 Rate (%)',false,false]
                ,["tier2_rate", 'Tier 2 Rate (%)',false,false]
                ,["tier3_rate", 'Tier 3 Rate (%)',false,false]
                ,["created_at", 'Created at',false,false]
                ,["updated_at", 'Updated at',false,false]
                ,["edit",'Edit',false,false] 
            ];
    
    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();
        

        var fieldEdit = utils.getDataTableFieldIdx("edit",fields);


        for (var i = 1, row; row = table.rows[i]; i++) 
        {   

            var btnEdit = document.createElement("i");
            btnEdit.className = "fa fa-edit fa-2x";
            btnEdit.onclick = showEditModal;
            btnEdit.rowId = i;
            btnEdit.style.cursor = "pointer";
            btnEdit.style.color = "#11acf4";
            btnEdit.setAttribute("data-toggle", "tooltip");
            btnEdit.setAttribute("title", locale['tooltip.edit']);
            row.cells[fieldEdit].innerHTML = "";
            row.cells[fieldEdit].appendChild(btnEdit);
            row.cells[fieldEdit].className = "pb-0";
        }  
    }


}

function showEditModal()
{
    $("#modalEditPromo").modal('show');
 
    var tier1Rate = mainData.results[this.rowId - 1]["tier1_rate"];
    var tier2Rate = mainData.results[this.rowId - 1]["tier2_rate"];
    var tier3Rate = mainData.results[this.rowId - 1]["tier3_rate"];


    $("#tier1_rate").val(tier1Rate);
    $("#tier2_rate").val(tier2Rate);
    $("#tier3_rate").val(tier3Rate);    

}


function submitPromoSetting()
{
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitDetails","modalEditPromo");

    $("#formEditPromo").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/referral/setting/update",
        data:  new FormData($("#formEditPromo")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnSubmitDetails","modalEditPromo");

            var obj = JSON.parse(data);

            console.log(obj);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $('#status').html('');
                $("#modalEditPromo").modal('hide');

            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onModalEditDismissError);

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

function onModalDismiss() 
{
    if(refreshMainData)
    {
        getMainData();
    }
}

function onModalEditDismissError()
{
    $("#formEditMerchant").attr("enabled",1);
}

</script>

<style type="text/css">
    


</style>

@endsection

@section('content')

<ol class="breadcrumb">
    <li class="breadcrumb-item">Bonus</li>
    <li class="breadcrumb-item active">Referral Setting</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

    <div id="main-data">


        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Referral Setting</h4>
            </div>
            <div id="main-spinner" class="card-body"></div>

            <div id="main-table" class="card-body"></div>

            <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>            

        </div>

    </div>

</div>

<div id="modalEditPromo" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Referral</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formEditPromo">

                       <input type="hidden" id="category" name="category">

                       <div class="card-body">

                        <div class="row">
                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label>Tier 1 Rate (%)</label>
                                    <input type='text' id="tier1_rate" name="tier1_rate" autocomplete="off" class="form-control" >
                                </div>

                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Tier 2 Rate (%)</label>
                                    <input type='text' id="tier2_rate" name="tier2_rate" autocomplete="off" class="form-control">
                                </div>

                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Tier 3 Rate (%)</label>
                                    <input type='text' id="tier3_rate" name="tier3_rate" autocomplete="off" class="form-control">
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="card-footer">

                        <button id="btnSubmitDetails" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitPromoSetting()">
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
