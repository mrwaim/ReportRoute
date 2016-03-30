@extends('app')

@section('page-header')
    @include('elements.page-header', ['section_title' => 'Report Management', 'page_title' => 'Monthly Reports'])
@endsection


@section('content')

    <div class="panel panel-default">
        @include('report-route::list-monthly-report-partial')
    </div>

@endsection
