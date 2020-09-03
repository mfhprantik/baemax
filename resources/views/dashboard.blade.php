@extends('layout')

@section('title', "Dashboard | Baemax")

@section('content')

<div class="button-logout">
    <a href="{{ route('logout') }}" class="material-icons" style="font-size: 64px; color: white;" title="logout">exit_to_app</a>
</div>

<div class="container vhcenter">
    <div  class="row">
        <div class="col-12">
            <form method="post" action="{{ route('doUpload') }}" enctype="multipart/form-data">
                @csrf
                <div class="info" onclick="clickUpload()" title="Upload">
                    <img class="user-image profile-picture" src="{{ Auth::user()->image }}">
                </div>

                <input type="file" name="image" accept="image/*" style="display: none">
                <input type="submit" style="display: none;">
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h1>Welcome</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h2>{{ Auth::user()->name }}</h2>
        </div>
    </div>

    <hr style="padding: 0px; margin: : 20px;">

    <div class="row">
        <div class="col-12 user-list">
            <div class="no-user-found" style="display: none;">Sorry, we couldn't find anyone near you.</div>

        @foreach($users as $key => $user)
            <div class="user-card" id="user_{{ $user->id }}">
                <div class="col-1 pl-0 pr-0" onclick="viewImage('{{ $user->id }}')">
                @if (isset($user->image))
                    <img class="image" id="image_{{ $user->id }}" src="{{ url($user->image) }}">
                @else
                    <img class="image" src="{{ url('/uploads/images/placeholder.png') }}">
                @endif
                </div>
                <div class="col-4 pr-0"><span class="info float-left">{{ $user->name }}</span></div>
                <div class="col-1 pl-0 pr-0"><span class="info">{{ $user->age }}y</span></div>
                <div class="col-2 pl-0 pr-0"><span class="info">{{ $user->gender ? 'Female' : 'Male' }}</span></div>
                <div class="col-2 pl-0 pr-0"><span class="info">{{ number_format($user->distance / 1000, 1, '.', ',') }}KM</span></div>
                <div class="col-1 pl-0 pr-0" title="Dislike">
                    <svg class="cross" viewBox="0 0 64.000000 64.000000" onclick="dislike('{{ $user->id }}')"><g xmlns="http://www.w3.org/2000/svg" transform="translate(0.000000,64.000000) scale(0.100000,-0.100000)"><path d="M192 619 c-116 -45 -192 -163 -192 -299 0 -184 121 -310 305 -318 102 -5 175 21 238 81 64 63 91 126 95 222 5 106 -21 177 -88 245 -89 88 -235 116 -358 69z m83 -210 l45 -42 45 42 c48 44 55 47 73 29 18 -18 15 -25 -29 -73 l-42 -45 42 -45 c44 -48 47 -55 29 -73 -18 -18 -25 -15 -73 29 l-45 42 -45 -42 c-48 -44 -55 -47 -73 -29 -18 18 -15 25 29 73 l42 45 -42 45 c-44 48 -47 55 -29 73 18 18 25 15 73 -29z"/></g></svg>
                </div>
                <div class="col-1 pl-0 pr-0" title="Like">
                    <svg class="heart" viewBox="0 0 32 29.6" onclick="like('{{ $user->id }}')"><path d="M23.6,0c-3.4,0-6.3,2.7-7.6,5.6C14.7,2.7,11.8,0,8.4,0C3.8,0,0,3.8,0,8.4c0,9.4,9.5,11.9,16,21.2 c6.1-9.3,16-12.1,16-21.2C32,3.8,28.2,0,23.6,0z"/></svg>
                </div>
            </div>
        @endforeach
        </div>
    </div>
</div>

<div id="match" class="modal fade">
    <div class="modal-dialog vhcenter" style="transform: translate(-50%, -50%);">
        <div class="modal-content match">
            <div class="modal-body">
                <svg class="heart" viewBox="0 0 32 29.6"><path d="M23.6,0c-3.4,0-6.3,2.7-7.6,5.6C14.7,2.7,11.8,0,8.4,0C3.8,0,0,3.8,0,8.4c0,9.4,9.5,11.9,16,21.2 c6.1-9.3,16-12.1,16-21.2C32,3.8,28.2,0,23.6,0z"/></svg>
                <h1>It's a Match!</h1>
            </div>
        </div>
    </div>
</div>

<div id="showImage" class="modal fade">
    <div class="modal-dialog vhcenter" style="transform: translate(-50%, -50%); max-width: 100%;">
        <div class="modal-content view-image">
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <img src="" style="width: 100%; min-width: 260px; height: 100%; min-height: 440px;" id="modal-image">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script type="text/javascript">
    if ($('.user-card').length == 0) $('.no-user-found').show();

    $('input[type=file]').on('change', function() {
        $('input[type=submit]').click();
    });

    function clickUpload() {
        $('input[type=file]').click();
    }

    // Shows user image given user_id as parameter
    function viewImage(user_id) {
        image = $('#image_' + user_id).attr('src');
        if (image) {
            $('#modal-image').attr('src', image);
            $('.container').hide();
            $('#showImage').modal('show');

            $('#showImage').on('hidden.bs.modal', function () {
                $('.container').show();
            });
        }
    }

    /**
     *  Sends jQuery post request when user likes someone
     *  Shows a modal in case its a match
     */
    function like(user_id) {
        _token = $('meta[name="csrf-token"]').attr('content');
        $.post('/like', {user_id : user_id, _token : _token}, function(response) {
            $('#user_' + user_id).remove();
            if ($('.user-card').length == 0) $('.no-user-found').show();

            if (response == 1) {
                $('.container').hide();
                $('#match').modal('show');

                $('#match').on('hidden.bs.modal', function () {
                    $('.container').show();
                });
            }
        });
    }

    // Sends jQuery post request when user dislikes someone
    function dislike(user_id) {
        _token = $('meta[name="csrf-token"]').attr('content');
        $.post('/dislike', {user_id : user_id, _token : _token}, function(response) {
            $('#user_' + user_id).remove();
            if ($('.user-card').length == 0) $('.no-user-found').show();
        });
    }
</script>

@endsection