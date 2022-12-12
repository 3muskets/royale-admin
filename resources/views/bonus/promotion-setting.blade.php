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


    $("#modalAddPromo").on('submit',(function(e){
        e.preventDefault();
    }));



    var s_date = utils.getParameterByName("s_date");
    var e_date = utils.getParameterByName("e_date");

    $("#s_date").val(utils.formattedDate(s_date));
    $("#e_date").val(utils.formattedDate(e_date));

    $("#s_date1").val(s_date);
    $("#e_date1").val(e_date);

    if (!s_date) 
      utils.datepickerStart('s_date','e_date','s_date1',date);

    if (!e_date) 
      utils.datepickerEnd('s_date','e_date','e_date1',date,1);
  
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

    utils.datepickerStart('s_date','e_date','s_date','');
    utils.datepickerEnd('s_date','e_date','e_date','');




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
        url: "/ajax/promo/getList",
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
                ["promo_id", 'Promo ID',true,false]
                ,["promo_name", 'Promo Name',true,false]
                ,["status", 'Status',true,false]
                ,["is_casino", 'Casino',true,false]
                ,["is_sportbook", 'Sportbook',true,false]
                ,["is_slot", 'Slot',true,false]
                ,["rate", 'Percentage (%)',true,false]
                ,["turnover_multiple", 'Turnover Multiply',true,false]
                ,["detail", 'Promotion Detail',false,false]
                ,["start_date", 'Start Date',true,false]
                ,["end_date", 'End Date',true,false]
                ,["images", 'Images',true,false]
                ,["created_at", 'Created at',true,false]
                ,["updated_at", 'Updated at',true,false]
                @can('permissions.edit_promo')
                ,["edit",'Edit',false,false] 
                @endcan
            ];


    
    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();
        

        var fieldEdit = utils.getDataTableFieldIdx("edit",fields);
        var fieldCheckBoxCasino = utils.getDataTableFieldIdx("is_casino",fields);
        var fieldCheckBoxSportbook = utils.getDataTableFieldIdx("is_sportbook",fields);
        var fieldCheckBoxSlot = utils.getDataTableFieldIdx("is_slot",fields);
        var fieldPromoDetail = utils.getDataTableFieldIdx("detail",fields);
        var fieldStatus = utils.getDataTableFieldIdx("status",fields);
        var fieldImages = utils.getDataTableFieldIdx("images",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            var isCasino = mainData.results[i - 1]["is_casino"];
            var isSportbook = mainData.results[i - 1]["is_sportbook"];
            var isSlot = mainData.results[i - 1]["is_slot"];
            var promoDetail = mainData.results[i - 1]['detail'];

            row.cells[fieldCheckBoxCasino].style = 'text-align: center;';

            row.cells[fieldImages].innerHTML = "";

            if(mainData.results[i - 1]["image"] != null)
            {
                var bannerImage = document.createElement("img");
                bannerImage.src= mainData.results[i - 1]["image"];
                bannerImage.style.width = '100%';
                row.cells[fieldImages].innerHTML = "";
                row.cells[fieldImages].appendChild(bannerImage);  
            }

            if(isCasino == 0)
                row.cells[fieldCheckBoxCasino].innerHTML = '<i class="fa fa-close fa-lg" style="color: red; " data-toggle="tooltip""></i>';
            else
                row.cells[fieldCheckBoxCasino].innerHTML = '<i class="fa fa-check fa-lg" style="color: green; " data-toggle="tooltip""></i>';

            if(isSportbook == 0)
                row.cells[fieldCheckBoxSportbook].innerHTML = '<i class="fa fa-close fa-lg" style="color: red; " data-toggle="tooltip""></i>';
            else
                row.cells[fieldCheckBoxSportbook].innerHTML = '<i class="fa fa-check fa-lg" style="color: green; " data-toggle="tooltip""></i>';

            if(isSlot == 0)
                row.cells[fieldCheckBoxSlot].innerHTML = '<i class="fa fa-close fa-lg" style="color: red; " data-toggle="tooltip""></i>';
            else
                row.cells[fieldCheckBoxSlot].innerHTML = '<i class="fa fa-check fa-lg" style="color: green; " data-toggle="tooltip""></i>';


            if(promoDetail != null)
            {
                row.cells[fieldPromoDetail].innerHTML = promoDetail.substring(0,15);
                
                if(promoDetail.length > 8)
                    row.cells[fieldPromoDetail].innerHTML +=  '.....';
            }

            //status
            if(mainData.results[i - 1]["status"] == "a")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+mainData.results[i - 1]["status_desc"] +'</span>';
            else 
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+mainData.results[i - 1]["status_desc"] +'</span>';

            @can('permissions.edit_promo')
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
            @endcan
        }  
    }


}

