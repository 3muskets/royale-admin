@extends('layouts.app')

@section('head')

<script type="text/javascript">

    var date = utils.getToday();

    $(document).ready(function() 
    {

        prepareLocale();

        utils.createSpinner("main-spinner");

        var tier = utils.getParameterByName('tier');
        var s_date = utils.getParameterByName("s_date");
        var e_date = utils.getParameterByName("e_date");

        if(auth.getUserLevel() == 2)
        {
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
        }

        createBreadcrumbs("breadcrumb-own");

        if (tier != null) 
        {

            if('{{$upTier1}}'!= '')
            {
                createBreadcrumbs("breadcrumb-uptier1",'{{$upTierUsername1}}','{{$upTier1}}'); 
            }

            if('{{$upTier2}}'!= '')
            {
                createBreadcrumbs("breadcrumb-uptier2",'{{$upTierUsername2}}','{{$upTier2}}'); 
            }
            if('{{$upTier3}}'!= '')
            {
                createBreadcrumbs("breadcrumb-uptier3",'{{$upTierUsername3}}','{{$upTier3}}'); 
            }

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
            getMainData();
        }));

        $("#modalChangePassword").on('submit',(function(e){
            e.preventDefault();
            filterMainData();
        }));

        $("#modalEditMerchant").on('submit',(function(e){
            e.preventDefault();
            filterMainData();
        }));
    });

    function createBreadcrumbs(id, username,tier) 
    {
        var a = document.createElement("a");
        a.innerHTML = username;

        if(id == "breadcrumb-own")
        {
            a.innerHTML = document.getElementById(id).innerHTML;
            document.getElementById(id).innerHTML = "";
        }

        if (tier != null) 
        {
            a.href = "/merchants/merchant?tier="+tier;
        }
        else
        {
            a.href = "/merchants/merchant";
        }
        
        document.getElementById(id).appendChild(a);
        
        $("#" + id).addClass("d-md-block");

        $("#" + id).clone().attr('id',id + '-m').appendTo('#breadcrumb-m');

        $("#" + id + '-m').removeClass("d-none");
    }


function prepareLocale()
{
    locale['mainData.merchantcode'] = "{!! __('app.merchants.merchant.maindata.agent') !!}";
    locale['mainData.username'] = "{!! __('app.member.accounts.username') !!}";
    locale['mainData.fullname'] = "{!! __('app.merchants.merchant.maindata.alias') !!}";
    locale['mainData.dateregistered'] = "{!! __('app.merchants.merchant.maindata.dateregistered') !!}";
    locale['mainData.status'] = "{!! __('app.merchants.merchant.maindata.status') !!}";
    locale['mainData.suspended'] = "{!! __('app.merchants.merchant.maindata.suspended') !!}";
    locale['mainData.member'] = "{!! __('app.merchants.merchant.maindata.member') !!}";
    locale['mainData.member_fullname'] = "{!! __('app.merchants.merchant.maindata.fullname') !!}";
    locale['mainData.mobile'] = "{!! __('app.merchants.merchant.maindata.mobile') !!}";
    locale['mainData.memberid'] = "{!! __('app.merchants.merchant.maindata.memberid') !!}";
    locale['mainData.changepwd'] = "{!! __('app.merchants.merchant.maindata.changepassword') !!}";
    locale['mainData.comm'] = "{!! __('app.merchants.merchant.maindata.commission') !!}";
    locale['mainData.regcode'] = "{!! __('app.merchants.merchant.maindata.regcode') !!}";
    locale['mainData.evo'] = "{!! __('app.merchants.merchant.maindata.evo') !!}";
    locale['mainData.sxg'] = "{!! __('app.merchants.merchant.maindata.sxg') !!}";
    locale['mainData.wm'] = "{!! __('app.merchants.merchant.maindata.wm') !!}";
    locale['mainData.prag'] = "{!! __('app.merchants.merchant.maindata.prag') !!}";
    locale['mainData.haba'] = "{!! __('app.merchants.merchant.maindata.haba') !!}";
    locale['mainData.edit'] = "{!! __('app.merchants.merchant.maindata.edit') !!}";
    locale['mainData.action'] = "{!! __('app.merchants.merchant.maindata.action') !!}";

    locale['tooltip.edit'] = "{!! __('app.merchants.merchant.maindata.edit') !!}";;
    locale['tooltip.changepassword'] = "{!! __('app.merchants.merchant.maindata.changepassword') !!}";

    locale['mainData.member'] = "{!! __('app.member.accounts.member') !!}";
    locale['mainData.level'] = "Member Level";
    locale['mainData.wallet'] = "Crypto Wallet";
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
    locale['mainData.duplicate_ip'] = "Duplicate IP";

    locale['info'] = "{!! __('common.modal.info') !!}";
    locale['success'] = "{!! __('common.modal.success') !!}";
    locale['error'] = "{!! __('common.modal.error') !!}";

}



