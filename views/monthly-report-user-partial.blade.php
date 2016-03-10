<section class="panel">
    <header class="panel-heading">
        <h2 class="panel-title">User Report for {{$month}} {{$year}}</h2>
    </header>

    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-condensed mb-none">
                <thead>
                <tr>
                    <th>User</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">Bank Account</th>
                    <th class="text-center">Approved Orders</th>
                    <th class="text-center">Total Stockist</th>
                    <th class="text-center">Total Introduction</th>
                    <th class="text-center">Bonus Cash</th>
                    <th class="text-center">Online Payer</th>
                </tr>
                </thead>
                <tbody>
                @foreach($userData as $data)
                    <tr name="bonus_row">
                        <td>@link($data->user)
                            @if($data->user->isBlocked())
                                BLOCKED
                            @endif
                        </td>
                        <td class="text-center">{{$data->user->role->name}}</td>
                        <td class="text-center text-nowrap">{{$data->user->bank_name}}<br>{{$data->user->bank_account}}
                        </td>
                        <td class="text-center">{{$data->orders_count}}</td>
                        <td class="text-center">{{$data->total_stockist_count}}</td>
                        <td class="text-center">{{$data->introductions_count}}</td>
                        <td class="text-center">{{$data->bonus_payout_cash}}</td>
                        <td class="text-center">
                            @if($data->online_payer)
                                Yes
                            @else
                                No
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