function showEditModal()
{
    document.getElementById("formEditPromo").reset();

    $("#modalEditPromo").modal('show');
 
    var promoId = mainData.results[this.rowId - 1]["promo_id"];
    var promoName = mainData.results[this.rowId - 1]["promo_name"];
    var rate = mainData.results[this.rowId - 1]["rate"];
    var turnoverMultiple = mainData.results[this.rowId - 1]["turnover_multiple"];
    var isCasino = mainData.results[this.rowId - 1]["is_casino"];
    var isSportbook = mainData.results[this.rowId - 1]["is_sportbook"];
    var isSlot = mainData.results[this.rowId - 1]["is_slot"];
    var detail = mainData.results[this.rowId - 1]["detail"];
    var status = mainData.results[this.rowId - 1]["status"];
    var img = mainData.results[this.rowId - 1]["image"];
    var type = mainData.results[this.rowId - 1]["type"];

    if(isCasino == 1)
        document.getElementById("is_casino").checked = true;

    if(isSportbook == 1)
        document.getElementById("is_sportbook").checked = true;

    if(isSlot == 1)
        document.getElementById("is_slot").checked = true;

    if(type != null)
    document.getElementById("edit_promo_type"+type).checked = true;

    $('#edit_status').append('<option>' + '</option>').children().remove();  
    $('#edit_status').append('{{ Helper::generateOptions($OptionPromoStatus,'') }}');
    document.getElementById("edit_status").value = status;

    if(mainData.results[this.rowId - 1]["start_date"] != null)
    {
        utils.datepickerStart('edit_s_date','edit_e_date','edit_s_date1',utils.formattedDate(mainData.results[this.rowId - 1]["start_date"]));
        utils.datepickerEnd('edit_s_date','edit_e_date','edit_e_date1',utils.formattedDate(mainData.results[this.rowId - 1]["end_date"]),1);
    }
    else
    {
        utils.datepickerStart('edit_s_date','edit_e_date','edit_s_date1',date);
        utils.datepickerEnd('edit_s_date','edit_e_date','edit_e_date1',date,1);
        $("#edit_s_date, #edit_s_date1, #edit_e_date, #edit_e_date1").val("");
    }




    $("#edit_promo_name").val(promoName);
    $("#edit_promo_id").val(promoId);
    $("#edit_rate").val(rate);
    $("#edit_turnover_multiple").val(turnoverMultiple);
    $("#edit_promo_detail").val(detail);

    if(img != null)
    {
        document.getElementById("edit_banner").style.display = "block";
        $('#edit_banner')
            .attr('src', img)
            .width('100%');
    }
    else
    {
        document.getElementById("edit_banner").style.display = "none";
    }

}

function showAddPromo()
{
     document.getElementById("formAddPromo").reset();
    $("#modalAddPromo").modal('show');

}