var mainData;
var table;
var refreshMainData = false;

function getMainData() 
{
    var containerId = "main-table";

    $("#main-spinner").show();
    $("#main-table").hide();
    $("#notes").hide();

    var data = utils.getDataTableDetails(containerId);

    data["username"] = $("#f_username").val();
    data["tier"] = utils.getParameterByName('tier');
    data["slast_login"] = $("#slast_login1").val();
    data["elast_login"] = $("#elast_login1").val();
    data["slast_deposit"] = $("#slast_deposit1").val();
    data["elast_deposit"] = $("#elast_deposit1").val();
    data["start_date"] = $("#s_date1").val();
    data["end_date"] = $("#e_date1").val();

    $.ajax({
        type: "GET",
        url: "/ajax/merchants/merchant/list",
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

            if(auth.getUserLevel() == 3 || '{{$levelByTier}}' == 3)
            {
                loadMemberMainData(containerId);   
            }
            else
            {
                loadAdminMainData(containerId);
            }

            
        }
    });
}

function loadAdminMainData(containerId)
{

    $("#main-spinner").hide();
    $("#main-table").show();


    var fields = [   
                    ["username",locale['mainData.merchantcode'],true,false]                        
                    ,["fullname",locale['mainData.fullname'],true,false]
                    ,["status",locale['mainData.status'],true,false]
                    ,["suspended",locale['mainData.suspended'],true,false]
                    ,["created_at",locale['mainData.dateregistered'],true,false]
                    @can('permissions.edit_downline_list') 
                    ,["edit",locale['mainData.edit'],false,false]
                    @endcan
                    ,["comm",locale['mainData.comm'],true,false]
                    ,["reg_cd",locale['mainData.regcode'],false,false]
                    ,["evo_pt",locale['mainData.sxg'],true,false]
                    ,["haba_pt",locale['mainData.haba'],true,false]
                    ,["prag_pt",locale['mainData.prag'],true,false]
                    ,["wm_pt",locale['mainData.wm'],true,false]
                    @can('permissions.edit_downline_list') 
                    ,["",locale['mainData.action'],false,false] 
                    @endcan                   
                    ]; 


    if(utils.getParameterByName('tier'))
    {


        var fields = [   
                        ["username",locale['mainData.merchantcode'],true,false]                        
                        ,["fullname",locale['mainData.fullname'],true,false]
                        ,["status",locale['mainData.status'],false,false]
                        ,["suspended",locale['mainData.suspended'],false,false]
                        ,["created_at",locale['mainData.dateregistered'],true,false]
                        ,["comm",locale['mainData.comm'],true,false]
                        ,["reg_cd",locale['mainData.regcode'],false,false]
                        ,["evo_pt",locale['mainData.sxg'],true,false]
                        ,["haba_pt",locale['mainData.haba'],true,false]
                        ,["prag_pt",locale['mainData.prag'],true,false]
                        ,["wm_pt",locale['mainData.wm'],true,false]             
                        ]; 


    }



    if(auth.getUserLevel() != 2 && '{{$levelByTier}}' != 2)
    {    

        for(var i = fields.length-1 ; i > 0; i--)
        {
            if(fields[i][0] == "comm" || fields[i][0] == "reg_cd" )
            {
                fields.splice(i,1);
            }
        }
         
    } 

    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $("#notes").show();
        

        var fieldMerchant = utils.getDataTableFieldIdx("username",fields);
        var fieldStatus = utils.getDataTableFieldIdx("status",fields);
        var fieldSuspended= utils.getDataTableFieldIdx("suspended",fields);
        var fieldEvoPt = utils.getDataTableFieldIdx("evo_pt",fields);
        var fieldHabaPt = utils.getDataTableFieldIdx("haba_pt",fields);
        var fieldPragPt = utils.getDataTableFieldIdx("prag_pt",fields);
        var fieldWmPt = utils.getDataTableFieldIdx("wm_pt",fields);
        var fieldEdit = utils.getDataTableFieldIdx("edit",fields);
        var fieldActions = utils.getDataTableFieldIdx("",fields);


        for (var i = 1, row; row = table.rows[i]; i++) 
        {   
            var merchant = mainData.results[i - 1]["username"];
            var id = mainData.results[i - 1]["id"];
            var evoPt = mainData.results[i - 1]["evo_pt"];
            var habaPt = mainData.results[i - 1]["haba_pt"];
            var pragPt = mainData.results[i - 1]["prag_pt"];
            var wmPt = mainData.results[i - 1]["wm_pt"];


            //username
            var a = document.createElement("a");
            a.href = "/merchants/merchant?tier=" + id;
            a.innerHTML = merchant;

            row.cells[fieldMerchant].innerHTML =  "";
            row.cells[fieldMerchant].appendChild(a);

            //given pt by all provider
            row.cells[fieldEvoPt].innerHTML = parseFloat(evoPt);
            row.cells[fieldHabaPt].innerHTML = parseFloat(habaPt);
            row.cells[fieldPragPt].innerHTML = parseFloat(pragPt);
            row.cells[fieldWmPt].innerHTML = parseFloat(wmPt);


            if(evoPt == null)
            {
                row.cells[fieldEvoPt].innerHTML  = "-";

            }
            if(habaPt == null)
            {
                row.cells[fieldHabaPt].innerHTML  = "-";
            }
            if(pragPt == null)
            {
                row.cells[fieldPragPt].innerHTML  = "-";
            } 
            if(wmPt == null)
            {
                row.cells[fieldWmPt].innerHTML  = "-";
            }

            //status
            if(mainData.results[i - 1]["status"] == "a")
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-success">'+mainData.results[i - 1]["status_desc"] +'</span>';
            else 
                row.cells[fieldStatus].innerHTML = '<span class="badge badge-warning">'+mainData.results[i - 1]["status_desc"] +'</span>';

            //suspended
            if(mainData.results[i - 1]["suspended"] == "0")
                row.cells[fieldSuspended].innerHTML = '<span class="badge badge-success">'+mainData.results[i - 1]["suspended_desc"] +'</span>';
            else 
                row.cells[fieldSuspended].innerHTML = '<span class="badge badge-warning">'+mainData.results[i - 1]["suspended_desc"] +'</span>';

             @can('permissions.edit_downline_list') 

            if(!utils.getParameterByName('tier'))
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

                //change password            
                var imgChange = document.createElement("img");
                imgChange.onclick = showPasswordModal;
                imgChange.style.cursor="pointer";
                imgChange.setAttribute("class","left");
                imgChange.setAttribute("data-toggle", "tooltip");
                imgChange.setAttribute("title", locale['tooltip.changepassword']);
                imgChange.rowId = i;
                imgChange.src="/images/icon/icon-pw4.png";
                imgChange.style.height = '30px';
                imgChange.style.width = '30px';
                imgChange.style.margin = "0 15px 0 0";

                row.cells[fieldActions].innerHTML = '';
                row.cells[fieldActions].appendChild(imgChange);

            }

            @endcan

        }  
    }
}

