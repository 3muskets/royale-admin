@extends('layouts.app')

@section('head')

<style>
.heading {
    font-size: 15px;
    font-weight: bold;
    margin-bottom: 10px;
}
</style>


<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

/*    utils.createSpinner("main-spinner");*/

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));
    
    var memberId = utils.getParameterByName('id');
    var username = utils.getParameterByName('username');

    createBreadcrumbs("breadcrumb-mbmsg");

    createBreadcrumbs("breadcrumb-mb",username,memberId); 

    if(auth.getUserLevel() != 1)
    {
        $('#submit_msg').prop('disabled', true);
        $('#delete_msg').prop('disabled', true);
        $('#clear_msg').prop('disabled', true);
        $('#message').prop('disabled', true);
        $('#subject').prop('disabled', true);
    }


});



function createBreadcrumbs(id, username,memberId) 
{

    var a = document.createElement("a");
    a.innerHTML = username;


    if(id == "breadcrumb-mbmsg")
    {
        a.href = "/member/msg";
        a.innerHTML = document.getElementById(id).innerHTML;
        document.getElementById(id).innerHTML = "";
        document.getElementById(id).appendChild(a);
    }
    
    if(id == "breadcrumb-mb")
    {
        a.href = "detail?id="+memberId+"&username="+username;
        document.getElementById(id).appendChild(a);

    }

 
    $("#" + id).addClass("d-md-block");

    $("#" + id).clone().attr('id',id + '-m').appendTo('#breadcrumb-m');

    $("#" + id + '-m').removeClass("d-none");
}

function prepareLocale()
{


    locale['mainData.username'] = "{!! __('app.member.msg.message.member.details') !!}";
    locale['mainData.sendby'] = "{!! __('app.member.msg.message.sendby.details') !!}";
    locale['mainData.msg'] =  "{!! __('app.member.msg.message.msg.details') !!}";
    locale['mainData.msgtime'] = "{!! __('app.member.msg.message.msgtime.details') !!}";
    locale['mainData.subj'] = "{!! __('app.member.msg.message.subject.details') !!}";


    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
    locale['fail.msg'] ="{!! __('app.member.msg.fail.msg') !!}";
    locale['tooltip.check'] = "<input type='checkbox' id='checkAll' onclick='checkAllData(this);'>";
}

var refreshMainData = false;
var mainData;

function getMainData() 
{
    var containerId = "main-table";

/*    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();*/

    var data = utils.getDataTableDetails(containerId);

    data["id"] = utils.getParameterByName('id');


    $.ajax({
        type: "GET",
        url: "/ajax/member/message/detail",
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
    /*    $("#main-spinner").hide();
        $("#main-table").show();*/

    var fields = [   
                    ["username",locale['mainData.username'],false,false] 
                    ,["",locale['tooltip.check'],false,true] 
                    ,["send_by_desc",locale['mainData.sendby'],false,false] 
                    ,["subject",locale['mainData.subj'],false,false]  
                    ,["message",locale['mainData.msg'],false,false]    
                    ,["created_at",locale['mainData.msgtime'],false,false]                                 
                ];

    if(auth.getUserLevel() != 1)
    {
        for(var i = fields.length-1 ; i > 0; i--)
        {
            if( fields[i][0] == "")
            {
                fields.splice(i,1);
            }
        }   
    }


    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);


    if(table != null)
    {
        $("#notes").show();

        var fieldActions = utils.getDataTableFieldIdx("",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {
            var msgId = mainData.results[i - 1]["id"];

            if(auth.getUserLevel() == 1)
            {
                row.cells[fieldActions].innerHTML = " <input type='checkbox' name='check[]' id='check_"+i+"' value='" + msgId  + "'>";
                row.cells[fieldActions].className = "text-center display";
            }


        }

    }
}




function submitMessage()
{
    data = {};

    data['message'] = $("#message").val();
    data['subject'] = $("#subject").val();
    data['id'] = utils.getParameterByName('id');


    utils.startLoadingBtn("submit_msg","filterForm");
 
    $.ajax({
        type: "POST",
        url: "/ajax/member/message/update",
        data:  data,
        success: function(data) 
        {
           $("#message").val("");
           $("#subject").val("");

           utils.stopLoadingBtn("submit_msg","filterForm");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,getMainData);
            }

        },
        error: function(){}       
    });
}


function checkAllData(e)
{
    var checked = e.checked;
    if (checked) {
        $('input[name="check[]"]').prop('checked', true);
    } else {
        $('input[name="check[]"]').prop('checked', false);
    }
}

function deleteSelectedMessage()
{
    dataDel = {};
    var check = [];
    var memberId = utils.getParameterByName('id');
    
    $.each($("input[name='check[]']:checked"), function(){            
        check.push($(this).val());
    });

    dataDel['msg_id'] = check;
    dataDel['member_id'] = memberId;

    utils.startLoadingBtn("delete_msg","filterForm");
 
    $.ajax({
        type: "POST",
        url: "/ajax/member/message/delete",
        data:  dataDel,
        success: function(data) 
        {
           $("#message").val("");
           $("#subject").val("");

           utils.stopLoadingBtn("delete_msg","filterForm");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,getMainData);
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

function clearMessage()
{
    $("#message").val("");
    $("#subject").val("");
    $("#checkAll").prop('checked', false);
    $('input[name="check[]"]').prop('checked', false);
}
</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item" id="breadcrumb-mbmsg">{{ __('app.member.msg.breadcrumb.membermsg.details') }}</li>
    <li id="breadcrumb-mb" class="breadcrumb-item d-none"></li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="filterForm">

                <div class="card-header">
                    <strong></strong>
                </div>
                
                <div class="card-body">

                            <div id="main-table" class="card-body"></div>

                            <div class="col-12" style="padding-left: 20px;">
                                <label for="message" class="heading">
                                   {{ __('app.member.msg.message.subject.details') }}
                                </label>
                                <div class="col-sm-6 col-md-4" style="padding-left: 0px;">
                                    <input id="subject" name="subject" type="text" class="form-control form-control-sm" >
                                </div>
                            </div>
                        
                            <div class="col-12" style="padding-left: 20px;">
                                <label for="message" class="heading">
                                    {{ __('app.member.msg.message.msg.details') }}
                                </label>
                                <div class="col-sm-6 col-md-4" style="padding-left: 0px;">
                                    <textarea id="message" name="message" class="form-control form-control-sm" rows="8" style="width: 100%; "></textarea>
                                </div>
                            </div>
                </div>

                <div class="card-footer">
                    <button type="button" id="submit_msg" class="btn btn-sm btn-success" onclick="submitMessage()" ><i class="fa fa-dot-circle-o"></i>{{ __('app.member.msg.message.button.submit.details') }}</button>

                    <button type="button" id="clear_msg" class="btn btn-sm btn-danger" onclick="clearMessage()"><i class="fa fa-ban"></i>{{ __('app.member.msg.message.button.cancel.details') }}</button>

                    <button type="button" id="delete_msg"  class="btn btn-sm btn-primary" onclick="deleteSelectedMessage()"><i class="fa fa-ban"></i>{{ __('app.member.msg.message.button.delete.details') }}</button>
                </div>

            </form>

        </div>


    </div>
</div>

@endsection
