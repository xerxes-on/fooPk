<div class="row">
    <div class="col-md-3">
        {!! Form::button(trans('common.subscriptions_chargebee_add'), ['type' => 'submit', 'id' => 'chargebee-subscription-add', 'class' => 'btn btn-info']) !!}
    </div>
</div>

<h3 class="text-center">@lang('common.subscription')</h3>
<div id="client-id" data-client-id="{{ $client->id }}"></div>

@if($subscriptions->count() > 0)
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>@lang('common.title') ID</th>
            <th>@lang('common.subscription')</th>
            <th>@lang('common.status')</th>
            <th>@lang('common.next_billing')</th>
            <th>@lang('common.plan_amount')</th>
            <th>@lang('common.since')</th>
            <th>@lang('common.until')</th>
            <th>@lang('common.assignation')</th>
        </tr>
        </thead>

        <tbody>
        @foreach($subscriptions as $index => $subscription)
            <tr @if(data_get($subscription->data, 'status') == 'active') class="success" @endif>
                <td>{{ $index + 1 }}</td>
                <td>{{ trans('common.subscription') .' #'. $subscription->id }} <small>( {{ $subscription->uuid }}
                        )</small></td>
                <td> {{ data_get($subscription->data, 'plan_id', '--') }} </td>
                <td>{{ trans('common.' . data_get($subscription->data, 'status') )}} </td>
                <td>{{ data_get($subscription->data, 'next_billing_at', '--') }}</td>
                <td>{{ data_get($subscription->data, 'mrr', '--') }} {{data_get($subscription->data, 'currency_code', '')}}</td>
                <td>{{ data_get($subscription->data, 'activated_at', '--') }}</td>
                <td>{{ data_get($subscription->data, 'cancelled_at', '--') }}</td>
                <td>
                    @if($subscription->assigned_user_id && $subscription->assigned_user_id != $subscription->user_id)
                        @if($client->id == $subscription->user_id )
                            @lang('common.assigned_to')
                            <a href="{{ route('admin.model.update', ['adminModel' => 'users', 'adminModelId' =>$subscription->assignedClient]) }}">
                                {{ $subscription->assignedClient->full_name }} ({{$subscription->assignedClient->email}}
                                )
                            </a>
                        @else
                            @lang('common.assigned_from')
                            <a href="{{ route('admin.model.update', ['adminModel' => 'users', 'adminModelId' => $subscription->owner]) }}">
                                {{ $subscription->owner->full_name }} ({{$subscription->owner->email}})
                            </a>
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>
@else
    <div>@lang('common.no_subscriptions')</div>
@endif