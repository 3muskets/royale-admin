@extends('layouts.app')

@section('head')

<script type="text/javascript">

var date = utils.getToday();

$(document).ready(function() 
{
    prepareLocale();

    utils.createSpinner("main-spinner");

    var s_date = utils.getParameterByName("s_date");
    var e_date = utils.getParameterByName("e_date");

    if(s_date)
    {
        $("#s_date").val(utils.formattedDate(s_date));
        $("#s_date1").val(s_date);
    }

    if(e_date)
    {
         $("#e_date").val(utils.formattedDate(e_date));
        $("#e_date1").val(e_date);
    }

    getMainData();

    utils.datepickerStart('slast_login','elast_login','slast_login1','');
    utils.datepickerEnd('slast_login','elast_login','elast_login1','');

    utils.datepickerStart('slast_deposit','elast_deposit','slast_deposit1','');
    utils.datepickerEnd('slast_deposit','elast_deposit','elast_deposit1','');

    utils.datepickerStart('s_date','e_date','s_date1','');
    utils.datepickerEnd('s_date','e_date','e_date1','');

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    $("#formEdit").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    $("#formChangePassword").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));
});

function prepareLocale()
{
    locale['mainData.memberid'] = "{!! __('app.member.accounts.memberid') !!}";
    locale['mainData.username'] = "{!! __('app.member.accounts.username') !!}";
    locale['mainData.dateregistered'] = "{!! __('app.member.accounts.dateregistered') !!}";
    locale['mainData.status'] = "{!! __('app.member.accounts.status') !!}";
    locale['mainData.suspended'] = "{!! __('app.member.accounts.suspended') !!}";
    locale['mainData.fullname'] = "{!! __('app.member.accounts.fullname') !!}";
    locale['mainData.mobile'] = "{!! __('app.member.accounts.mobile') !!}";
    locale['mainData.edit'] = "{!! __('app.member.accounts.edit') !!}";
    locale['mainData.action'] = "{!! __('app.member.accounts.action') !!}";

    locale['mainData.member'] = "{!! __('app.member.accounts.member') !!}";
    locale['mainData.credit'] = "{!! __('app.member.accounts.credit') !!}";
    locale['mainData.bank'] = "{!! __('app.member.accounts.bank') !!}";
    locale['mainData.email'] = "{!! __('app.member.accounts.email') !!}";    
    locale['mainData.bankname'] = "{!! __('app.member.accounts.bankname') !!}";
    locale['mainData.bankacc'] = "{!! __('app.member.accounts.bankacc') !!}";
    locale['mainData.bankaddress'] = "Account Name";
    locale['mainData.last_login'] = "{!! __('app.member.accounts.last_login') !!}";
    locale['mainData.last_ip'] = "{!! __('app.member.accounts.last_ip') !!}";
    locale['mainData.member_deposit'] = "{!! __('app.member.accounts.member_deposit') !!}";
    locale['mainData.member_withdraw'] = "{!! __('app.member.accounts.member_withdraw') !!}";
    locale['mainData.admin_deposit'] = "{!! __('app.member.accounts.admin_deposit') !!}";
    locale['mainData.admin_withdraw'] = "{!! __('app.member.accounts.admin_withdraw') !!}";
    locale['mainData.unread_msg'] = "{!! __('app.member.accounts.unread_msg') !!}";
    locale['mainData.level'] = "Member Level";
    locale['mainData.wallet'] = "Crypto Wallet";
    locale['mainData.admin'] = "Company/Agent";
    locale['mainData.duplicate_ip'] = "Duplicate IP";
    locale['mainData.type'] = "Admin Type";


    locale['tooltip.edit'] = "{!! __('app.tooltip.edit') !!}";
    locale['tooltip.changepassword'] = "{!! __('app.tooltip.changepassword') !!}";
    locale['mainData.alias'] = "{!! __('app.member.accounts.alias') !!}";

    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";

    locale['mainData.level.new'] = "New";
    locale['mainData.level.regular'] = "Regular";
    locale['mainData.level.bronze'] = "Bronze";
    locale['mainData.level.silver'] = "Silver";
    locale['mainData.level.gold'] = "Gold";
    locale['mainData.level.platinum'] = "Platinum";


}

