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
    $("#main-data").hide();
    $("#main-spinner").show();
    $("#notes").hide();


    var data = '';

    $.ajax({
        type: "GET",
        url: "/ajax/rebate/setting/detail",
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

            loadMainData();
        }
    });
}

function loadMainData()
{
    $("#main-data").show();
    $("#main-spinner").hide();


    //prevent duplicate option 
    $('#status').append('<option>' + '</option>').children().remove();  
    $('#new').append('<option>' + '</option>').children().remove();  
    $('#reg').append('<option>' + '</option>').children().remove();  
    $('#bronze').append('<option>' + '</option>').children().remove();  
    $('#slv').append('<option>' + '</option>').children().remove();  
    $('#gld').append('<option>' + '</option>').children().remove();
    $('#plt').append('<option>' + '</option>').children().remove();  
    /*$('#frequency').append('<option>' + '</option>').children().remove();  */


    for (j = 0; j < 5.0; j += 0.01) 
    {

        $('#new').append($('<option />').attr('value', j.toFixed(2)).html(j.toFixed(2)));
        $('#reg').append($('<option />').attr('value', j.toFixed(2)).html(j.toFixed(2)));
        $('#bronze').append($('<option />').attr('value', j.toFixed(2)).html(j.toFixed(2)));
        $('#slv').append($('<option />').attr('value', j.toFixed(2)).html(j.toFixed(2)));
        $('#gld').append($('<option />').attr('value', j.toFixed(2)).html(j.toFixed(2)));
        $('#plt').append($('<option />').attr('value', j.toFixed(2)).html(j.toFixed(2)));
    }






    $("#min").val(utils.formatMoney(mainData[0].min,2));
    $("#max").val(utils.formatMoney(mainData[0].max,2));
    

    var inputMin = $("#min");
    var inputMax = $("#max");
    utils.formatCurrencyInput(inputMin);
    utils.formatCurrencyInput(inputMax);

    $('#status').append('{{ Helper::generateOptions($optionsStatus,'') }}');
   /* $('#frequency').append('{{ Helper::generateOptions($optionsFrequency,'') }}');*/

    utils.datepickerStart('s_date','e_date','s_date1',utils.formattedDate(mainData[0].start_date));
    utils.datepickerEnd('s_date','e_date','e_date1',utils.formattedDate(mainData[0].end_date),1);

    
    document.getElementById("status").value = mainData[0].status;
    /*document.getElementById("frequency").value = mainData[0].frequency;*/
    document.getElementById("new").value = mainData[0].new_mem_value;
    document.getElementById("reg").value = mainData[0].reg_mem_value;
    document.getElementById("bronze").value = mainData[0].bronze_mem_value;
    document.getElementById("slv").value = mainData[0].silver_mem_value;
    document.getElementById("gld").value = mainData[0].gold_mem_value;
    document.getElementById("plt").value = mainData[0].plat_mem_value;

}

