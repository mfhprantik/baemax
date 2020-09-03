@extends('layout')

@section('title', "Baemax")

@section('content')

<div class="container login-form">
    <div class="vhcenter">
        <h1>Login</h1>
        <hr>

        @if ($errors->has('error'))
            <span style="color:red !important">
                <strong>{{ $errors->first('error') }}</strong>
            </span>
        @endif

        <form method="post" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <input class="form-control" type="text" name="email" placeholder="Email" required>
            </div>

            <div class="form-group">
                <input class="form-control" type="password" name="password" placeholder="Password" required>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <input type="submit" class="form-control button-primary" value="Login">
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <span class="form-control button-primary" onclick="showSignupForm()">Sign Up</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container signup-form">
    <div class="vhcenter">
        <h1>Sign Up</h1>
        <hr>

        <span style="color:red !important">
            <strong id="error">{{ $errors->has('error') ? $errors->first('error') : '' }}</strong>
        </span>

        <form method="post" action="{{ route('signup') }}">
            @csrf
            <div class="form-group">
                <input class="form-control" type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="form-group">
                <input class="form-control" type="email" name="email" placeholder="Email" required>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <input class="form-control" type="password" name="password" id="password" placeholder="Password" required>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <input class="form-control" type="password" name="cpassword" id="cpassword" placeholder="Confirm Password" required>
                    </div>
                </div>
            </div>

            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <div class="form-group">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <input class="form-control" type="date" name="dob" placeholder="Date of Birth" max="2002-01-01" required>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <select class="form-control" name="gender" required>
                            <option value="0">Male</option>
                            <option value="1">Female</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <input type="submit" class="form-control button-primary" value="Sign Up" onclick="return validate()">
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <span class="form-control button-primary" onclick="showLoginForm()">Login</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')

<script type="text/javascript">
    @if (isset($_GET['signup']) && $_GET['signup'] == 1)
        showSignupForm();
    @else
        showLoginForm();
    @endif

    function showLoginForm() {
        $('.signup-form').hide();
        $('.login-form').show();
    }

    function showSignupForm() {
        $('.login-form').hide();
        $('.signup-form').show();
    }

    function validate() {
        if ($('#password').val() != $('#cpassword').val()) {
            $('#password').addClass('form-control-incorrect');
            $('#cpassword').addClass('form-control-incorrect');
            return false;
        }

        return true;
    }

    var locationIsSet = false;
    getLocation();
    interval = setInterval(function() {
        if (!locationIsSet) {
            $('#error').text('Sorry, we could not fetch your location data.');
            clearInterval(interval);
        }
    }, 100);

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(setLocation);
        }
    }

    function setLocation(position) {
        $('#latitude').val(position.coords.latitude);
        $('#longitude').val(position.coords.longitude);
        locationIsSet = true;
    }
</script>

@endsection