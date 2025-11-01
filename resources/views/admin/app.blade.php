<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>{!! !empty($pageTitle) ? $pageTitle . ' · ' : '' !!}{!! config('cms.name') !!}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080b0f">
    <meta name="color-scheme" content="dark">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ $assetHelper::admin('images/favicon.png') }}">
    <link rel="apple-touch-icon" type="image/png" href="{{ $assetHelper::admin('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ $assetHelper::admin('site.webmanifest') }}">
    <link rel="stylesheet" href="{{ $assetHelper::versioned($assetHelper::admin('css/styles.css')) }}">
    <script>window.app = @json([
        'locale' => app()->getLocale(),
        'auth' => Auth::check() ? [
            'id' => Auth::user()->id,
            'role' => Auth::user()->role
        ] : null
    ])</script>
</head>

<body class="{{ Auth::check() ? '-logged-in -role-' . Auth::user()->role : ''}}">

    <div class="admin__scaffold">

        @if (Auth::check())
            @include('admin::panel')
        @endif

        <div class="content__wrapper">

            @if (Auth::check())
                @include('admin::header')
            @endif

            <main class="content__container">
                @yield('content')
            </main>

        </div>
    </div>

    <script type="module" src="{{ $assetHelper::versioned($assetHelper::admin('js/app.js')) }}"></script>
</body>
</html>