function loadMemberMainData(containerId)
{
    
    $("#main-spinner").hide();
    $("#main-table").show();

   //fields for the first row
    fields1 = [
                    ["",locale['mainData.memberid'],true,false]                        
                    ,["",locale['mainData.username'],true,false]
                    ,["",locale['mainData.credit'],true,false]
                    ,["", locale['mainData.status'],true, false]
                    ,["", locale['mainData.suspended'],true, false]
                    ,["member",locale['mainData.member'],false,true]
                    ,["",locale['mainData.bank'],false,true]
                    ,["",locale['mainData.wallet'],true,false]
                    ,["",locale['mainData.dateregistered'],true,false]
                    ,["",locale['mainData.last_login'],true,false]
                    ,["",locale['mainData.last_ip'],true,false]
                    ,["",locale['mainData.duplicate_ip'],true,false]
                    ,["",locale['mainData.member_deposit'],true,false]
                    ,["",locale['mainData.admin_deposit'],true,false]
                    ,["",locale['mainData.member_withdraw'],true,false]
                    ,["",locale['mainData.admin_withdraw'],true,false]
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
                    ,["available",locale['mainData.credit'],false,true]
                    ,["status", locale['mainData.status'],false, false]
                    ,["suspended", locale['mainData.suspended'],false, false]
                    ,["mobile",locale['mainData.mobile'],false,false]
                    ,["email",locale['mainData.email'],false,false]
                    ,["bank",locale['mainData.bankname'],false,false]
                    ,["acc_no",locale['mainData.bankacc'],false,false]
                    ,["name",locale['mainData.bankaddress'],false,false]
                    ,["wallet_address",locale['mainData.wallet'],false,false]
                    ,["created_at",locale['mainData.dateregistered'],true,false]
                    ,["last_login",locale['mainData.last_login'],false,false]
                    ,["last_ip",locale['mainData.last_ip'],false,false]
                    ,["is_duplicate_ip",locale['mainData.duplicate_ip'],false,false]
                    ,["member_deposit",locale['mainData.member_deposit'],false,false]
                    ,["admin_deposit",locale['mainData.admin_deposit'],false,false]
                    ,["member_withdraw",locale['mainData.member_withdraw'],false,false]
                    ,["admin_withdraw",locale['mainData.admin_withdraw'],false,false]
                    ,["unread_msg",locale['mainData.unread_msg'],false,false]

                ];



    table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

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
        
        var fieldMerchant = utils.getDataTableFieldIdx("username",fields);
        var fieldStatus = utils.getDataTableFieldIdx("status",fields);
        var fieldSuspended= utils.getDataTableFieldIdx("suspended",fields);
        var fieldDuplicate = utils.getDataTableFieldIdx("is_duplicate_ip", fields);
        var fieldCredit= utils.getDataTableFieldIdx("available",fields);

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

        }  
    }
}


