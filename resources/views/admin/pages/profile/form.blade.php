@extends('admin::app')

@section('content')
    <div class="content__content">
        @include('admin::components.form', [
            'key' => $key ?? null,
            'formData' => $formData ?? null,
        ])

        <div class="delete-account__container">
            <span class="delete-account__button link -secondary" data-delete-account-modal>{{ __('admin::users.profile.delete.linkText') }}</span>
        </div>

        <script>
            window.deleteAccountModalTexts = {
                title: {!! json_encode(__('admin::users.profile.delete.modalTitle')) !!},
                text: {!! json_encode(__('admin::users.profile.delete.modalText')) !!},
                cancelButtonText: {!! json_encode(__('admin::users.profile.delete.modalCancelButtonText')) !!},
                submitButtonText: {!! json_encode(__('admin::users.profile.delete.modalSubmitButtonText')) !!},
                textfieldPlaceholder: {!! json_encode(__('admin::users.profile.delete.modalTextfieldPlaceholder')) !!},
            };
        </script>
    </div>
@endsection
