<section class="panel">
    <header class="panel-heading">
        <h2 class="panel-title">Reports Available</h2>
    </header>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-condensed mb-none">
                <thead>
                <tr>
                    <th class="text-center">Date</th>
                    <th class="text-center">Order Count</th>
                    <th class="text-center">Approved Order Count</th>
                    <th class="text-center">New Users Count</th>
                    <th class="text-center">Total Users Count</th>
                    <th class="text-center">Total Revenue</th>
                    <th class="text-center">Bonus</th>
                </tr>
                </thead>
                <tbody>
                @foreach($list as $item)
                    <tr name="report_row">
                        <td class="text-center">
                            {{$item->year}}
                            /{{$item->month}}
                            <br/>
                            <a name="report_link"
                               href='/report/monthly-report/{{$item->year}}/{{$item->month}}/all'>All Users</a> |
                            <a name="report_link"
                               href='/report/monthly-report/{{$item->year}}/{{$item->month}}/active'>Active Users</a>
                        </td>
                        <td class="text-center">{{$item->orders_count}}</td>
                        <td class="text-center">{{$item->approved_orders_count}}</td>
                        <td class="text-center">{{$item->new_users_count}}</td>
                        <td class="text-center">{{$item->total_users_count}}</td>
                        <td class="text-center">{{$item->total_revenue}}</td>
                        <td class="text-center">{{$item->bonus_payout_cash}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {!! $list->render() !!}
        </div>
    </div>
</section>

