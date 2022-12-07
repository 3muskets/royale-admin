<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="/js/utils.js"></script>
    
    <!-- CoreUI -->
    <link href="/coreui/vendors/css/font-awesome.min.css" rel="stylesheet">
    <link href="/coreui/vendors/css/simple-line-icons.min.css" rel="stylesheet">
    <link href="/coreui/css/style.css" rel="stylesheet">

    <script type="text/javascript">
        
        $(document).ready(function() 
        {
            if(utils.getParameterByName("k") == 1)
            {
                alert("{!! __('error.login.multiple_login') !!}");
                window.history.pushState({}, document.title, "/");
            }
            else if(utils.getParameterByName("k") == 2)
            {
                alert("{!! __('error.login.account_inactive') !!}");
                window.history.pushState({}, document.title, "/");
            }

        });

    </script>

</head>

<body class="app flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card-group">
                    <div class="card p-4">
                        <div class="card-body">
                            <h1>{{ __('app.login.login') }}</h1>
                            <p class="text-muted">{{ __('app.login.signin') }}</p>

                            <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
                            @csrf

                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="icon-user"></i></span>
                                    </div>

                                    <input id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" placeholder="{{ __('app.login.username') }}" required autofocus oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')">

                                    @if ($errors->has('username'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('username') }}</strong>
                                        </span>
                                    @endif

                                </div>
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="icon-lock"></i></span>
                                    </div>

                                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="{{ __('app.login.password') }}" required oninvalid="this.setCustomValidity('{!! __('error.input.required') !!}')" oninput="setCustomValidity('')">

                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif

                                </div>
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary px-4">{{ __('app.login.login') }}</button>
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  
</body>
</html>