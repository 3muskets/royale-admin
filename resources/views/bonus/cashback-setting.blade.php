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
        url: "/ajax/cashback/setting/detail",
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
    $('#rate').append('<option>' + '</option>').children().remove();  
    /*$('#frequency').append('<option>' + '</option>').children().remove();  */
    


    for (j = 0; j < 5.00; j += 0.01) 
    {

        $('#rate').append($('<option />').attr('value', j.toFixed(2)).html(j.toFixed(2)));

    }






    $("#amount").val(utils.formatMoney(mainData[0].amount,2));
;
    

    var inputAmt = $("#amount");
    utils.formatCurrencyInput(inputAmt);


    $('#status').append('{{ Helper::generateOptions($optionsStatus,'') }}');
    /*$('#frequency').append('{{ Helper::generateOptions($optionsFrequency,'') }}');*/


    utils.datepickerStart('s_date','e_date','s_date1',utils.formattedDate(mainData[0].start_date));
    utils.datepickerEnd('s_date','e_date','e_date1',utils.formattedDate(mainData[0].end_date),1);

    
    document.getElementById("status").value = mainData[0].status;
    /*document.getElementById("frequency").value = mainData[0].frequency;*/


    for(var i = 0; i < 1; i++)
    {

        document.getElementById("rate").value = mainData[i].rate;
    }
}

function updateCashBack() 
{
    utils.startLoadingBtn("btnSubmitCashBack","main-data");

    $("#formCashBack").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/cashback/setting/update",
        data:  new FormData($("#formCashBack")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnSubmitCashBack","main-data");

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
    <li class="breadcrumb-item active">CashBack Setting</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

    <div id="main-spinner" class="card-body"></div>

    <div id="main-data" style="display: none;">
        <div class="card-header">
            <h4 class="card-title">CashBack Setting</h4>
        </div>


        <div class="card">

            <form method="POST" id="formCashBack">
                <div class="card-body">
                    <div class="row">
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

                                <input type="text" class="form-control " name="s_date" id="s_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                <input type="hidden" name="s_date1" id="s_date1" value="">


                            </div>

                        </div>

                        <div class="col-12 col-sm-2 col-md-2 align-middle">

                            <div class="form-group">
                                <label for="e_date" style="margin: 10px 0;">End Date</label>
                            </div>

                        </div>
                        <div class="col-12 col-sm-4 col-md-2">

                            <div class="form-group">
                                <input type="text" class="form-control" name="e_date" id="e_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                <input type="hidden" name="e_date1" id="e_date1" value="">
                            </div>

                        </div>
                    </div>

                    <div class="row">

                        <div class="col-12 col-sm-2 col-md-2 align-middle">

                            <div class="form-group">
                                <label for="max" style="margin: 10px 0;">Target Lost Amount</label>
                            </div>

                        </div>
                        <div class="col-12 col-sm-10 col-md-2">

                            <div class="form-group">
                                <input style="margin: 5px 0;" type="text" id="amount" name="amount"  class="form-control" autocomplete="off">
                            </div>

                        </div>
                    </div>



                    <div class="row">
                        <!-- Cash Back Type -->
                            <div class="col-12 col-sm-2 col-md-2 col-xl-2" style="margin-top: auto;">

                                <div class="form-group">
                                    <label style="margin: 10px 0;">All Game</label>
                                </div>

                            </div>
                            <div class="col-12 col-sm-5 col-md-3 col-xl-2">

                                <div class="form-group">
                                    <label class="form-check-label" for="type_all">Rate (%)</label>

                                    <input style="margin: 5px 0;" type="text" id="rate" name="rate"  class="form-control" autocomplete="off">

                                </div>

                            </div>

                    </div>
      
                </div>
                @can('permissions.edit_cashback_setting')
                <div class="card-footer text-center">

                        <button id="btnSubmitCashBack" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="updateCashBack()">
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
