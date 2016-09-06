<section class="panel">
    <header class="panel-heading">
        <h2 class="panel-title">User Report for {{DateTime::createFromFormat('!m', $month)->format('F')}}, {{$year}} for {{$for}} {{$report->draft ? '(Draft Report)' : ''}}</h2>
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
                    @if($config->stockist_can_introduce)
                        <th class="text-center">Total Introduction</th>
                    @endif
                    @if($has_bonus)
                        <th class="text-center">Bonus Cash</th>
                    @endif
                    <th class="text-center">Online Payer</th>
                </tr>
                </thead>
                <tbody>
                @foreach($userData as $data)
                    <tr name="bonus_row">
                        <td>
                            @if(!$data->user)
                                N/A
                            @else
                                @link($data->user)
                                @if($data->user->isBlocked())
                                    BLOCKED
                                @endif
                            @endif
                        </td>
                        <td class="text-center">{{$data->user ? $data->user->getDisplayRole() : 'N/A'}}</td>
                        <td class="text-center text-nowrap">{{$data->user ? $data->user->bank_name : 'N/A'}}<br>{{$data->user ? $data->user->bank_account : 'N/A'}}
                        </td>
                        <td class="text-center">{{$data->orders_count}}</td>
                        <td class="text-center">{{$data->total_stockist_count}}</td>
                        @if($config->stockist_can_introduce)
                            <td class="text-center">{{$data->introductions_count}}</td>
                        @endif
                        @if($has_bonus)
                            <td class="text-center">{{$data->bonus_payout_cash}}</td>
                        @endif
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

