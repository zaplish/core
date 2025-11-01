@extends('admin::app')

@section('content')
    <div class="content__content">
        @include('admin::components.list', [
            'key' => 'content-types',
        ])
    </div>
@endsection
