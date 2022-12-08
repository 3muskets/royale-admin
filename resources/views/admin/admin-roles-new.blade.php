@extends('layouts.app')

@section('head')

<script type="text/javascript">

    $(document).ready(function() 
    {
        prepareLocale();

        $("#mainForm").attr("enabled",1);

        $("#mainForm").on('submit',(function(e){
            e.preventDefault();
            submitMainForm();
        }));

    });

    function prepareLocale()
    {
        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";
    }

    function submitMainForm()
    {   
        if($("#mainForm").attr("enabled") == 0)
        {
            return;
        }

        $("#mainForm").attr("enabled",0);

        var data  = {};
        var check = [];

        $.each($("input[type='checkbox']"), function(){
            var id = $(this).attr('id');
            var isChecked =  $("#"+id).is(':checked');
            if(isChecked == true)
            {
               $("#"+id).val(0);

            }
            else
            {
                $("#"+id).val(1);
            }

            if(!id.includes("parent"))
            {
                check.push(id + '-' + $("#"+id).val());
            }
        });
        
        data["name"] = $("#name").val();
        data["check"] = check;

        utils.startLoadingBtn("btnSubmit","mainForm");

        $.ajax({
            url: "/ajax/admins/roles/create",
            type: "POST",
            data:  data,
            success: function(data)
            {
            // console.log(data);

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

    function onMainModalDismiss()
    {

        window.location.href = "/admins/roles";
    }

    function onMainModalDismissError()
    {
        $("#mainForm").attr("enabled",1);
    }

    $(function() {
      $(".checkall").click(function () {
        $(this).closest('div').find(':checkbox').prop('checked', this.checked);
        });

      $(".checkul").click(function () {
        $(this).next('ul').find(':checkbox').prop('checked', this.checked);
        });  
    });

    $(function() {
        $(document).on("change", "li:has(li) > input[type='checkbox']", function() {
            $(this).parent().parent().prev('input[type="checkbox"]').prop('checked', this.checked);
            $(this).siblings('ul').find("input[type='checkbox']").prop('checked', this.checked);
        });

        $(document).on("change", "input[type='checkbox'] ~ ul input[type='checkbox']", function() {
            var l_1 = $(this).parent().parent().parent().find('.child').nextAll().find('input:checked').length;
            var l_2 = $(this).parent().parent().find('input:checked').length;
            var c = $(this).parent().find("input[type='checkbox']").is(':checked');

            if (l_2 == 0 && c == false) 
            {
                $(this).parent().parent().parent().parent().prev('input[type="checkbox"]').prop('checked', this.checked);
                $(this).closest("li:has(li)").children("input[type='checkbox']").prop('checked', c);
            } 
            else if (l_1 > 0 && c == true || l_2 > 0 && c == true) 
            {
                $(this).parent().parent().parent().parent().prev('input[type="checkbox"]').prop('checked', this.checked);
                $(this).closest("li:has(li)").children("input[type='checkbox']").prop('checked', c);
            }
        });
    })
</script>

<style>

    .heading 
    {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    #entry_valid, #entry_non_valid 
    {
        display: none;
        float: right
    }

    legend
    {
        width:50px;
        padding:0 10px;
        border-bottom:none;
    }

    li
    {
        list-style-type: none;
    }

</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.admins.admin.create.breadcrumb.admins') }}</li>
    <li class="breadcrumb-item">
        <a href="/admins/roles">
            {{ __('app.admins.admin.breadcrumb.admins_role') }}
        </a>
    </li>
    <li class="breadcrumb-item active">{{ __('app.admins.admin.create.breadcrumb.admins_role.create') }}</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                @csrf

                <div class="card-header">
                    <strong>{{ __('app.admins.admins_role.create.header') }}</strong>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">

                            <div class="heading" style=" margin-bottom: 1rem">
                                {{ __('app.admins.admins_role.create.details') }} 
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4">

                                    <label>{{ __('app.admins.admins_role.create.username') }}</label>

                                </div>
                                <div class="col-sm-8">

                                    <input type="text" name="name" id="name" class="form-control" autocomplete="off" required="">

                                </div>

                            </div>
                    </div>

                </div>                
                    <fieldset class="form-group border" style="width:100%">
                        <legend class="w-auto px-2">{{ __('app.admins.admins_role.create.ca') }} </legend>
                        <ul>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="row form-group">
                                    <li>
                                        <input type="checkbox" class="checkall" name="member" id="parent-mm">Member Management
                                            <ul>
                                                <li>
                                                    <input type="checkbox" class="checkul" id="parent-member_list">Member Listing
                                                    <ul>
                                                        <li>
                                                            <input type="checkbox" id="member_list">View
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" id="member_list">Edit
                                                        </li>
                                                    </ul>
                                                </li>


                                                <li>
                                                    <input type="checkbox" class="checkul" id="member_credit">Member Credit
                                                </li>
                                                <li>
                                                    <input type="checkbox" class="checkul" id="parent-memmber_levelsetting">Member Level Setting
                                                    <ul>
                                                        <li>
                                                            <input type="checkbox" id="view_member_levelsetting">View
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" id="edit_member_levelsetting">Edit
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    </div>
                                <div class="row form-group">
                                    <li>
                                        <input type="checkbox" class="checkall" name="banking" id="parent-bk">Banking
                                        <ul>
                                            <li>
                                                    <input type="checkbox" class="checkul" id="parent-banking_acc" >Bank Account List
                                                    <ul>
                                                        <li>
                                                            <input type="checkbox" id="view_banking_acc">View
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" id="edit_banking_acc">Edit
                                                        </li>  
                                                    </ul> 
                                            </li>

                                            <li>
                                                   <input type="checkbox" class="checkul" id="parent-dw_request" >DW Request
                                                    <ul>
                                                        <li>
                                                            <input type="checkbox" id="view_dw_request">View
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" id="edit_dw_request">Edit
                                                        </li>  
                                                    </ul> 
                                            </li>

                                        </ul>
                                    </li>
                                </div>

                                <div class="row form-group">
                                    <li>
                                        <input type="checkbox" class="checkall" name="bonus" id="parent-bn">Bonus
                                        <ul>
                                            <li>
                                                <input type="checkbox" class="checkul" id="parent-promo">Promo Setting
                                                <ul>
                                                    <li>
                                                        <input type="checkbox" id="view_promog">View
                                                    </li>
                                                    <li>
                                                        <input type="checkbox" id="edit_promog">Edit
                                                    </li>
                                                </ul>
                                            </li>
                                            <li>
                                                <input type="checkbox" class="checkul" id="parent-cashback">Cash Back Setting
                                                <ul>
                                                    <li>
                                                        <input type="checkbox" id="view_cashback_setting">View
                                                    </li>
                                                    <li>
                                                        <input type="checkbox" id="edit_cashback_setting">Edit
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                </div>

                                </div>
                                <div class="col-sm-4">

                                    <div class="row form-group">
                                        <li>
                                            <input type="checkbox" class="checkall" name="report" id="parent-rp">Report
                                            <ul>
                                                <li>
                                                    <input type="checkbox" id="txn_history_report">Transaction History
                                                </li>
                                                <li>
                                                    <input type="checkbox" id="win_loss_report">Win Loss
                                                </li>
                                                <li>
                                                    <input type="checkbox" id="member_credit_report">Member Credit Report
                                                </li>
                                            </ul>
                                        </li>
                                    </div>


                                    <div class="row form-group">
                                            <li>
                                            <input type="checkbox" class="checkall" name="membermessage" id="member_msg">Member Messgae
                                            </li>
                                    </div>
                                    <div class="row form-group">
                                        <li>
                                            <input type="checkbox" class="checkall" name="report" id="parent-CMS">CMS
                                            <ul>
                                                <li>
                                                    <input type="checkbox" id="cms_main_banner">Main Banner
                                                </li>
                                                <li>
                                                    <input type="checkbox" id="cms_topbar_announcement">Top Bar Announcement
                                                </li>
                                                <li>
                                                    <input type="checkbox" id="cms_popup">Pop Up
                                                </li>
                                            </ul>
                                        </li>
                                    </div>


                                    <div class="row form-group">
                                        <li>
                                            <input type="checkbox" class="checkall" name="settings" id="parent-s">{{ __('app.admins.admins_role.create.setting') }}
                                            <ul>
                                                <li>
                                                    <input type="checkbox" id="create_admin">{{ __('app.admins.admins_role.create.create_admin') }}
                                                </li>
                                                <li>
                                                    <input type="checkbox" class="checkul" id="parent-s">{{ __('app.admins.admins_role.create.admin_list') }}
                                                    <ul>
                                                        <li>
                                                            <input type="checkbox" id="view_admin_list">{{ __('app.admins.admins_role.create.view') }}
                                                        </li>
                                                        <li>
                                                            <input type="checkbox" id="edit_admin_list">{{ __('app.admins.admins_role.create.edit') }}
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                    </div>

                                </div>

                            </div>
                        </ul>
                </fieldset>                    
            </div>

            <div class="card-footer">

                <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                    <i class="fa fa-dot-circle-o"></i> {{ __('app.admins.admins_role.create.create') }}
                </button>

            </div>

            </form>

        </div>

    </div>
</div>

@endsection