var mainData;
var refreshMainData = false;

function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();

    var data = utils.getDataTableDetails(containerId);

    data["username"] = $("#f_username").val();
    data["slast_login"] = $("#slast_login1").val();
    data["elast_login"] = $("#elast_login1").val();
    data["slast_deposit"] = $("#slast_deposit1").val();
    data["elast_deposit"] = $("#elast_deposit1").val();
    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();

    $.ajax({
        type: "GET",
        url: "/ajax/merchants/merchant/member",
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
    $("#main-spinner").hide();
    $("#main-table").show();

    //fields for the first row
    fields1 = [
                    ["",locale['mainData.memberid'],true,false]                        
                    ,["",locale['mainData.username'],true,false]
                    ,["",locale['mainData.level'],true,false]
                    ,["",locale['mainData.credit'],true,false]
                    ,["", locale['mainData.status'],true, false]
                    ,["", locale['mainData.suspended'],true, false]
                    ,["", locale['mainData.edit'],true, false]
                    ,["", locale['mainData.action'],true, false]
                    ,["member",locale['mainData.member'],false,true]
                    ,["",locale['mainData.bank'],false,true]
                    /*,["",locale['mainData.wallet'],true,false]*/
                    ,["",locale['mainData.dateregistered'],true,false]
                    ,["",locale['mainData.last_login'],true,false]
                    ,["",locale['mainData.last_ip'],true,false]
                    ,["",locale['mainData.duplicate_ip'],true,false]
/*                    ,["",locale['mainData.member_deposit'],true,false]
                    ,["",locale['mainData.admin_deposit'],true,false]
                    ,["",locale['mainData.member_withdraw'],true,false]
                    ,["",locale['mainData.admin_withdraw'],true,false]*/
                    ,["",locale['mainData.unread_msg'],true,false]
               
                ];

    fields2 = [     
                    ["",locale['mainData.mobile']]
                    ,["", locale['mainData.email']]
                    ,["",locale['mainData.bankname']]
                    ,["", locale['mainData.bankacc']]
                    ,["",locale['mainData.bankaddress']]

                ];

    var fields = [   
                    ["id",locale['mainData.memberid'],true,false]                        
                    ,["username",locale['mainData.username'],true,false]
                    ,["level_id",locale['mainData.level'],true,false]
                    ,["available",locale['mainData.credit'],false,true]
                    ,["status", locale['mainData.status'],false, false]
                    ,["suspended", locale['mainData.suspended'],false, false]
                    ,["edit", locale['mainData.edit'],false, false]
                    ,["", locale['mainData.action'],false, false]
                    ,["mobile",locale['mainData.mobile'],false,false]
                    ,["email",locale['mainData.email'],false,false]
                    ,["bank",locale['mainData.bankname'],false,false]
                    ,["acc_no",locale['mainData.bankacc'],false,false]
                    ,["name",locale['mainData.bankaddress'],false,false]
                    /*,["wallet_address",locale['mainData.wallet'],false,false]*/
                    ,["created_at",locale['mainData.dateregistered'],true,false]
                    ,["last_login",locale['mainData.last_login'],false,false]
                    ,["last_ip",locale['mainData.last_ip'],false,false]
                    ,["is_duplicate_ip",locale['mainData.duplicate_ip'],false,false]
                    ,["unread_msg",locale['mainData.unread_msg'],false,false]

                ];

    if(auth.getUserLevel() == 3)
    {    
        for(var i = fields.length-1 ; i > 0; i--)
        {
            if(fields[i][0] == "admin")
            {
                fields.splice(i,1);
            }
        }

        for(var i = fields1.length-1 ; i > 0; i--)
        {
            if(fields1[i][0] == "admin")
            {
                fields1.splice(i,1);
            }
        }
         
    }

    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();

        //create header
        var tHead = table.createTHead();
        var row = tHead.insertRow(0); 
        var row1 = tHead.insertRow(1); 

        //header - row 1
        for(var j = 0; j < fields1.length; j++)
        {
            var fieldName = fields1[j][0];
            var fieldTitle = fields1[j][1];
            var fieldRowSpan= fields1[j][2];
            var fieldColSpan= fields1[j][3];

            var th = document.createElement('th');

            th.style.textAlign = 'center'; 
            th.innerHTML = fieldTitle;  

            if(fieldRowSpan == true)
            {
                th.setAttribute('rowspan',2);
                th.style.padding = '0 0 20px 0';
            }

            if(fieldColSpan == true)
            {
                if(fields1[j][0] == 'member')
                {
                    th.setAttribute('colspan',2);
                }
                else
                {
                    th.setAttribute('colspan',3);
                }
            }
            
            row.appendChild(th);
        }

        //header - row 2
        for (var i = 0; i < fields2.length; i++)
        {
            var fieldName1 = fields2[i][0];
            var fieldTitle1 = fields2[i][1];
            var fieldTable1 = fields2[i][2];

            var th1 = document.createElement('th');
            th1.innerHTML = fieldTitle1; 
            th1.style.textAlign = 'center';  

            row1.appendChild(th1);
        }

        table.rows[2].style.display = "none";

        var fieldStatus = utils.getDataTableFieldIdx("status", fields);
        var fieldSuspended = utils.getDataTableFieldIdx("suspended", fields);
        var fieldDuplicate = utils.getDataTableFieldIdx("is_duplicate_ip", fields);
        var fieldEdit = utils.getDataTableFieldIdx("edit", fields);
        var fieldAction = utils.getDataTableFieldIdx("", fields);
        var fieldCredit= utils.getDataTableFieldIdx("available",fields);

        var fieldLevel = utils.getDataTableFieldIdx("level_id", fields);

        for (var i = 3, row; row = table.rows[i]; i++) 
        {   
            var available = mainData.results[i - 3]["available"];
            row.cells[fieldCredit].innerHTML =  utils.formatMoney(available);
            
            if(mainData.results[i-3]["status"] == 'a')
            {
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+mainData.results[i-3]["status_desc"]+'</span>';
            }
            else
            {
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+mainData.results[i-3]["status_desc"]+'</span>';
            }

            if (mainData.results[i-3]["suspended"] == '0') 
            {
                row.cells[fieldSuspended].innerHTML = '<span class="badge badge-success">'+mainData.results[i-3]["suspended_desc"]+'</span>';
            }
            else
            {
                row.cells[fieldSuspended].innerHTML = '<span class="badge badge-warning">'+mainData.results[i-3]["suspended_desc"]+'</span>';
            }

            if (mainData.results[i-3]["is_duplicate_ip"] == '0') 
            {
                row.cells[fieldDuplicate].innerHTML = '<span class="badge badge-success">'+mainData.results[i-3]["duplicate_desc"]+'</span>';
            }
            else
            {
                row.cells[fieldDuplicate].innerHTML = '<span class="badge badge-warning">'+mainData.results[i-3]["duplicate_desc"]+'</span>';
            }

            var level = mainData.results[i - 3]["level_id"];


            if (level == 1)
                row.cells[fieldLevel].innerHTML =  locale['mainData.level.new'];
            else if (level == 2)
                row.cells[fieldLevel].innerHTML =  locale['mainData.level.regular'];
            else if (level == 3)
                row.cells[fieldLevel].innerHTML =  locale['mainData.level.bronze'];
            else if (level == 4)
                row.cells[fieldLevel].innerHTML =  locale['mainData.level.silver'];
            else if (level == 5)
                row.cells[fieldLevel].innerHTML =  locale['mainData.level.gold'];
            else if (level == 6)
                row.cells[fieldLevel].innerHTML =  locale['mainData.level.platinum'];


            //Edit button
            var btnEdit = document.createElement("i");
            btnEdit.className = "fa fa-edit fa-2x";
            btnEdit.onclick = showEditModal;
            btnEdit.rowId = i;
            btnEdit.style.cursor = "pointer";
            btnEdit.style.color = "#11acf4";
            btnEdit.setAttribute("data-toggle", "tooltip");
            btnEdit.setAttribute("title", locale['tooltip.edit']);
            row.cells[fieldEdit].innerHTML = "";
            row.cells[fieldEdit].className = "pb-0";

            //Action button- change pwd
            var imgChange = document.createElement("img");
            // imgChange.onclick = showPasswordModal;
            imgChange.style.cursor="pointer";
            imgChange.setAttribute("class","left");
            imgChange.setAttribute("data-toggle", "tooltip");
            imgChange.setAttribute("title", locale['tooltip.changepassword']);
            imgChange.rowId = i;
            imgChange.src="/images/icon/icon-pw4.png";
            imgChange.style.height = '30px';
            imgChange.style.width = '30px';
            imgChange.style.margin = "0 15px 0 0";
            imgChange.onclick = showPasswordModal;
            row.cells[fieldAction].innerHTML = "";

            if(auth.getUserLevel() == mainData.results[i - 3]["level"] || auth.getUserLevel() == 0)
            {
                row.cells[fieldAction].appendChild(imgChange);
                row.cells[fieldEdit].appendChild(btnEdit);
            }
        }
    }
}

