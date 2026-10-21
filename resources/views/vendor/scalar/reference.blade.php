{{-- Overrides the package view to load the pinned bundle with Subresource Integrity --}}
@extends('scalar::layout')

@section('content')
    <div id="app"></div>

    <script src="{{ \Scalar\Scalar::cdn() }}" integrity="{{ config('scalar.cdn_integrity') }}" crossorigin="anonymous"></script>

    <script>
       Scalar.createApiReference('#app', {!! \Scalar\Scalar::configuration() !!})
    </script>
@endsection
