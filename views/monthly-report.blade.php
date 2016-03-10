@extends('app')

@section('page-header')
    <h2>Monthly Report for {{$month}} {{$year}} : {{$filter == 'all' ? 'All Users' : 'Active Users'}}</h2>

    <div class="right-wrapper pull-right">
        <ol class="breadcrumbs">
            <li>
                <a href="index.html">
                    <i class="fa fa-home"></i>
                </a>
            </li>
            <li><span>Report Management</span></li>
            <li><span>Monthly Reports</span></li>
            <li><span>{{$month}} {{$year}}</span></li>
        </ol>

        <div class="sidebar-right-toggle"></div>
    </div>
@endsection


@section('content')

    @include('elements.success-message-partial')

    <div class="panel panel-default no-print">
        @include('report-route::monthly-report-summary-partial')
    </div>

    <div class="panel panel-default">
        @include('report-route::monthly-report-user-partial')
    </div>

    <div class="panel panel-default no-print">
        <section class="panel">
            <header class="panel-heading">
                <h2 class="panel-title">Admin</h2>
            </header>

            <div class="panel-body">
                <form enctype="multipart/form-data" class="form-horizontal" role="form" method="POST"
                      action="{{ url("/report/update-monthly-report/$month/$year") }}" id="report-management-update-report">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    @include('elements.confirm-dialog', ['confirmId' => 'ReportUpdate', 'confirmTitle' => 'Confirm report update', 'confirmText' => 'Are you sure that you want to refresh the report? Previous bonus approval and reject data will be lost.', 'confirmAction' => 'Update Report'])
                </form>
            </div>
        </section>
    </div>
@endsection

@section('feedback')
@endsection
