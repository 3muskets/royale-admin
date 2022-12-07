@extends('layouts.app')

@section('head')
<script type="text/javascript">

var date = utils.getToday();

$(document).ready(function() 
{
  var s_date = utils.getParameterByName("s_date");
  var e_date = utils.getParameterByName("e_date");

  $("#s_date").val(utils.formattedDate(s_date));
  $("#e_date").val(utils.formattedDate(e_date));

  $("#s_date1").val(s_date);
  $("#e_date1").val(e_date);

  if (!s_date) 
      utils.datepickerStart('s_date','e_date','s_date1',date);

  if (!e_date) 
      utils.datepickerEnd('s_date','e_date','e_date1',date);
  
  if (s_date == "") 
  {
      document.getElementById('s_date').value = "";
      document.getElementById('s_date1').value = "";
  }

  if (e_date == "") 
  {
      document.getElementById('e_date').value = "";
      document.getElementById('e_date1').value = "";
  }

  utils.datepickerStart('s_date','e_date','s_date','');
  utils.datepickerEnd('s_date','e_date','e_date','');

  getMainData();

    $("#prd_id").on('change', function postinput()
    {
        getMainData();
    });
});

function getMainData()
{
  var data = {};

  data["start_date"] = $("#s_date1").val();
  data["end_date"] = $("#e_date1").val();
  data['prd_id'] = $("#prd_id").val();

  var s_date = $("#s_date1").val();
  var e_date = $("#e_date1").val();


     $.ajax({
        type: "GET",
        url: "/ajax/home",
        data: data,
        success: function(data) 
        {
          if(data) 
          {
            $("#total-member").html(data.totalMember);
/*            $("#total-adj-add").html(utils.formatMoney(data.totalAdjustmentAdd));
            $("#total-adj-deduct").html(utils.formatMoney(data.totalAdjustmentDeduct));*/

            $("#register-member").html(data.registerMember);
/*            $("#register-agent-link").html('<a href="/merchants/merchant?s_date='+s_date+'&e_date='+e_date+'">' + data.registerAgent + '</a>');
            $("#register-member-link").html('<a href="/merchants/merchant/member?s_date='+s_date+'&e_date='+e_date+'">' + data.registerMember + '</a>');
            $("#deposit").html(utils.formatMoney(data.totalDeposit));
            $("#withdraw").html(utils.formatMoney(data.totalWithdraw));
            $("#crypto-deposit").html(utils.formatMoney(data.totalCryptoDeposit));
            $("#crypto-withdraw").html(utils.formatMoney(data.totalCryptoWithdraw));
            $("#turnover").html(utils.formatMoney(data.totalTurnover));
            $("#win").html(utils.formatMoney(data.totalWinLoss));
            $("#pt_amt").html(utils.formatMoney(data.totalPtAmt));
*/

            utils.initChart(data.profitProduct,['Sxg', 'Haba', 'Prag', 'Wm'], "{{ __('app.home.chart.product') }}");

            $('#five_agent tbody tr').remove();
            $('#five_member tbody tr').remove();

            var member = data.topFiveMember;
            var agent = data.topFiveAgent;

            if(member.length > 0)
            {
              for (var j = 0; j < member.length; j ++) 
              {
                var username = member[j].username;
                var winloss = member[j].win_loss;


                $('#five_member tbody').append('<tr><td>' + username + '</td><td style="color:#4dbd74;"><b>' + utils.formatMoney(winloss) + '</b></td></tr>');
              }
            }
          }
        }
  });
}

function filterMainData()
{
    getMainData();
}

function resetMainData()
{
    $("#e_date, #e_date1").val("");
    $("#s_date, #s_date1").val("");

    filterMainData();
}

function filterDate(date)
{
    var startDate = '';
    var endDate = '';

    if(date == 1)
    {
        startDate = utils.getToday();
        endDate = utils.getToday();
    }

    if(date == 2)
    {
        startDate = utils.getYesterday();
        endDate = utils.getYesterday();
    }

    if(date == 3)
    {
        var week = utils.getThisWeek();
        startDate = week[0];
        endDate = week[1];
    }

    if(date == 4)
    {
        var lastweek = utils.getLastWeek();
        startDate = lastweek[0];
        endDate = lastweek[1];
    }
    if(date == 5)
    {
        var month = utils.getThisMonth();
        startDate = month[0];
        endDate = month[1];
    }

    if(date == 6)
    {
        var lastmonth = utils.getLastMonth();
        startDate = lastmonth[0];
        endDate = lastmonth[1];

    }

    $("#e_date").val(endDate);
    $("#s_date").val(startDate);
    $("#e_date1").val(utils.formattedDbDate(endDate));
    $("#s_date1").val(utils.formattedDbDate(startDate));


    getMainData();


}