function submitPromoSetting()
{
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnAddPromo","modalEditPromo");

    $("#formEditPromo").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/promo/setting/update",
        data:  new FormData($("#formEditPromo")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnAddPromo","modalEditPromo");

            var obj = JSON.parse(data);

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


function createNewPromo()
{
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitDetails","modalAddPromo");

    $("#formAddPromo").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/promo/setting/create",
        data:  new FormData($("#formAddPromo")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnSubmitDetails","modalAddPromo");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $('#status').html('');
                $("#modalAddPromo").modal('hide');

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
    $("#formAddPromo").attr("enabled",1);
    $("#formEditPromo").attr("enabled",1);
}

function readURL(input) 
{
    if (input.files && input.files[0]) 
    {
        var reader = new FileReader();

        reader.onload = function (e) 
        {
            document.getElementById("banner").style.display = "block";
            $('#banner')
                .attr('src', e.target.result)
                .width('100%');
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function eidtReadURL(input) 
{
    if (input.files && input.files[0]) 
    {
        var reader = new FileReader();

        reader.onload = function (e) 
        {
            document.getElementById("edit_banner").style.display = "block";
            $('#edit_banner')
                .attr('src', e.target.result)
                .width('100%');
        };

        reader.readAsDataURL(input.files[0]);
    }
}


</script>

<style type="text/css">
    


</style>

@endsection

@section('content')

<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.bonus.promo.breadcrumb.bonus') }}</li>
    <li class="breadcrumb-item active">{{ __('app.bonus.promo.breadcrumb.promosetting') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div id="main-data">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('app.bonus.promo.header.promosetting') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-2">
                            
                            <div class="form-group">
                                <label for="f_title">Event Title</label>
                                <input type="text" class="form-control" id="f_title" placeholder="">
                            </div>

                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="f_status">Status</label>
                                <select name="f_status" id="f_status" class="form-control">
                                    <option value="">All</option>
                                    <option value="a">Active</option><option value="i">Inactive</option> 
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="f_p_type">Type</label>
                                <select name="f_p_type" id="f_p_type" class="form-control">
                                    <option value="">All</option>
                                    <option value="1">First Time</option><option value="2">Daily</option><option value="3">Weekly</option><option value="4">Monthly</option><option value="5">Everyime</option> 
                                </select>
                            </div>
                        </div>                    
                    </div>
                    
                </div>
                <div class="card-footer">
                    <button type="button" id="submit" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> Filter</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="resetMainData()"><i class="fa fa-ban"></i> Reset</button>
                    @can('permissions.edit_promo')
                    <button type="button" class="btn btn-sm btn-primary pull-right" onclick="showAddPromo()"><i class="fa fa-plus"></i> Create</button>  
                    @endcan     
                </div>
            </div>
            <div class="card">

                <div class="card-body">
                    <div id="main-spinner" class="card-body"></div>

                    <div id="main-table" class="card-body"></div>

                    <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>      
                    
                </div>
            </div>

        </div>

    </div>
</div>

<div id="modalEditPromo" class="modal fade" role="dialog" style="overflow-x: hidden;overflow-y: auto;">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-title">{{ __('app.bonus.promo.modal.editpromo') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formEditPromo">

                       <input type="hidden" id="edit_promo_id" name="promo_id">
                       <input type="hidden" id="category" name="category">

                       <div class="card-body">

                            <div class="row">
                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label>{{ __('app.bonus.promo.modal.promoname') }}</label>
                                        <input type='text' id="edit_promo_name" name="promo_name" autocomplete="off" class="form-control">
                                    </div>

                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Percentage (%)</label>
                                        <input type='text' id="edit_rate" name="rate" autocomplete="off" class="form-control">
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>{{ __('app.bonus.promo.modal.turnovermult') }}</label>
                                        <input type='text' id="edit_turnover_multiple" name="turnover_multiple" autocomplete="off" class="form-control">
                                    </div>

                                </div>
                     
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control" id="edit_status">
                                            <option value="a">Active</option>
                                            <option value="i">Inactive</option> 
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Promotion Type</label>
                                        <br>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="edit_promo_typef" name="promo_type" value="f">
                                            <label class="custom-control-label" for="edit_promo_typef">First Time</label>
                                        </div>

                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="edit_promo_typed" name="promo_type" value="d">
                                            <label class="custom-control-label" for="edit_promo_typed">Daily</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="edit_promo_typew" name="promo_type" value="w">
                                            <label class="custom-control-label" for="edit_promo_typew">Weekly</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="edit_promo_typem" name="promo_type" value="m">
                                            <label class="custom-control-label" for="edit_promo_typem">Monthly</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label for="edit_s_date" style="margin: 10px 0;">Promotion Start Date</label>
                                    </div>

                                </div>
                                <div class="col-sm-6">

                                    <div class="form-group">

                                        <input type="text" class="form-control" name="edit_s_date" id="edit_s_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                        <input type="hidden" name="edit_s_date1" id="edit_s_date1" value="">

                                    </div>

                                </div>

                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label for="edit_e_date" style="margin: 10px 0;">Promotion End Date</label>
                                    </div>

                                </div>
                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <input type="text" class="form-control" name="edit_e_date" id="edit_e_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                        <input type="hidden" name="edit_e_date1" id="edit_e_date1" value="">
                                    </div>

                                </div>


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Banner Image: </label>
                                        <input name="image" type="file" onchange="eidtReadURL(this);">
                                        <img style="display:none;" id="edit_banner" src="#" alt="banner image"><br>
                                    </div>
                                </div> 
                    

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Promotion Detail</label>
                                        <textarea rows="6" cols="50" type='text' id="edit_promo_detail" name="promo_detail" class="form-control">
                                        </textarea>
                                    </div>

                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                      <input type="hidden" name="is_casino" value="0">
                                      <input type="hidden" name="is_sportbook" value="0">
                                      <input type="hidden" name="is_slot" value="0">
                                      <input type="checkbox" id="is_casino" name="is_casino" value="1">
                                      <label for="is_casino">{{ __('app.bonus.promo.modal.livecasino') }}</label><br>
                                      <input type="checkbox" id="is_sportbook" name="is_sportbook" value="1">
                                      <label for="is_sportbook">{{ __('app.bonus.promo.modal.sportbook') }}</label><br>
                                      <input type="checkbox" id="is_slot" name="is_slot" value="1">
                                      <label for="is_slot">{{ __('app.bonus.promo.modal.slot') }}</label><br><br>
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

<div id="modalAddPromo" class="modal fade" role="dialog" style="overflow-x: hidden;overflow-y: auto;">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-title">Add Promotion</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formAddPromo">
                       <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label>{{ __('app.bonus.promo.modal.promoname') }}</label>
                                        <input type='text' id="promo_name" name="promo_name" autocomplete="off" class="form-control">
                                    </div>

                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Percentage (%)</label>
                                        <input type='text' id="rate" name="rate" autocomplete="off" class="form-control">
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>{{ __('app.bonus.promo.modal.turnovermult') }}</label>
                                        <input type='text' id="turnover_multiple" name="turnover_multiple" autocomplete="off" class="form-control">
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="a">Active</option>
                                            <option value="i">Inactive</option> 
                                        </select>
                                    </div>
                                </div>


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Promotion Type</label>
                                        <br>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="promo_type1" name="promo_type" value="f">
                                            <label class="custom-control-label" for="promo_type1">First Time</label>
                                        </div>

                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="promo_type2" name="promo_type" value="d">
                                            <label class="custom-control-label" for="promo_type2">Daily</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="promo_type3" name="promo_type" value="w">
                                            <label class="custom-control-label" for="promo_type3">Weekly</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input" id="promo_type4" name="promo_type" value="m">
                                            <label class="custom-control-label" for="promo_type4">Monthly</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label for="s_date" style="margin: 10px 0;">Promotion Start Date</label>
                                    </div>

                                </div>
                                <div class="col-sm-6">

                                    <div class="form-group">

                                        <input type="text" class="form-control" name="s_date" id="s_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                        <input type="hidden" name="s_date1" id="s_date1" value="">

                                    </div>

                                </div>

                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label for="e_date" style="margin: 10px 0;">Promotion End Date</label>
                                    </div>

                                </div>
                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <input type="text" class="form-control" name="e_date" id="e_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                        <input type="hidden" name="e_date1" id="e_date1" value="">
                                    </div>

                                </div>


                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Banner Image: </label>
                                        <input name="image" type="file" onchange="readURL(this);">
                                        <img style="display:none;" id="banner" src="#" alt="banner image"><br>
                                    </div>
                                </div> 
                    

                                <div class="col-sm-6">
                                    <div class="form-group">
                                      <label>Category</label>
                                      <br>
                                      <input type="hidden" name="is_casino" value="0">
                                      <input type="hidden" name="is_sportbook" value="0">
                                      <input type="hidden" name="is_slot" value="0">
                                      <input type="checkbox" id="is_casino" name="is_casino" value="1">
                                      <label for="is_casino">{{ __('app.bonus.promo.modal.livecasino') }}</label><br>
                                      <input type="checkbox" id="is_sportbook" name="is_sportbook" value="1">
                                      <label for="is_sportbook">{{ __('app.bonus.promo.modal.sportbook') }}</label><br>
                                      <input type="checkbox" id="is_slot" name="is_slot" value="1">
                                      <label for="is_slot">{{ __('app.bonus.promo.modal.slot') }}</label><br><br>
                                    </div>
                                </div>
                                
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Promotion Detail</label>
                                        <textarea rows="6" cols="50" type='text' id="promo_detail" name="promo_detail" class="form-control">
                                        </textarea>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <div class="card-footer">

                            <button id="btnAddPromo" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="createNewPromo()">
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