function showEditModal() 
{
    var id = mainData.results[this.rowId - 3]["id"];
    var username = mainData.results[this.rowId - 3]["username"];
    var status = mainData.results[this.rowId - 3]["status"];
    var suspended = mainData.results[this.rowId - 3]["suspended"];
    
    $('#status').append('<option>' + '</option>').children().remove();  
    $('#suspended').append('<option>' + '</option>').children().remove();  

    //logging
    var log_data = '{"username":"'+username+'","status":"'+status+'","suspended":"'+suspended+'"}';
    $("#log_old").val(log_data);

    $('#status').append('{{ Helper::generateOptions($optionsStatus,'') }}');
    $('#suspended').append('{{ Helper::generateOptions($optionsSuspended,'') }}');

    $("#id").val(id);
    $("#username").val(username);
    $("#status").val(status);
    $("#suspended").val(suspended);

    $("#modalMessage").hide();
    $("#modalEdit").modal('show');

    refreshMainData = false;
}

function submitDetails()
{
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitDetails","modalEdit");

    $("#formEdit").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/merchants/merchant/member/update",
        data:  new FormData($("#formEdit")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnSubmitDetails","modalEdit");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $('#status').html('');
                $("#modalEdit").modal('hide');

            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onModalEditDismissError);

            }
        },
        error: function(){}       
    });
}

