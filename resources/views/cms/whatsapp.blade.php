@extends('layouts.app')

@section('head')

<script type="text/javascript">

var date = utils.getToday();

$(document).ready(function() 
{
    prepareLocale();

    // utils.createSpinner("main-spinner");

    // getMainData();


    /*getMainData();*/
    $("#mainForm").attr("enabled",1);

    $("#mainForm").on('submit',(function(e){
        e.preventDefault();
        submitMainForm();
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

function submitMainForm()
{   
    if($("#mainForm").attr("enabled") == 0)
    {
        return;
    }

    $("#mainForm").attr("enabled",0);

    utils.startLoadingBtn("btnSubmit","mainForm");

    $.ajax({
        url: "/ajax/cms/whatsapp",
        type: "POST",
        data: new FormData($("#mainForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            utils.stopLoadingBtn("btnSubmit","mainForm");

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

// var mainData;
// var mainDataDetail;
// var refreshMainData = false;

// function getMainData() 
// {

//     var containerId = "main-table";
    
//     $("#main-spinner").show();
//     $("#main-table").hide();
//     $('#notes').hide();

//     var data = utils.getDataTableDetails(containerId);

//     data["type"] = $("#f_p_type").val();
//     data["status"] = $("#f_status").val();
//     data["promo_name"] = $("#f_title").val();


//     $.ajax({
//         type: "GET",
//         url: "/ajax/announcement/getList",
//         data: data,
//         success: function(data) 
//         {

//             if(data.length > 0)
//             {
//                 mainData = JSON.parse(data);

//             }
//             else
//             {
//                 mainData = [];
//             }

//             loadMainData(containerId);
//         }
//     });
// }

// function loadMainData(containerId)
// {
//     $("#main-table").show();
//     $("#main-spinner").hide();



//     var fields = [  
//                 ["sequence", 'Sequence',true,false]
//                 ,["status", 'Status',true,false]
//                 ,["text", 'Announcement Text',true,false]
//                 ,["start_date", 'Start Date',true,false]
//                 ,["end_date", 'End Date',true,false]
//                 ,["created_at", 'Created at',true,false]
//                 ,["updated_at", 'Updated at',true,false]
//                 ,["edit",'Edit',false,false] 
//             ];


    
//     table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

//     if(table != null)
//     {
//         $("#notes").show();
        

//         var fieldEdit = utils.getDataTableFieldIdx("edit",fields);
//         var fieldStatus = utils.getDataTableFieldIdx("status",fields);
//         var fieldText = utils.getDataTableFieldIdx("text",fields);

//         for (var i = 1, row; row = table.rows[i]; i++) 
//         {   

//             //status
//             if(mainData.results[i - 1]["status"] == "a")
//                 row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+mainData.results[i - 1]["status_desc"] +'</span>';
//             else 
//                 row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+mainData.results[i - 1]["status_desc"] +'</span>';


//             var btnEdit = document.createElement("i");
//             btnEdit.className = "fa fa-edit fa-2x";
//             btnEdit.onclick = showEditModal;
//             btnEdit.rowId = i;
//             btnEdit.style.cursor = "pointer";
//             btnEdit.style.color = "#11acf4";
//             btnEdit.setAttribute("data-toggle", "tooltip");
//             btnEdit.setAttribute("title", locale['tooltip.edit']);
//             row.cells[fieldEdit].innerHTML = "";
//             row.cells[fieldEdit].appendChild(btnEdit);
//             row.cells[fieldEdit].className = "pb-0";
//         }  
//     }


// }


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


function onMainModalDismiss()
{
    window.location.href = "/cms/whatsapp";
}

function onMainModalDismissError()
{
    $("#mainForm").attr("enabled",1);
}

</script>

<style type="text/css">
    


</style>

@endsection

@section('content')

<ol class="breadcrumb">
    <li class="breadcrumb-item">CMS</li>
    <li class="breadcrumb-item active">Whatsapp</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div id="main-data">
            <div class="card">
                <form method="POST" id="mainForm">
                @csrf
                    <div class="card-header">
                        <h4 class="card-title">Whatsapp</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="f_no">Current Number</label>
                                    @if($num)
                                    <input class="form-control form-control-sm" value="{{$num}}" type="text" id="f_no" name="f_no" required autocomplete="off">
                                    @else
                                    <input class="form-control form-control-sm" type="text" id="f_no" name="f_no" required autocomplete="off">
                                    @endif
                                </div>
                            </div>                 
                        </div>
                        
                    </div>
                    <div class="card-footer">
                        <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                            <i class="fa fa-dot-circle-o"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
            

        </div>

    </div>
</div>


@endsection
