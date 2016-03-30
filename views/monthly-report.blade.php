@default($has_bonus, true)

@extends('app')

@section('page-header')
    @include('elements.page-header', ['section_title' => 'Report Management', 'page_title' => 'View Report'])
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