function showPasswordModal()
{
    var userId = mainData.results[this.rowId - 3]["id"];

    $("#modal-id").val(userId);
    $("#formChangePassword")[0].reset();

    $("#modalMessage2").hide();
    $("#modalChangePassword").modal('show');

    refreshMainData = false;
}

function submitPassword()
{ 
    $("#modalMessage2").hide();

    utils.startLoadingBtn("btnSubmitPassword","modalChangePassword");

    $("#formChangePassword").attr("enabled",0);

    $.ajax({
        url: "/ajax/merchants/merchant/member/change_password",
        type: "POST",
        data:  new FormData($("#formChangePassword")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {

            utils.stopLoadingBtn("btnSubmitPassword","modalChangePassword");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;

                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $("#modalChangePassword").modal('hide');
            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,onModalPasswordDismissError);
                $("#new_password").val("");
            }
        },
        error: function(){}             
    }); 
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

function onModalPasswordDismissError()
{
    $("#formChangePassword").attr("enabled",1);
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
    $("#f_username").val("");
    $("#slast_login, #slast_login1,#elast_login, #elast_login1").val("");
    $("#slast_deposit, #slast_deposit1,#elast_deposit, #elast_deposit1").val("");
    $("#s_date, #s_date1, #e_date, #e_date1").val("");

    filterMainData();
}

