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

    $("#modalEditBanner").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));


    $("#modalAddBanner").on('submit',(function(e){
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

    data["type"] = $("#f_p_type").val();
    data["status"] = $("#f_status").val();
    data["promo_name"] = $("#f_title").val();


    $.ajax({
        type: "GET",
        url: "/ajax/banner/getList",
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
                ["sequence", 'Sequence',true,false]
                ,["status", 'Status',true,false]
                ,["images", 'Images',true,false]
                ,["start_date", 'Start Date',true,false]
                ,["end_date", 'End Date',true,false]
                ,["created_at", 'Created at',true,false]
                ,["updated_at", 'Updated at',true,false]
                ,["edit",'Edit',false,false] 
            ];


    
    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();
        

        var fieldEdit = utils.getDataTableFieldIdx("edit",fields);
        var fieldStatus = utils.getDataTableFieldIdx("status",fields);
        var fieldImages = utils.getDataTableFieldIdx("images",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {   

            row.cells[fieldImages].innerHTML = "";

            if(mainData.results[i - 1]["image"] != null)
            {
                var bannerImage = document.createElement("img");
                bannerImage.src= mainData.results[i - 1]["image"];
                bannerImage.style.height = '100px';
                bannerImage.style.width = '100%';
                row.cells[fieldImages].innerHTML = "";
                row.cells[fieldImages].appendChild(bannerImage);  
            }

        

            //status
            if(mainData.results[i - 1]["status"] == "a")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+mainData.results[i - 1]["status_desc"] +'</span>';
            else 
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+mainData.results[i - 1]["status_desc"] +'</span>';


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

 document.getElementById("formEditBanner").reset();

    $("#modalEditBanner").modal('show');
 
    var sequence = mainData.results[this.rowId - 1]["sequence"];
    var status = mainData.results[this.rowId - 1]["status"];
    var img = mainData.results[this.rowId - 1]["image"];

    var bannerId = mainData.results[this.rowId - 1]["id"];




    $('#edit_status').append('<option>' + '</option>').children().remove();  

    $('#edit_status').append('{{ Helper::generateOptions($optionsStatus,'') }}');
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


    $("#edit_sequence").val(sequence);
     $("#edit_banner_id").val(bannerId);

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

function showAddBanner()
{
     document.getElementById("formAddBanner").reset();
    $("#modalAddBanner").modal('show');

}

function submitBanner()
{
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnAddPromo","modalEditBanner");

    $("#formEditBanner").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/cms/banner/update",
        data:  new FormData($("#formEditBanner")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnAddPromo","modalEditBanner");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $('#status').html('');
                $("#modalEditBanner").modal('hide');

            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onModalEditDismissError);

            }
        },
        error: function(){}       
    });
}


function createNewBanner()
{
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitDetails","modalAddBanner");

    $("#formAddBanner").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/cms/banner/create",
        data:  new FormData($("#formAddBanner")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnSubmitDetails","modalAddBanner");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $('#status').html('');
                $("#modalAddBanner").modal('hide');

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


function resetMainData()
{
    $("#f_p_type").val("");
    $("#f_status").val("");
    $("#f_title").val("");    
    filterMainData();
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
    $("#formAddBanner").attr("enabled",1);
    $("#formEditBanner").attr("enabled",1);
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
    <li class="breadcrumb-item">CMS</li>
    <li class="breadcrumb-item active">Main Banner</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div id="main-data">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Main Banner</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="f_status">Status</label>
                                <select name="f_status" id="f_status" class="form-control">
                                    <option value="">All</option>
                                    <option value="a">Active</option><option value="i">Inactive</option> 
                                </select>
                            </div>
                        </div>                 
                    </div>
                    
                </div>
                <div class="card-footer">
                    <button type="button" id="submit" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> Filter</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="resetMainData()"><i class="fa fa-ban"></i> Reset</button>
                    <button type="button" class="btn btn-sm btn-primary pull-right" onclick="showAddBanner()"><i class="fa fa-plus"></i> Create</button>       
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

<div id="modalEditBanner" class="modal fade" role="dialog" style="overflow-x: hidden;overflow-y: auto;">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-title">Edit Banneer</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formEditBanner">

                       <input type="hidden" id="edit_banner_id" name="banner_id">

                       <div class="card-body">

                            <div class="row">
                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label>Sequence</label>
                                        <input type='text' id="edit_sequence" name="sequence" autocomplete="off" class="form-control">
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

                            

                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label for="edit_s_date" style="margin: 10px 0;">Start Date</label>
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
                                        <label for="edit_e_date" style="margin: 10px 0;">End Date</label>
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
                    
                            </div>
                        </div>

                        <div class="card-footer">

                            <button id="btnSubmitDetails" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitBanner()">
                                <i class="fa fa-dot-circle-o"></i> {{ __('common.modal.submit') }}
                            </button>

                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

<div id="modalAddBanner" class="modal fade" role="dialog" style="overflow-x: hidden;overflow-y: auto;">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-title">Create Banner</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formAddBanner">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="a" >Active</option><option value="i" >Inactive</option> 
                                        </select>
                                    </div>   
                                </div>

                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label>Sequence</label>
                                        <input type="number" class="form-control" name="sequence" id="sequence" placeholder="" required>
                                    </div>   
                                </div>

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
                            </div>

                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label>Banner Image: </label>
                                        <input name="image" type="file" required= required onchange="readURL(this);">
                                        <img style="display:none;" class="banner_image" id="banner" src="#" alt="banner image" /><br>
                                    </div>
                                </div> 

                            </div>

                        </div>

                        <div class="card-footer">

                            <button id="btnAddPromo" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="createNewBanner()">
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