function showEditModal() 
{
    var id = mainData.results[this.rowId - 1]["id"];
    var merchantCode = mainData.results[this.rowId - 1]["username"];
    var merchantName = mainData.results[this.rowId - 1]["fullname"];
    var status = mainData.results[this.rowId - 1]["status"];
    var suspended = mainData.results[this.rowId - 1]["suspended"];
    var evoPt = mainData.results[this.rowId - 1]["evo_pt"];
    var habaPt = mainData.results[this.rowId - 1]["haba_pt"];
    var pragPt = mainData.results[this.rowId - 1]["prag_pt"];
    var wmPt = mainData.results[this.rowId - 1]["wm_pt"];
    var maxEvoPt = mainData.results[this.rowId - 1]["maxEvoPt"];
    var maxHabaPt = mainData.results[this.rowId - 1]["maxHabaPt"];
    var maxPragPt = mainData.results[this.rowId - 1]["maxPragPt"];
    var maxWmPt = mainData.results[this.rowId - 1]["maxWmPt"];

    $("#id").val(id);
    $("#fullname").val(merchantName);
    $("#merchant_code").val(merchantCode);

    //prevent duplicate option 
    $('#status').append('<option>' + '</option>').children().remove();  
    $('#suspended').append('<option>' + '</option>').children().remove();  
    $('#evo_pt').append('<option>' + '</option>').children().remove();  
    $('#haba_pt').append('<option>' + '</option>').children().remove();  
    $('#prag_pt').append('<option>' + '</option>').children().remove();  
    $('#wm_pt').append('<option>' + '</option>').children().remove();  
    

    for (j = 0; j <= maxEvoPt; j += 0.5) 
    {
        $('#evo_pt').append($('<option />').attr('value', j).html(j));
    }
    for (j = 0; j <= maxHabaPt; j += 0.5) 
    {
        $('#haba_pt').append($('<option />').attr('value', j).html(j));
    }
    for (j = 0; j <= maxPragPt; j += 0.5) 
    {
    $('#prag_pt').append($('<option />').attr('value', j).html(j));
    }
        for (j = 0; j <= maxWmPt; j += 0.5) 
    {
        $('#wm_pt').append($('<option />').attr('value', j).html(j));
    }

    $('#status').append('{{ Helper::generateOptions($optionsStatus,'') }}');
    $('#suspended').append('{{ Helper::generateOptions($optionsSuspended,'') }}');

    //set the given pt before changed as default value 
    document.getElementById("evo_pt").value = parseFloat(evoPt);
    document.getElementById("haba_pt").value = parseFloat(habaPt);
    document.getElementById("prag_pt").value = parseFloat(pragPt);
    document.getElementById("wm_pt").value = parseFloat(wmPt);
    document.getElementById("status").value = status;
    document.getElementById("suspended").value = suspended;


    // logging
    var log_data = '{"merchant":"'+merchantCode+'","fullname":"'+merchantName+'","status":"'+status+'","evoPt":"'+evoPt+'","habaPt":"'+habaPt+'","pragPt":"'+pragPt+'","wmPt":"'+wmPt+'"}';
    $("#log_old").val(log_data);

    if(auth.getUserLevel() == 2)
    {
        var comm = mainData.results[this.rowId - 1]["comm"];
        var regCode = mainData.results[this.rowId - 1]["reg_cd"];

        $("#reg_cd").val(regCode);

        //prevent duplicate option 
        $('#comm').append('<option>' + '</option>').children().remove();  

        for (j = 0; j <= 50; j ++) 
        {
            $('#comm').append($('<option />').attr('value', j).html(j));
        }

        document.getElementById("comm").value = parseFloat(comm);

        // logging
        var log_data = '{"merchant":"'+merchantCode+'","fullname":"'+merchantName+'","status":"'+status+'","evoPt":"'+evoPt+'","habaPt":"'+habaPt+'","pragPt":"'+pragPt+'","wmPt":"'+wmPt+'","commission":"'+comm+'"}';
        $("#log_old").val(log_data);    
    }

    $("#modalMessage").hide();
    $("#modalEditMerchant").modal('show');

    refreshMainData = false;
}

