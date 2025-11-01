@extends('admin::auth.page')

@section('authContent')

    <h1 class="auth__form-title">
        {!! __('admin::auth.login.form.title') !!}
    </h1>

    @if (session('install-success'))
        <div class="auth__form-description">
            {!! session('install-success') !!}
        </div>
    @endif

    <form class="auth__form -login" data-login-form onsubmit="return false">

        <div class="auth__form-message"></div>

        <input
            class="textfield -h"
            name="csrf"
            data-login-form-input="csrf"
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
                    data-login-form-input="email"
                    type="text"
                    placeholder="{{ __('admin::auth.login.form.placeholderEmail') }}"
                    autocomplete="email"
                    spellcheck="false"
                >
            </div>

            <div class="input__container auth__textfield-container">
                <input
                    class="textfield auth__textfield -block"
                    name="password"
                    data-submit-on-enter
                    data-clear-error-on-input
                    data-login-form-input="password"
                    type="password"
                    placeholder="{{ __('admin::auth.login.form.placeholderPassword') }}"
                    maxlength="50"
                    autocomplete="password"
                >
            </div>
        </div>

        <div class="auth__button-container">
            <button
                type="button"
                class="button auth__button -block"
                data-submit-button
                data-login-form-submit-button
            >
                <span>{{ __('admin::auth.login.form.submitButtonText') }}</span>
            </button>
        </div>
    </form>

    <div class="auth__form-links">
        <a class="auth__form-link" href="{{ route('admin.reset-password') }}">{{ __('admin::auth.login.form.linkForgotPassword') }}</a>
    </div>

@endsection
