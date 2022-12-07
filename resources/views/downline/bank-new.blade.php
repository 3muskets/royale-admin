
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
    locale['info'] = "Info";
    locale['success'] = "Success";
    locale['error'] = "Error";
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
        url: "/ajax/bank/create",
        type: "POST",
        data:  new FormData($("#mainForm")[0]),
        contentType: false,
        cache: false,
        processData:false,
        success: function(data)
        {
            console.log(data);
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
    window.location.href = "/bank/bank";
}

function onMainModalDismissError()
{
    $("#mainForm").attr("enabled",1);
}

function readURL(input) 
{
    if (input.files && input.files[0]) 
    {
        var reader = new FileReader();

        reader.onload = function (e) 
        {
            document.getElementById("bank").style.display = "block";
            $('#bank')
                .attr('src', e.target.result)
                .width(1000)
                .height(350);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

</script>

@endsection

@section('content')

<!-- Breadcrumb -->
<ol class="breadcrumb">
    <li class="breadcrumb-item">Banking</li>
    <li class="breadcrumb-item"><a href="/bank/bank">Bank List</a></li>
    <li class="breadcrumb-item active">Create New Bank</li>
</ol>

<div class="container-fluid">
    <div class="animated fadeIn">

        <div class="card">

            <form method="POST" id="mainForm">
                <input type="hidden">
                <div class="card-header" style="display:block;">
                    <strong>New Bank</strong>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 col-md-5 col-lg-4 col-xl-4">

                            <!-- Site Url -->
                            <div class="form-group row">

                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label>Site Url: </label>
                                </div>

                                <div class="col-sm col-md col-lg col-xl">
                                    <input class="form-control form-control-sm" type="text" name="site_url" required>
                                </div>

                            </div>

                            <!-- Bank Name -->
                            <div class="form-group row">

                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label>Bank Name: </label>
                                </div>

                                <div class="col-sm col-md col-lg col-xl">
                                    <input class="form-control form-control-sm" type="text" name="bank_name" required>
                                </div>

                            </div>

                            <!-- Status -->
                            <div class="form-group row">

                                <div class="col-sm-4 col-md-3 col-lg-4 col-xl-3">
                                    <label>Status: </label>
                                </div>

                                <div class="col-sm col-md col-lg col-xl">
                                    <select name="status" class="form-contro form-control-sm">

                                        <option value="0" >Offline</option><option value="1" >Online</option>
                                    </select>
                                </div>
                                
                            </div>     
                        </div>
                    </div>

                </div>

                <div class="card-footer">

                    <button id="btnSubmit" class="btn btn-sm btn-primary btn-ladda" data-style="expand-right">
                        <i class="fa fa-dot-circle-o"></i> Create
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

@endsection