function submitMerchantDetails()
{
    $("#modalMessage").hide();

    utils.startLoadingBtn("btnSubmitDetails","modalEditMerchant");

    $("#formEditMerchant").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/merchants/merchant/update",
        data:  new FormData($("#formEditMerchant")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnSubmitDetails","modalEditMerchant");

            var obj = JSON.parse(data);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

                $('#status').html('');
                $("#modalEditMerchant").modal('hide');

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
    var userId = mainData.results[this.rowId - 1]["id"];
    var username = mainData.results[this.rowId - 1]["merchant"];

    $("#modal-id").val(userId);
    $("#username").val(username);
    $("#formChangePassword")[0].reset();

    $("#modalMessage2").hide();
    $("#modalChangePassword").modal('show');

    refreshMainData = false;
}

function submitPassword()
{
    utils.startLoadingBtn("btnSubmitPassword","modalChangePassword"); 
    $("#modalMessage2").hide();

    

    $("#formChangePassword").attr("enabled",0);

    $.ajax({
        url: "/ajax/merchants/merchant/change_password",
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

function checkAll(e)
{
    var checked = e.checked;
    if (checked) 
    {
        $('input[name="check[]"]').prop('checked', true);
    } 
    else 
    {
        $('input[name="check[]"]').prop('checked', false);
    }
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

<style type="text/css">
    .right
    {
        float: right;

        margin-left: 15px;
    }
</style>
@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.merchants.merchant.breadcrumb.agentmanagement') }}</li>
     <li class="breadcrumb-item">{{ __('app.merchants.merchant.breadcrumb.agentlist') }}</li>
    <li id="breadcrumb-own" class="breadcrumb-item d-none d-md-block">{{ Auth::user()->username }}</li>
    <li id="breadcrumb-uptier3" class="breadcrumb-item d-none"></li>
    <li id="breadcrumb-uptier2" class="breadcrumb-item d-none"></li>
    <li id="breadcrumb-uptier1" class="breadcrumb-item d-none"></li>
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
                                @if($levelByTier == '3' || $userLevel == '3')
                                <label for="merchantcode">{{ __('app.merchants.merchant.filter.member') }}</label>
                                @else
                                <label for="merchantcode">{{ __('app.merchants.merchant.filter.agent') }}</label>
                                @endif
                                <input type="text" class="form-control" id="f_username" autocomplete="off" placeholder="">
                            </div>

                        </div>
                        @if($levelByTier == '2' || $userLevel == '2' || $levelByTier == '3' || $userLevel == '3')
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
                        @endif
                    </div>

                    @if($levelByTier == '3' || $userLevel == '3')
                        <div class="row">
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
                        </div>
                        @endif
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

<div id="modalEditMerchant" class="modal fade" role="dialog">
    <div class="modal-dialog modal-primary" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('app.merchants.merchant.maindata.edit.title') }}</h4>
                <button class="close" id="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">

                <div class="card">

                    <div id="modalMessage" class="card-header"></div>

                    <form method="POST" id="formEditMerchant">

                       <input type="hidden" id="log_old" name="log_old">
                       <input type="hidden" id="id" name="id" class="form-control">

                       <div class="card-body">

                        <div class="row">
                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.agent') }}</label>
                                    <input type='text' id="merchant_code" name="merchant_code" autocomplete="off" class="form-control" readonly="readonly">
                                </div>

                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.sxg') }}</label>
                                    <select class="form-control" id="evo_pt" name="evo_pt">
                                    </select>
                                </div>

                            </div>

                        </div>

                        <div class="row">
                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.alias') }}</label>
                                    <input type='text' id="fullname" name="fullname" class="form-control" autocomplete="off">
                                </div>

                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.haba') }}</label>
                                    <select class="form-control" id="haba_pt" name="haba_pt">

                                    </select>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.status') }}</label>
                                    <select class="form-control" id="status" name="status">

                                    </select>
                                </div>

                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.prag') }}</label>
                                    <select class="form-control" id="prag_pt" name="prag_pt">

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.suspended') }}</label>
                                    <select class="form-control" id="suspended" name="suspended">

                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.wm') }}</label>
                                    <select class="form-control" id="wm_pt" name="wm_pt">

                                    </select>
                                </div>

                            </div>
                        </div>
                        @if($userLevel == '2')
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.commission') }}</label>
                                    <select class="form-control" id="comm" name="comm">

                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('app.merchants.merchant.maindata.regcode') }}</label>
                                    <input type='text' id="reg_cd" name="reg_cd" class="form-control" autocomplete="off">
                                </div>

                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="card-footer">

                        <button id="btnSubmitDetails" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitMerchantDetails()">
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
                <h4 class="modal-title">{{ __('app.merchants.merchant.maindata.changepassword') }}</h4>
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
                                        <label>{{ __('app.merchants.merchant.maindata.password') }}</label>
                                        <input type="password" id="new_password" name="password" class="form-control" autocomplete="off">
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="card-footer">

                            <button id="btnSubmitPassword" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="submitPassword()">
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
