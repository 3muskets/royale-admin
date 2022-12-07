@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

    utils.createSpinner("main-spinner");

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    if(auth.getUserLevel() != 1)
    {
        $('#msg-selected-mb').hide();
        $('#msg-all-mb').hide();
    }
});

function prepareLocale()
{

    locale['mainData.id'] ="{!! __('app.member.msg.message.memberid') !!}";
    locale['mainData.unread'] = "{!! __('app.member.msg.message.unreadmsg') !!}";
    locale['mainData.username'] ="{!! __('app.member.msg.message.member') !!}";
    

    locale['fail.msg'] ="{!! __('app.member.msg.fail.msg') !!}";
    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";
    locale['tooltip.check'] = "<input type='checkbox' id='checkAll' onclick='checkAll(this);'>";
}

var refreshMainData = false;
var mainData;
var type;

function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();

    var data = utils.getDataTableDetails(containerId);


    data["member_name"] = $("#f_member").val();

    $.ajax({
        type: "GET",
        url: "/ajax/member/message/list",
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
                    ,["",locale['tooltip.check'],false,true] 
                    ,["unread_msg",locale['mainData.unread'],false,true]          
                                        
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

        var fieldMemberId = utils.getDataTableFieldIdx("username",fields);
        var fieldActions = utils.getDataTableFieldIdx("",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {
            var id = mainData.results[i - 1]["id"];

            //member id
            var a = document.createElement("a");
            a.href = "/member/msg/detail?id=" + id +"&username="+mainData.results[i - 1]["username"];
            a.innerHTML = mainData.results[i - 1]["username"];

            row.cells[fieldMemberId].innerHTML =  "";
            row.cells[fieldMemberId].appendChild(a);

            if(auth.getUserLevel() == 1)
            {
                row.cells[fieldActions].innerHTML = " <input type='checkbox' name='check[]' id='check_"+i+"' value='" + id  + "'>";
                row.cells[fieldActions].className = "text-center display";
            }


        }

    }
}

function checkAll(e)
{
    var checked = e.checked;
    if (checked) {
        $('input[name="check[]"]').prop('checked', true);
    } else {
        $('input[name="check[]"]').prop('checked', false);
    }
}

function submitMessage()
{
    var data = {};
    var check = [];
    
    $.each($("input[name='check[]']:checked"), function(){            
        check.push($(this).val());
    });

    utils.startLoadingBtn("submit_msg","modalDetails");

    data['message'] = $("#message").val();
    data['subject'] = $("#subject").val();

    //message all member
    if(type == 1)
    {
        data['id'] = 'a';
    }
    else if(type == 2)
    {
        data['id'] = check;
    }

 
    $.ajax({
        type: "POST",
        url: "/ajax/member/message/update",
        data:  data,
        success: function(data) 
        {
            utils.stopLoadingBtn("submit_msg","modalDetails");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                utils.showModal(locale['info'],locale['success'],obj.status,getMainData);
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,getMainData);
            }

           
           
            $("#message").val("");
            $("#subject").val("");
            $("#modalDetails").modal('hide');

        },
        error: function(){}       
    });
}

function cancelMessage()
{
    $("#message").val("");
    $("#subject").val("");
    $("#modalDetails").modal('hide');
}


function messageAllModule(value)
{
    type = value;

    $("#modalDetails").modal('show');

    if(type == 1)
    {
        document.getElementById("modal-msg-title").innerHTML = "{!! __('app.member.msg.message.allmember') !!}";
    }
    else if(type == 2)
    {
        document.getElementById("modal-msg-title").innerHTML = "{!! __('app.member.msg.message.selectedmember') !!}";;
    }
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
    filterMainData();
}
</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.member.msg.breadcrumb.membermsg') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

<!--         <div class="card">
            <div class="card-header">
                <strong>Message All Member</strong>
            </div>
            <div class="card-body">
                <div class="col-12" style="padding-left: 0px;">
                    <label for="message" class="heading">
                        Message
                    </label>
                    <div class="col-sm-6 col-md-4" style="padding-left: 0px;">
                        <textarea id="message" name="message" rows="8" style="width: 100%;height: 50px;" disabled></textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="button" id="submit" class="btn btn-sm btn-success" onclick="submitMessage()"><i class="fa fa-dot-circle-o"></i>Submit</button>

                <button type="button" class="btn btn-sm btn-danger" onclick="cancelMessage()"><i class="fa fa-ban"></i>Clear</button>
            </div>
        </div> -->

        <div class="card">

            <form method="POST" id="filterForm">

                <div class="card-header">
                    <strong>{{ __('common.filter.title') }}</strong>
                </div>
                
                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-2">

                            <div class="form-group">
                                <label for="tier">{{ __('app.member.msg.filter.member') }}</label>
                                <input type="text" class="form-control" id="f_member" placeholder="" autocomplete="">
                            </div>

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

            <div class="card-body">
                <button type="button" id="msg-all-mb" onclick="messageAllModule(1)">{{ __('app.member.msg.message.allmember') }}</button>
                <button type="button" id="msg-selected-mb" onclick="messageAllModule(2)" >{{ __('app.member.msg.message.selectedmember') }}</button>
            </div>

            <div id="main-spinner" class="card-body"> </div>

            <div id="main-table" class="card-body"></div>

            <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>

        </div>

    </div>
</div>

<div id="modalDetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id='modal-msg-title'>{{ __('app.member.msg.message.allmember') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>            
            </div>
            <div class="modal-body">
                <div class="col-12" style="padding-left: 0px;">
                    <label for="message" class="heading">
                       {{ __('app.member.msg.message.subject') }}
                    </label>
                    <div class="col-sm-6 col-md-6" style="padding-left: 0px;">
                        <input id="subject" name="subject" type="text" class="form-control form-control-sm" >
                    </div>
                </div>
                <div class="col-12" style="padding-left: 0px;">
                    <label for="message" class="heading">
                        {{ __('app.member.msg.message.msg') }}
                    </label>
                    <div class="col-sm-6 col-md-6" style="padding-left: 0px;">
                        <textarea id="message" name="message" rows="8" class="form-control form-control-sm" style="width: 100%"></textarea>
                    </div>
                </div>
            </div>  
            <div class="modal-footer">
                <button type="button" id="submit_msg" class="btn btn-sm btn-success" onclick="submitMessage()"><i class="fa fa-dot-circle-o"></i>{{ __('app.member.msg.message.button.submit') }}</button>

                <button type="button" class="btn btn-sm btn-danger" onclick="cancelMessage()"><i class="fa fa-ban"></i>{{ __('app.member.msg.message.button.cancel') }}</button>
            </div>      
        </div>
    </div>
</div>

@endsection
