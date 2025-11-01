@extends('admin::app')

@section('content')
    <div class="content__content">
        @include('admin::components.list', [
            'key' => $key ?? null,
            'listData' => $listData ?? null,
        ])
    </div>
@endsection
