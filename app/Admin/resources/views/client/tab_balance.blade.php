<div class="row">
    <div class="col-md-12">
        <div class="info-box">
            <span class="info-box-icon bg-teal">
                <b>FP</b>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">@lang('common.client_current_balance'):</span>
                <span class="info-box-number">{{ $client->balance }}</span>
            </div>
        </div>
    </div>
    @can(\App\Enums\Admin\Permission\PermissionEnum::MANAGE_CLIENT_BALANCE->value, '\App\Models\Admin')
        <div class="col-md-12">
            <button type="button" id="add-deposit" class="btn btn-info" onclick="window.FoodPunk.functions.deposit()">
                <span class="ladda-label">@lang('common.deposit')</span>
            </button>

            <button type="button" id="withdraw-balance" class="btn btn-info"
                    onclick="window.FoodPunk.functions.withdraw()">
                <span class="ladda-label">@lang('common.withdraw')</span>
            </button>
        </div>
    @endcan
</div>

<h3 class="text-center">@lang('common.transaction_history')</h3>

@if($client->transactions()->count() > 0)
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>@lang('common.type')</th>
            <th>@lang('common.data')</th>
            <th>@lang('common.amount')</th>
            <th>@lang('common.transaction_id')</th>
            <th>@lang('common.notes')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($client->transactions()->latest()->get() as $transaction)
            @php $class = $transaction->type === $transaction::TYPE_DEPOSIT ? 'table-success' : 'table-danger'; @endphp

            <tr class="{{ $class }}">
                <td>{{ $transaction->id }}</td>
                <td>{{ $transaction->type }}</td>
                <td>{{ parseDateString($transaction->updated_at,'d.m.Y') }}</td>
                <td>{{ $transaction->amount }}</td>
                <td>{{ $transaction->uuid }}</td>
                <td>{{ array_key_exists('description', (array)$transaction->meta) ? $transaction->meta['description'] : '' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <div>@lang('common.transaction_empty')</div>
@endif