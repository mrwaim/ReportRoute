@extends('app')

@section('page-header')
    @include('elements.page-header', ['section_title' => 'Report Management', 'page_title' => 'Monthly Reports'])
@endsection


@section('content')
    <section class="panel">
        <header class="panel-heading">
            <h2 class="panel-title">Sales Report</h2>
        </header>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed mb-none">
                    <thead>
                    <tr>
                        <th class="text-center">Unit Name</th>
                        <th class="text-center">Count</th>
                        @if($filter == 'pl')
                        <th class="text-center">Organization</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $item)
                        <tr>
                            <td>{{ $item->productUnit->name }}</td>
                            <td>{{ $item->total_quantity }}</td>
                            @if($filter == 'pl')
                            <td>{{ $item->orderItem->organization->name }}</td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{--{!! $list->render() !!}--}}
            </div>
        </div>
    </section>


@endsection
