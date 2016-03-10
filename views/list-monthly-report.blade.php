@extends('app')

@section('page-header')
    <h2>Monthly Reports</h2>

    <div class="right-wrapper pull-right">
        <ol class="breadcrumbs">
            <li>
                <a href="index.html">
                    <i class="fa fa-home"></i>
                </a>
            </li>
            <li><span>Report Management</span></li>
            <li><span>Monthly Reports</span></li>
        </ol>

        <div class="sidebar-right-toggle"></div>
    </div>
@endsection


@section('content')

    <div class="panel panel-default">
        @include('report-route::list-monthly-report-partial')
    </div>

@endsection
