@extends('admin::app')

@section('content')

    <div class="auth__wrapper">

        <h1 class="auth__form-title">
            {!! __('admin::auth.newPassword.form.title') !!}
        </h1>

        <div class="auth__form-description">
            {!! __('admin::auth.newPassword.form.description') !!}
        </div>

        <form class="auth__form -new-password" data-new-password-form onsubmit="return false">

            <div class="auth__form-message"></div>

            <input
                class="textfield -h"
                name="csrf"
                data-new-password-form-input="csrf"
                type="text"
                aria-hidden="true"
                tabindex="-1"
                autocomplete="new-password"
            >

            <input
                name="user-id"
                data-new-password-form-input="user-id"
                value="{{ $userId }}"
                type="hidden"
                autocomplete="new-password"
            >

            <input
                name="reset-password-hash"
                data-new-password-form-input="reset-password-hash"
                value="{{ $resetPasswordHash }}"
                type="hidden"
            >

            <div class="auth__textfields">

                <div class="input__container auth__textfield-container">
                    <input
                        class="textfield auth__textfield -block"
                        name="password"
                        data-submit-on-enter
                        data-clear-error-on-input
                        data-new-password-form-input="password"
                        type="password"
                        placeholder="{{ __('admin::auth.newPassword.form.placeholderPassword') }}"
                        autocomplete="new-password"
                        spellcheck="false"
                        maxlength="50"
                    >
                </div>

                <div class="input__container auth__textfield-container">
                    <input
                        class="textfield auth__textfield -block"
                        name="password-repeat"
                        data-submit-on-enter
                        data-clear-error-on-input
                        data-new-password-form-input="password-repeat"
                        type="password"
                        placeholder="{{ __('admin::auth.newPassword.form.placeholderPasswordRepeat') }}"
                        autocomplete="new-password"
                        spellcheck="false"
                        maxlength="50"
                    >
                </div>
            </div>

            <div class="auth__button-container">
                <button
                    type="button"
                    class="button auth__button -block"
                    data-submit-button
                    data-new-password-form-submit-button
                >
                    <span>{{ __('admin::auth.newPassword.form.submitButtonText') }}</span>
                </button>
            </div>
        </form>

        <div class="auth__form-links">
            <a class="auth__form-link" href="{{ route('admin.login') }}">{{ __('admin::auth.newPassword.form.backToSignIn') }}</a>
        </div>

    </div>

@endsection
