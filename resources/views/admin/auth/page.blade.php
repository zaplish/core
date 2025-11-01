@extends('admin::app')

@section('content')

    <div class="auth__wrapper">

        @if (session('message'))
            <div class="flash-message__wrapper -{{ session('message.type') }}">
                <div class="flash-message__title">{!! session('message.title') !!}</div>
                @if (session('message.text'))
                    <div class="flash-message__text">{!! session('message.text') !!}</div>
                @endif
            </div>
        @endif

        @yield('authContent')

    </div>

@endsection