</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.member.accounts.breadcrumb.membermanagement') }}</li>
    <li class="breadcrumb-item active">{{ __('app.member.accounts.breadcrumb.memberlist') }}</li>
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
                                <label for="merchantcode">{{ __('app.member.accounts.filter.name') }}</label>
                                <input type="text" class="form-control" id="f_username" placeholder="" autocomplete="">
                            </div>

                        </div>

                       <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{!! __('app.member.accounts.dateregistered') !!}</label>
                                <input type="text" class="form-control" name="s_date" id="s_date" placeholder="dd/mm/yyyy" autocomplete="">
                                <input type="hidden" name="s_date1" id="s_date1">
                            </div>

                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>~</label>
                                <input type="text" class="form-control" name="e_date" id="e_date" placeholder="dd/mm/yyyy" autocomplete="">
                                <input type="hidden" name="e_date1" id="e_date1">
                            </div>

                        </div>
                    </div>

<!--                     <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('app.member.accounts.filter.last_login') }}</label>
                                <input type="text" class="form-control" name="slast_login" id="slast_login" placeholder="dd/mm/yyyy" autocomplete="">
                                <input type="hidden" name="slast_login1" id="slast_login1">
                            </div>

                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>~</label>
                                <input type="text" class="form-control" name="elast_login" id="elast_login" placeholder="dd/mm/yyyy" autocomplete="">
                                <input type="hidden" name="elast_login1" id="elast_login1">
                            </div>

                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="name">{{ __('app.member.accounts.filter.admin_deposit') }}</label>
                                <input type="text" class="form-control" name="slast_deposit" id="slast_deposit" placeholder="dd/mm/yyyy" autocomplete="">
                                <input type="hidden" name="slast_deposit1" id="slast_deposit1">
                            </div>

                        </div>

                        <div class="col-sm-2">
                            <div class="form-group">
                                <label>~</label>
                                <input type="text" class="form-control" name="elast_deposit" id="elast_deposit" placeholder="dd/mm/yyyy" autocomplete="">
                                <input type="hidden" name="elast_deposit1" id="elast_deposit1">
                            </div>

                        </div>
                    </div> -->
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

<div id="modalEdit" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.member.accounts.edit.title') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formEdit">

                       <input type="hidden" id="log_old" name="log_old">
                       <input type="hidden" id="id" name="id" class="form-control">

                       <div class="card-body">

                        <div class="row">
                            <div class="col-sm-8">

                                <div class="form-group">
                                    <label>{{ __('app.member.accounts.username') }}</label>
                                    <input type='text' id="username" name="username" autocomplete="off" class="form-control" readonly="readonly">
                                </div>

                            </div>

                        </div>

                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label>{{ __('app.member.accounts.status') }}</label>
                                    <select class="form-control" id="status" name="status">

                                    </select>
                                </div>

                            </div>

                        </div>

                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label>{{ __('app.member.accounts.suspended') }}</label>
                                    <select class="form-control" id="suspended" name="suspended">

                                    </select>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="card-footer">

                        <button id="btnSubmitDetails" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitDetails()">
                            <i class="fa fa-dot-circle-o"></i> {{ __('common.modal.submit') }}
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>
</div>
</div>

<div id="modalChangePassword" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.member.accounts.changepassword') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage2" class="card-header"></div>

                    <form method="POST" id="formChangePassword">

                        <input type="hidden" id="modal-id" name="id">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-8">

                                    <div class="form-group">
                                        <label>{{ __('app.member.accounts.changepassword.title') }}</label>
                                        <input type="password" id="new_password" name="password" class="form-control" autocomplete="off">
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="card-footer">

                            <button id="btnSubmitPassword" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitPassword()">
                                <i class="fa fa-dot-circle-o"></i>  {{ __('common.modal.submit') }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection
