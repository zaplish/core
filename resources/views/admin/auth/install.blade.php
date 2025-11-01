@extends('admin::auth.page')

@section('authContent')

    <h1 class="auth__form-title">
        {!! __('admin::auth.install.form.title') !!}
    </h1>

    <div class="auth__form-description">
        {!! __('admin::auth.install.form.description') !!}
    </div>

    <form class="auth__form -install" data-install-form onsubmit="return false">

        <div class="auth__form-message"></div>

        <input
            class="textfield -h"
            name="csrf"
            data-install-form-input="csrf"
            type="text"
            aria-hidden="true"
            tabindex="-1"
            autocomplete="new-password"
        >

        <div class="auth__textfields">

            <div class="input__container auth__textfield-container">
                <input
                    class="textfield auth__textfield -block"
                    name="name"
                    data-submit-on-enter
                    data-clear-error-on-input
                    data-install-form-input="name"
                    type="text"
                    placeholder="{{ __('admin::auth.install.form.placeholderName') }}"
                    autocomplete="name"
                    spellcheck="false"
                >
            </div>

            <div class="input__container auth__textfield-container">
                <input
                    class="textfield auth__textfield -block"
                    name="email"
                    data-submit-on-enter
                    data-clear-error-on-input
                    data-install-form-input="email"
                    type="text"
                    placeholder="{{ __('admin::auth.install.form.placeholderEmail') }}"
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
                    data-install-form-input="password"
                    type="password"
                    placeholder="{{ __('admin::auth.install.form.placeholderPassword') }}"
                    maxlength="50"
                    autocomplete="new-password"
                >
            </div>
        </div>

        <div class="auth__button-container">
            <button
                type="button"
                class="button auth__button -block"
                data-submit-button
                data-install-form-submit-button
            >
                <span>{{ __('admin::auth.install.form.submitButtonText') }}</span>
            </button>
        </div>
    </form>

@endsection
