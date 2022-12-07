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
        url: "/ajax/popup/getDetail",
        data: data,
        success: function(data) 
        {
            console.log(data);
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
    

    $('#status').append('{{ Helper::generateOptions($optionsStatus,'') }}');

    if(mainData != '')
    {
        document.getElementById("status").value = mainData[0].status;

        document.getElementById("popup").style.display = "block";
        $('#popup')
            .attr('src', mainData[0].image)
            .width('50%')
            .height('auto');
        
    }

}

function updatePopup() 
{
    utils.startLoadingBtn("btnSubmitPopup","main-data");

    $("#formPopup").attr("enabled",0);

    $.ajax({
        type: "POST",
        url: "/ajax/cms/popup/update",
        data:  new FormData($("#formPopup")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data) 
        {
            utils.stopLoadingBtn("btnSubmitPopup","main-data");

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


function readURL(input) 
{
    if (input.files && input.files[0]) 
    {
        var reader = new FileReader();

        reader.onload = function (e) 
        {
            document.getElementById("popup").style.display = "block";
            $('#popup')
                .attr('src', e.target.result)
                .width('50%')
                .height('auto');
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
    <li class="breadcrumb-item active">Pop Up</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

    <div id="main-spinner" class="card-body"></div>

    <div id="main-data" style="display: none;">
        <div class="card-header">
            <h4 class="card-title">Pop Up</h4>
        </div>


        <div class="card">

            <form method="POST" id="formPopup">
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

                        <div class="col-12">
                            <div class="form-group">
                                <label>Pop Up Image: </label>
                                <input name="image" type="file" onchange="readURL(this);" style="width:100%;">
                                <img style="display:none;" id="popup" src="#" alt="popup image"><br>
                            </div>
                        </div> 

                    </div>
      
                </div>

                <div class="card-footer text-center">
                    <button id="btnSubmitPopup" type="submit" class="btn btn-primary btn-ladda" data-style="expand-right" onclick="updatePopup()">
                        Save
                    </button>
                </div>
            </form>

        </div>

    </div>

        
    <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>
    </div>
</div>

@endsection
