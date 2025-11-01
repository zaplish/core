@extends('admin::app')

@section('content')

<div class="content__content">

    <div class="tab-navigation__wrapper">
        <div class="tab-navigation__container">
            <a href="{{ route('admin.settings.developer', ['tab' => 'system']) }}" class="tab-navigation__item{{ $tab === 'system' ? ' -active' : '' }}">{{ __('admin::settings.developer.system.title') }}</a>
            <a href="{{ route('admin.settings.developer', ['tab' => 'media']) }}" class="tab-navigation__item{{ $tab === 'media' ? ' -active' : '' }}">{{ __('admin::settings.developer.media.title') }}</a>
            <a href="{{ route('admin.settings.developer', ['tab' => 'mail']) }}" class="tab-navigation__item{{ $tab === 'mail' ? ' -active' : '' }}">{{ __('admin::settings.developer.mail.title') }}</a>
            <a href="{{ route('admin.settings.developer', ['tab' => 'localization']) }}" class="tab-navigation__item{{ $tab === 'localization' ? ' -active' : '' }}">{{ __('admin::settings.developer.localization.title') }}</a>
        </div>

        @if ($tab === 'system')
            @include('admin::pages.settings.developer.section-system')
        @endif

        @if ($tab === 'media')
            @include('admin::pages.settings.developer.section-media')
        @endif

        @if ($tab === 'mail')
            @include('admin::pages.settings.developer.section-mail')
        @endif

        @if ($tab === 'localization')
            @include('admin::pages.settings.developer.section-localization')
        @endif
    </div>

</div>

@endsection