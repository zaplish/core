@extends('admin::app')

@section('content')
    <div class="content__content">
        @include('admin::components.form', [
            'key' => $key ?? null,
            'formData' => $formData ?? null,
        ])
    </div>
@endsection