</script>

<style type="text/css">

  .info-box 
  {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
  }

  .info-box-icon 
  {
    border-top-left-radius: 2px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 2px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 20px;
    line-height: 90px;
    color: #fff;
  }

  .info-box-content 
  {
    padding: 10px 10px 0px 10px;
    margin-left: 90px;
  }

  .info-box-text 
  {
    display: block;
    font-size: 14px;
    font-weight: bold;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    
  }

  .info-box-number 
  {
    display: block;
    font-size: 18px;
  }

  .fa
  {
    font-size : 20px;
  }

  .bg-one
  {
     background: #d9f6fa;
  }

  .bg-two
  {
     background: #d9f0dd;
  }

  .bg-three
  {
     background: #dcf1ea;
  }

  .bg-four
  {
     background: #d7eaf6;
  }

  #circle-one
  {
    background: #3bccdd;
    border-radius: 100%;
    padding: 13px 16px;
  }

  #circle-two
  {
    background-color: #3db34f;
    border-radius: 100%;
    padding: 13px 19px;
  }

  #circle-three
  {
    background: #4fbc8f;
    border-radius: 100%;
    padding: 13px 16px;
  } 

  #circle-four
  {
    background: #509fd5;
    border-radius: 100%;
    padding: 13px 19px;
  }

  #second
  {
    margin-bottom: 20px;
  }
</style>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
  <li class="breadcrumb-item">{{ __('app.home.home') }}</li>
</ol>

<div class="container-fluid">
  <div class="animated fadeIn">
    <div class="card">
      <form method="POST" id="filterForm">
        <div class="card-body">
          <div class="row">
              <div class="form-inline ml-2">
                <div class="form-group">
                  <label for="name">{{ __('common.filter.fromdate') }} </label>
                  <input type="text" class="form-control ml-2" name="s_date" id="s_date" placeholder="dd/mm/yyyy">
                  <input type="hidden" name="s_date1" id="s_date1">
                </div>
              <div class="form-inline ml-2">
                <div class="form-group">
                  <label>{{ __('common.filter.todate') }} </label>
                  <input type="text" class="form-control ml-2" name="e_date" id="e_date" placeholder="dd/mm/yyyy">
                  <input type="hidden" name="e_date1" id="e_date1">
                </div>
              </div>
              </div>
              <div class="form-inline ml-2">
                <div class="form-group ml-2">
                  <button type="button" id="submit" class="btn btn-sm btn-success" onclick="filterMainData()"><i class="fa fa-dot-circle-o"></i> {{ __('common.filter.submit') }}</button>
                  &nbsp;
                  <button type="button" class="btn btn-sm btn-danger" onclick="resetMainData()"><i class="fa fa-ban"></i> {{ __('common.filter.reset') }}</button>
                  &nbsp;
                  <button type="button" class="btn-sm " onclick="filterDate(1)">{{ __('common.filter.today') }}</button>
                  &nbsp;
                  <button type="button" class="btn-sm " onclick="filterDate(2)">{{ __('common.filter.yesterday') }}</button>
                  &nbsp;
                  <button type="button" class="btn-sm " onclick="filterDate(3)">{{ __('common.filter.thisweek') }}</button>
                  &nbsp;
                  <button type="button" class="btn-sm " onclick="filterDate(4)">{{ __('common.filter.lastweek') }}</button>
                  &nbsp;
                  <button type="button" class="btn-sm " onclick="filterDate(5)">{{ __('common.filter.thismonth') }}</button>
                  &nbsp;
                  <button type="button" class="btn-sm " onclick="filterDate(6)">{{ __('common.filter.lastmonth') }}</button>
                </div>
              </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="container-fluid">
  <div class="animated fadeIn">
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <div class="info-box">
              <span class="info-box-icon bg-one"><span id="circle-one"><i class="fa fa-users"></i></span></span>

              <div class="info-box-content">
                <span class="info-box-text">{{ __('app.home.registermember') }}</span><br/>
                @if(Auth::user()->level == 3)
                  <span class="info-box-number" id="register-member-link"></span>
                @else
                  <span class="info-box-number" id="register-member"></span>
                @endif
              </div>
            </div>
        </div>
      <div class="col-sm-12 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-three"><span id="circle-three"><i class="fa fa-users"></i></span></span>

            <div class="info-box-content">
              <span class="info-box-text">{{ __('app.home.totalmember') }}</span><br/>
              <span class="info-box-number" id="total-member"></span>
            </div>
          </div>
        </div>      
    </div>

