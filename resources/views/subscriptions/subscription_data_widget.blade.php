<section class="content-panel">
    <h3 class="content-panel_title">@lang('common.subscription') #{{ $subscriptionData['id'] }}</h3>
    <ul>
        <li>@lang('common.days_passed'): <strong>{{ data_get($subscriptionData, 'daysPassed') }}</strong></li>
        <li>@lang('common.days_left'): <strong>{{ data_get($subscriptionData, 'daysLeft') }}</strong></li>
        <li>@lang('common.status'): <strong>{{ data_get($subscriptionData, 'status') }}</strong></li>
        <li>@lang('course::common.date_start'): <strong>{{ data_get($subscriptionData, 'start') }}</strong></li>
        <li>@lang('course::common.date_end'): <strong>{{ data_get($subscriptionData, 'end') }}</strong></li>
    </ul>
</section>