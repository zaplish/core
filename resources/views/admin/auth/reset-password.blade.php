@extends('admin::app')

@section('content')

    <div class="auth__wrapper">

        <h1 class="auth__form-title">
            {!! __('admin::auth.resetPassword.form.title') !!}
        </h1>

        @if (session('new-password-link-expired'))
            <div class="auth__form-description -error">
                {!! session('new-password-link-expired') !!}
            </div>
        @else
            <div class="auth__form-description">
                {!! __('admin::auth.resetPassword.form.description') !!}
            </div>
        @endif

        <form class="auth__form -reset-password" data-reset-password-form onsubmit="return false">

            <div class="auth__form-message"></div>

            <input
                class="textfield -h"
                name="csrf"
                data-reset-password-form-input="csrf"
                type="text"
                aria-hidden="true"
                tabindex="-1"
                autocomplete="new-password"
            >

            <div class="auth__textfields">

                <div class="input__container auth__textfield-container">
                    <input
                        class="textfield auth__textfield -block"
                        name="email"
                        data-submit-on-enter
                        data-clear-error-on-input
                        data-reset-password-form-input="email"
                        type="text"
                        placeholder="{{ __('admin::auth.resetPassword.form.placeholderEmail') }}"
                        autocomplete="email"
                        spellcheck="false"
                    >
                </div>
            </div>

            <div class="auth__button-container">
                <button
                    type="button"
                    class="button auth__button -block"
                    data-submit-button
                    data-reset-password-form-submit-button
                >
                    <span>{{ __('admin::auth.resetPassword.form.submitButtonText') }}</span>
                </button>
            </div>
        </form>

        <div class="auth__form-links">
            <a class="auth__form-link" href="{{ route('admin.login') }}">{{ __('admin::auth.resetPassword.form.backToSignIn') }}</a>
        </div>

    </div>

@endsection
