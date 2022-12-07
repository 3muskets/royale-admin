@extends('layouts.app')

@section('head')

<script type="text/javascript">

$(document).ready(function() 
{
    prepareLocale();

    checkCredit();

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

    utils.startLoadingBtn("btnSubmit","mainForm");

    $.ajax({
        url: "/ajax/admins/credit/update",
        type: "POST",
        data:  new FormData($("#mainForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
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

function checkCredit()
{
    var input = $("form").find("#amount");
    utils.formatCurrencyInput(input);
}

function onMainModalDismiss()
{
    window.location.href = "/admins/credit";
}

function onMainModalDismissError()
{
    $("#mainForm").attr("enabled",1);
}
</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">{{ __('app.admins.admin.create.breadcrumb.admins') }}</li>
    <li class="breadcrumb-item active">CA Credit</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                @csrf

                <div class="card-header">
                    <strong>CA Credit</strong>
                </div>
                
                <div class="card-body">

                    <div class="col-sm-4">

                    <div class="form-group row">

                        <div class="col-sm-4">

                            <label>{{ __('app.merchants.merchant.create.credit.available') }}</label>

                        </div>

                        <div class="col-sm-8">

                            <input class="form-control" type="text" value="{{ Helper::formatMoney($availableCredit) }}" disabled="" autocomplete="off">

                        </div>
                        
                    </div>

                    <div class="form-group row">

                        <div class="col-sm-4">

                            <label>Type</label>

                        </div>

                        <div class="col-sm-8">

                            <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" class="custom-control-input" id="deposit" value="d" name="type" checked>
                                    <label for="deposit" class="custom-control-label">Deposit</label>
                                </div>

                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" class="custom-control-input" id="withdraw" value="w" name="type">
                                    <label for="withdraw" class="custom-control-label">Withdraw</label>
                                </div>

                        </div>
                        
                    </div>

                   <div class="form-group row">

                        <div class="col-sm-4">

                            <label>Amount</label>

                        </div>

                        <div class="col-sm-8">

                            <input class="form-control" type="text" name="amount" id="amount" required="" title="{!! __('error.input.required') !!}" autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')" autocomplete="off">

                        </div>
                   
                    </div>
                </div>

                </div>

                           

                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> Update
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>
        
@endsection
