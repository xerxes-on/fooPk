<section class="content-panel">
    <h3 class="content-panel_title">@lang('common.subscription') <small>#{{ $subscriptionData['id'] }}</small></h3>
    <ul>
        <li>{{ strtolower(trans('common.status')) }}: <strong>{{ data_get($subscriptionData, 'status') }}</strong></li>
        {{--        <li>@lang('common.billing_period') }<strong>{{ data_get($subscriptionData, 'billing_period') . ' ' . data_get($subscriptionData, 'billing_period_unit') }}</strong></li>--}}

        @if(data_get($subscriptionData, 'cancelled_at'))
            <li>@lang('common.cancelled_at'): <strong>{{ data_get($subscriptionData, 'cancelled_at') }}</strong></li>
            <li>@lang('common.cancel_reason'): <strong>{{ data_get($subscriptionData, 'cancel_reason_code') }}</strong>
            </li>
        @else
            <li>@lang('common.activated_at'): <strong>{{ data_get($subscriptionData, 'activated_at') }}</strong>
            </li>
            <li>@lang('common.next_billing_at'): <strong>{{ data_get($subscriptionData, 'next_billing_at') }}</strong>
            </li>
        @endif
    </ul>
</section>