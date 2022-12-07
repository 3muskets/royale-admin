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
        url: "/ajax/bonus/getList",
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
    $("#main-table").show();
    $("#main-spinner").hide();




    var fields = [  
                ["level_id", 'Level',true,false]
                ,["category_name", 'Category',true,false]
                ,["turnover", 'Target Turnover',true,false]
                ,["rate", 'Rate (%)',true,false]
                ,["created_at", 'Created at',true,false]
                ,["updated_at", 'Updated at',true,false]
                ,["edit",'Edit',false,false] 
            ];
    
    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();
        

        var fieldEdit = utils.getDataTableFieldIdx("edit",fields);

        var fieldTurnover = utils.getDataTableFieldIdx("turnover",fields);
        
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

            if(mainData.results[i - 1]["turnover"] != null)
                row.cells[fieldTurnover].innerHTML = utils.formatMoney(mainData.results[i - 1]["turnover"]);
        }  
    }


}

function showEditModal()
{
    $("#modalEditPromo").modal('show');
 
    var level = mainData.results[this.rowId - 1]["level_id"];
    var category = mainData.results[this.rowId - 1]["category"];
    var categoryName = mainData.results[this.rowId - 1]["category_name"];
    var rate = mainData.results[this.rowId - 1]["rate"];
    var turnover = mainData.results[this.rowId - 1]["turnover"];

    $("#level").val(level);
    $("#category").val(category);
    $("#category_name").val(categoryName);    
    $("#rate").val(rate);
    $("#turnover").val(turnover);

}


function submitPromoSetting()
{
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitDetails","modalEditPromo");

    $("#formEditPromo").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/bonus/setting/update",
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
    <li class="breadcrumb-item active">Bonus Setting</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

    <div id="main-data">


        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Bonus Setting</h4>
            </div>


            <div class="card-body">
                <div class="row">
                <!-- </div>

                <div class="row"> -->
                    <div class="col-12 col-sm-2 col-md-2 align-middle">

                        <div class="form-group">
                            <label for="category_id" style="margin: 10px 0;">Category Option</label>
                        </div>

                    </div>
                    <div class="col-12 col-sm-10 col-md-2">

                        <div class="form-group">

                            <select id="category_id" name="category_id" class="form-control" style="margin: 5px 0;">
                                <option value="" selected="">All</option>
                                {{ Helper::generateOptions($optionsCategory,'') }}
                            </select>
                        </div>

                    </div>
                </div>
                
            </div>

            <div class="card-footer">

                    <button id="btnSubmitRebate" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="getMainData()">
                        Submit
                    </button>
                
            </div>

        </div>

        <div class="card">

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
                <h4 class="modal-title">Edit Bonus</h4>
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
                                    <label>Level</label>
                                    <input type='text' id="level" name="level" autocomplete="off" class="form-control" readonly="readonly">
                                </div>

                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Category</label>
                                    <input type='text' id="category_name" name="category_name" autocomplete="off" class="form-control" readonly="readonly">
                                </div>

                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Rate (%)</label>
                                    <input type='text' id="rate" name="rate" autocomplete="off" class="form-control">
                                </div>

                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Target Turnover</label>
                                    <input type='text' id="turnover" name="turnover" autocomplete="off" class="form-control">
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
