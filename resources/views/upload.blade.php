@extends('layout')

@section('title', "Upload Profile Picture | Baemax")

@section('content')

<div class="container login-form">
    <div class="vhcenter">
        <h2>Upload a Profile Picture</h2>
        <hr>

        <form method="post" action="{{ route('doUpload') }}" enctype="multipart/form-data">
            @csrf
            <div class="profile-picture" onclick="clickUpload()">
                <div class="camera">
                    <span class="material-icons" style="font-size: 64px;">add_a_photo</span>
                </div>
            </div>

            <input type="file" name="image" accept="image/*" style="display: none">
            <input type="submit" style="display: none;">
        </form>
    </div>
</div>

@endsection

@section('scripts')

<script type="text/javascript">
    $('input[type=file]').on('change', function() {
        $('input[type=submit]').click();
    });

    function clickUpload() {
        $('input[type=file]').click();
    }
</script>

@endsection