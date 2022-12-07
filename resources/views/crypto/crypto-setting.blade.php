@extends('layouts.app')

@section('head')

    
<script type="text/javascript">
    $(document).ready(function()
    {
        prepareLocale();

        // $("#formRate").on('submit', function(e)
        // {
        //  e.preventDefault();
        //  updateRate();
        // });  

        $("#formRate").on('submit', function(e)
        {
            e.preventDefault();
            updateRate();
        }); 



        //onkeyup rate
        $(".crypto_value").keyup(function () {
            var val = $(this).val();
            val = val.replace(/[^0-9\.]/g, '');
            console.log('hh');
             val = utils.formatMoney(val/4.13);
             $("#rate").html('= '+val+' USDT');

        });


    });

    function prepareLocale() 
    {
        locale['info'] = "Info";
        locale['success'] = "Success";
        locale['error'] = "Error";
    }

    function onModalDismiss() 
    {
        location.reload();
    }

    function updateRate() 
    {
     utils.startLoadingBtn("btnSubmitRate");

     $("#formRate").attr("enabled",0);

     $.ajax({
         type: "POST",
         url: "/ajax/crypto/update/rate",
         data:  new FormData($("#formRate")[0]),
         contentType: false,
         cache: false,
         processData:false,
         success: function(data) 
         {
             utils.stopLoadingBtn("btnSubmitRate");

             var obj = JSON.parse(data);

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

</script>

@endsection

@section('content')

    <!-- Breadcrumb -->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">Crypto</li>
        <li id="breadcrumbs" class="breadcrumb-item active">Settings</li>
    </ol>

    <div class="container-fluid">
        <div class="animated fadeIn">
            <div class="card">
                <div class="card-header">
                    <strong>Crypto Rate Settings</strong>
                </div>

                <div class="card-body">
                    <form method="POST" id="formRate">
                        <div class="card-body">
                            <div class="row">
              
                                <div class="col-md-3">
                                    <span style="width: 100%">{{$cryptoDetail[0]->token}}</span>

                                    <input type="amount"  class="form-control"  value="{{$cryptoDetail[0]->rate}}" name="crypto_rate" id="crypto_rate">
                                    <input type="hidden" name="token_id" id="token_id" value="{{$cryptoDetail[0]->token_id}}">
                                </div>                  
                            </div>
                        </div>           
                    </form>
                </div>
                @can('system.accounts.admin')
                @can('permissions.edit_rebate_setting')
                <div class="card-footer text-center">
                    <button id="btnSubmitRate" type="submit" class="btn btn-primary btn-ladda"  onclick="updateRate()">
                        Save
                    </button>
                </div>
                @endcan  
                @endcan
            </div>


        </div>
    </div>

@endsection