function updateRebate() 
{
    utils.startLoadingBtn("btnSubmitRebate","main-data");

    $("#formRebate").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/rebate/setting/update",
        data:  new FormData($("#formRebate")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnSubmitRebate","main-data");

            var obj = JSON.parse(data);

            console.log(obj);

            if(obj.status == 1)
            {
                refreshMainData = true;
                utils.showModal(locale['info'],locale['success'],obj.status,onModalDismiss);

            }
            else
            {
                utils.showModal(locale['error'],obj.error,obj.status,"");

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


</script>

<style type="text/css">
    


</style>

@endsection

@section('content')

<ol class="breadcrumb">
    <li class="breadcrumb-item">Bonus</li>
    <li class="breadcrumb-item active">Rebate Setting</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

    <div id="main-spinner" class="card-body"></div>

    <div id="main-data" style="display: none;">
        <div class="card-header">
            <h4 class="card-title">Rebate Setting</h4>
        </div>


        <div class="card">

            <form method="POST" id="formRebate">

                <input type="hidden" id="id" name="id" value="1">

                <div class="card-body">
                    <div class="row">
                    <!-- </div>

                    <div class="row"> -->
                        <div class="col-12 col-sm-2 col-md-2 align-middle">

                            <div class="form-group">
                                <label for="status" style="margin: 10px 0;">Status</label>
                            </div>

                        </div>
                        <div class="col-12 col-sm-10 col-md-2">

                            <div class="form-group">

                                <select id="status" name="status" class="form-control" style="margin: 5px 0;">
                                   
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-2 col-md-2 align-middle">

                            <div class="form-group">
                                <label for="s_date" style="margin: 10px 0;">Start Date</label>
                            </div>

                        </div>
                        <div class="col-12 col-sm-4 col-md-2">

                            <div class="form-group">
                                <!-- <input style="margin: 5px 0;" type="text" id="s_date" name="s_date" class="form-control" autocomplete="off"> -->
                                <input type="text" class="form-control " name="s_date" id="s_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                <input type="hidden" name="s_date1" id="s_date1" value="">


                            </div>

                        </div>
                    <!-- </div>

                    <div class="row"> -->
                        <div class="col-12 col-sm-2 col-md-2 align-middle">

                            <div class="form-group">
                                <label for="e_date" style="margin: 10px 0;">End Date</label>
                            </div>

                        </div>
                        <div class="col-12 col-sm-4 col-md-2">

                            <div class="form-group">
                                <!-- <input style="margin: 5px 0;" type="text" id="e_date" name="e_date" class="form-control" autocomplete="off"> -->
                                <input type="text" class="form-control" name="e_date" id="e_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                <input type="hidden" name="e_date1" id="e_date1" value="">
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-2 col-md-2 align-middle">

                            <div class="form-group">
                                <label for="max" style="margin: 10px 0;">Max</label>
                            </div>

                        </div>
                        <div class="col-12 col-sm-10 col-md-2">

                            <div class="form-group">
                                <input style="margin: 5px 0;" type="text" id="max" name="max"  class="form-control" autocomplete="off">
                            </div>

                        </div>
                    </div>


                    <div class="row">

                            <div class="col-12 col-sm-6 col-md-2">

                                <div class="form-group">
                                        <label style="margin: 10px 0;">New Member</label>
                                        <select id="new" name="new" class="form-control" style="margin: 5px 0;">   
                                        </select>
                                </div>

                            </div>
                            <div class="col-12 col-sm-6 col-md-2">

                                <div class="form-group">
                                    <label style="margin: 10px 0;">Reg Member</label>
                                    <select id="reg" name="reg" class="form-control" style="margin: 5px 0;">    
                                    </select>
                                </div>

                            </div>

                            <div class="col-12 col-sm-6 col-md-2">

                                <div class="form-group">
                                    <label style="margin: 10px 0;">Bronze Member</label>
                                    <select id="bronze" name="bronze" class="form-control" style="margin: 5px 0;">    
                                    </select>
                                </div>

                            </div>

                            <div class="col-12 col-sm-6 col-md-2">

                                <div class="form-group">
                                    <label style="margin: 10px 0;">Silver Member</label>
                                    <select id="slv" name="slv" class="form-control" style="margin: 5px 0;">   
                                    </select>
                                </div>

                            </div>

                            <div class="col-12 col-sm-6 col-md-2">

                                <div class="form-group">
                                    <label style="margin: 10px 0;">Gold Member</label>
                                    <select id="gld" name="gld" class="form-control" style="margin: 5px 0;">    
                                    </select>

                                </div>

                            </div>

                            <div class="col-12 col-sm-6 col-md-2">

                                <div class="form-group">
                                    <label style="margin: 10px 0;">Plat Member</label>
                                    <select id="plt" name="plt" class="form-control" style="margin: 5px 0;">
                                    </select>
                                </div>

                            </div>
<!--                             <div class="col-12 col-sm-6 col-md-2">

                                <div class="form-group">
                                    <label style="margin: 10px 0;">Given Bonus Frequency</label>
                                    <select id="frequency" name="frequency" class="form-control" style="margin: 5px 0;">     
                                    </select>
                                </div>

                            </div> -->

                    </div>

                
<!-- 
                    
                    <div class="row" style="margin-bottom: -15px;">

                            <div class="col-sm-2 align-middle">

                                <div class="form-group">
                                    <label style="margin-top: .5rem;">Haba</label>
                                </div>

                            </div>
                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="new_2" name="prd_2[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>

                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="reg_2" name="prd_2[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="bronze_2" name="prd_2[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="slv_2" name="prd_2[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="gld_2" name="prd_2[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="plt_2" name="prd_2[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>
                    </div>

                    
                    <div class="row" style="margin-bottom: -15px;">

                            <div class="col-sm-2 align-middle">

                                <div class="form-group">
                                    <label style="margin-top: .5rem;">WM</label>
                                </div>

                            </div>

                           <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="new_3" name="prd_3[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>
                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="reg_3" name="prd_3[]" class="form-control" style="margin: 5px 0;">
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="bronze_3" name="prd_3[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="slv_3" name="prd_3[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="gld_3" name="prd_3[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="plt_3" name="prd_3[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>
                    </div>

                    
                    <div class="row" style="margin-bottom: -15px;">



                            <div class="col-sm-2 align-middle">

                                <div class="form-group">
                                    <label style="margin-top: .5rem;">Sportsbook</label>
                                </div>

                            </div>

                           <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="new_4" name="prd_4[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>
                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="reg_4" name="prd_4[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="bronze_4" name="prd_4[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="slv_4" name="prd_4[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="gld_4" name="prd_4[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>


                            <div class="col-sm-1 align-middle">

                                <div class="form-group">
                                    <select id="plt_4" name="prd_4[]" class="form-control" style="margin: 5px 0;">
                                        
                                    </select>
                                </div>

                            </div>
                    </div>
 -->
                    


                    
                </div>
                 @can('permissions.edit_rebate_setting')
                <div class="card-footer text-center">

                        <button id="btnSubmitRebate" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="updateRebate()">
                            Save
                        </button>
                    
                </div>
                @endcan
            </form>

        </div>

    </div>

        
    <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>
    </div>
</div>

@endsection