<!--     <div class="row">
      <div class="col-sm-12 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-three"><span id="circle-three"><i class="fa fa-money"></i></span></span>

            <div class="info-box-content">
              <span class="info-box-text">Total Deposit</span><br/>
              <span class="info-box-number" id="total-deposit"></span>
            </div>
          </div>
        </div>

      <div class="col-sm-12 col-md-6">
        <div class="info-box">
            <span class="info-box-icon bg-three"><span id="circle-three"><i class="fa fa-money"></i></span></span>

            <div class="info-box-content">
              <span class="info-box-text">Total Withdraw</span><br/>
              <span class="info-box-number" id="total-withdraw"></span>
            </div>
          </div>
        </div>

      </div>
 -->
    </div>
</div>


<!-- <div class="container-fluid" id="second">
  <div class="animated fadeIn">
    <table class="table table-responsive-sm table-hover table-outline mb-0">
        <thead class="thead-light">
            <tr>
                <th>{{ __('app.home.total') }}</th>
                <th style="width:20%;">
                    <select class="form-control" style="height: 30px;font-size: 12px" id="prd_id">
                        <option value="0">{{ __('app.home.total.all') }}</option>
                        <option value="1">Sxg</option>>
                        <option value="2">Haba</option>
                        <option value="3">Prag</option>
                        <option value="4">Wm</option>
                    </select>
               </th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #fff">
                <td>{{ __('app.home.total.deposit') }}</td>
                <td  class="text-center">
                <span class=" text-center badge badge-primary" style="font-size: 12px" id="deposit">{{Helper::formatMoney(0)}}</span>
                </td>
            </tr>
             <tr style="background-color: #fff">
                <td>{{ __('app.home.total.withdaw') }}</td>
                <td  class="text-center">
                <span class=" text-center badge badge-primary" style="font-size: 12px" id="withdraw">{{Helper::formatMoney(0)}}</span>
                </td>
            </tr>
            <tr style="background-color: #fff">
                <td>{{ __('app.home.total.cryptodeposit') }}</td>
                <td  class="text-center">
                <span class=" text-center badge bg-warning" style="font-size: 12px" id="crypto-deposit">{{Helper::formatMoney(0)}}</span>
                </td>
            </tr>
             <tr style="background-color: #fff">
                <td>{{ __('app.home.total.cryptowithdraw') }}</td>
                <td  class="text-center">
                <span class=" text-center badge bg-warning" style="font-size: 12px" id="crypto-withdraw">{{Helper::formatMoney(0)}}</span>
                </td>
            </tr>
            <tr style="background-color: #fff">
                <td>{{ __('app.home.total.turnover') }}</td>
                <td  class="text-center">
                <span class=" text-center badge badge-success" style="font-size: 12px" id="turnover">{{Helper::formatMoney(0)}}</span>
                </td>
            </tr>
            <tr style="background-color: #fff">
                <td>{{ __('app.home.total.wins') }}</td>
                <td  class="text-center">
                <span class=" text-center badge badge-danger" style="font-size: 12px" id="win">{{Helper::formatMoney(0)}}</span>
                </td>
            </tr>
            <tr style="background-color: #fff">
                <td>{{ __('app.home.total.ptamount') }}</td>
                <td  class="text-center">
                <span class=" text-center badge badge-dark" style="font-size: 12px" id="pt_amt">{{Helper::formatMoney(0)}}</span>
                </td>
            </tr>

        </tbody>
    </table>
  </div>
</div>

<div class="container-fluid">
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-sm-12 col-md-4">
        <div class="card">
          <div class="card-header"><b>{{ __('app.home.chart.profitproduct') }}</b></div>
          <div class="card-body">
            <canvas id="myChart" width="400" height="400"></canvas>
          </div>
        </div>
      </div>

      <div class="col-sm-12 col-md-4">
        <div class="card">
          <div class="card-header"><b>{{ __('app.home.top.members') }}</b></div>
          <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="five_member">
                        <thead class="thead-light">
                          <tr>
                             <th>{{ __('app.home.top.members.username') }}</th>
                            <th>{{ __('app.home.top.members.wins') }}</th>
                          </tr>
                        </thead>
                          <tbody>
             
                        </tbody>
                  </table>
                </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> -->
@endsection
