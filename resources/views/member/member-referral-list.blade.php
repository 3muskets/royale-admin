@extends('layouts.app')

@section('head')
<script type="text/javascript">

var date = utils.getToday();

$(document).ready(function() 
{
    prepareLocale();

    utils.createSpinner("main-spinner");

    getMainData();

    $("#filterForm").on('submit',(function(e){
        e.preventDefault();
        filterMainData();
    }));

    if(auth.getUserLevel() == 3)
    {
        $("#fg_tier4").css("display","none");
    }

});

function prepareLocale()
{   
    locale['no'] = "No.";
    locale['member_name'] = "Member Name";
    locale['member_id'] = "Member Id";
    locale['referral_bonus'] = "Referral Bonus";
    locale['join_date'] = "Join Date";
    locale['last_activity'] = "Last Activity";
    locale['last_deposit'] = "Last Deposit";
    locale['no_of_downline'] = "No. of Downline";            
    locale['mainData.edit'] = "Edit";
    locale['downline'] = "Downline";

    locale['tooltip.edit'] = "Edit";
    locale['tooltip.downline'] = "Downline";
    locale['info'] = "Info";
    locale['success'] = "Success";
    locale['error'] = "Error";
}

var mainData;
var refreshMainData = false;

function getMainData() 
{
    var containerId = "main-table";
    
    $("#main-spinner").show();
    $("#main-table").hide();
    $('#notes').hide();

    var data = utils.getDataTableDetails(containerId);


    data['username'] = $("#username").val();


    $.ajax({
        type: "GET",
        url: "/ajax/merchants/merchant/referral/list",
        data: data,
        success: function(data) 
        {
            console.log(data);
            if(data.length > 0)
                mainData = JSON.parse(data);
            else
                mainData = [];
            
            loadMainData(containerId);
        }
    });
}
function loadMainData(containerId)
{ 
    $("#main-spinner").hide();
    $("#main-table").show();

    var fields = [                           
                    ["no",locale['no'],false,false]
                    ,["id",locale['member_id'],true,false]
                    ,["username",locale['member_name'],true,false]
                    ,["created_at",locale['join_date'],true,false]                 
                    ,["referral_bonus",locale['referral_bonus'],true,true]
                    ,["num_downline",locale['no_of_downline'],true,true]                            
                    ,["downline",locale['downline'],false,false]
                ]; 


    var table = utils.createDataTable(containerId,mainData,fields,sortMainData,pagingMainData);

    if(table != null)
    {
        $('#notes').show();
                   
        var fieldDownline = utils.getDataTableFieldIdx("downline",fields);
        var fieldReferralBonus = utils.getDataTableFieldIdx("referral_bonus",fields);

        for (var i = 1, row; row = table.rows[i]; i++) 
        {       
                        

            var refBonus = utils.formatMoney(mainData.results[i - 1]["referral_bonus"]);

            if(refBonus == null)
                refBonus = 0;

            console.log(refBonus);
            row.cells[fieldReferralBonus].innerHTML = refBonus;
        } 
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
{;

    $("#username, #f_tier4").val("");

    filterMainData();
}

</script>

<style type="text/css">
    
</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.member.create.breadcrumb.membermanagement') }}</li>
    <li class="breadcrumb-item active">Member Referral Listing</li>
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
                                <label for="name">{{ __('app.reports.credit.member.filter.member') }}</label>
                                <input type="text" class="form-control" id="username" autocomplete="">
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

            <div id="main-spinner" class="card-body"></div>

            <div id="main-table" class="card-body"></div>

            <div id="notes" class="card-body">{{ __('common.notes.timezone') }}</div>            

        </div>
    </div>
</div>

@endsection
