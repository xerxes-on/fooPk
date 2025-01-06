<div class="row">
    <div class="col-md-3">
        {!! Form::open(['route' => ['admin.client.create-subscription', $client->id], 'method' => 'POST', 'class' => 'form-inline']) !!}
        {!! Form::button(trans('common.subscription_create'), ['type' => 'submit', 'id' => 'subscription-create', 'class' => 'btn btn-info']) !!}
        {!! Form::close() !!}
    </div>
</div>

<h3 class="text-center">@lang('common.subscription_history')</h3>

@if($subscriptions->count() > 0)
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>@lang('common.title')</th>
            <th>@lang('common.days_passed')</th>
            <th>@lang('common.days_left')</th>
            <th>@lang('common.status')</th>
            <th>@lang('course::common.date_start')</th>
            <th>@lang('course::common.date_end')</th>
        </tr>
        </thead>

        <tbody>
        @foreach($subscriptions as $index => $subscription)
            <tr @if($subscription->active) class="success" @endif>
                <td>{{ $index + 1 }}</td>
                <td>{{ trans('common.subscription') . " (#{$subscription->id})" }}</td>
                <td>
                    @php $from = Carbon\Carbon::parse($subscription->created_at); @endphp

                    @if($subscription->active && !is_null($subscription->ends_at))
                        @php $to = Carbon\Carbon::now(); @endphp
                    @else
                        @php $to = Carbon\Carbon::parse($subscription->ends_at); @endphp
                    @endif

                    {{ $from->diffInDays($to, false) + 1 }}
                </td>
                <td>
                    @php $from = Carbon\Carbon::now(); @endphp

                    @if($subscription->active && !is_null($subscription->ends_at))
                        @php $to = Carbon\Carbon::parse($subscription->ends_at); @endphp

                        {{ $from->diffInDays($to, false) + 1 }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    {{ $subscription->active ? trans('common.active') : trans('common.finished') }}
                </td>
                <td>{{ parseDateString($subscription->created_at,'d.m.Y') }}</td>
                <td>
                    @if(!is_null($subscription->ends_at))
                        {{ parseDateString($subscription->ends_at,'d.m.Y') }}
                    @else
                        <span style="font-size: 20px; line-height: 1px;">&#8734</span>
                    @endif

                    @can(\App\Enums\Admin\Permission\PermissionEnum::MANAGE_SUBSCRIPTION->value, '\App\Models\Admin')
                        <div class="btn-group pull-right">
                            @if($subscription->active)
                                <button type="button"
                                        class="button-round user-subscription-edit"
                                        data-subscription="{{ $subscription->id }}"
                                        data-subscriptionStartDate="@if(!is_null($subscription->ends_at)){{ parseDateString($subscription->ends_at,'d.m.Y') }}@endif"
                                        title="Set date end">
                                    <i class="fas fa-pencil-alt fa-lg" aria-hidden="true"></i>
                                </button>

                                <button type="button"
                                        class="button-round user-subscription-stop"
                                        data-subscription="{{ $subscription->id }}"
                                        title="Stop subscription">
                                    <i class="fa fa-stop-circle fa-lg" aria-hidden="true"></i>
                                </button>
                            @endif
                                @can(\App\Enums\Admin\Permission\PermissionEnum::DELETE_SUBSCRIPTION->value, '\App\Models\Admin')
                                    <button type="button" class="button-round user-subscription-delete"
                                            data-subscription="{{ $subscription->id }}"
                                            title="Delete subscription"> {{--TODO: translate--}}
                                        <i class="fa fa-trash fa-lg" aria-hidden="true"></i>
                                    </button>
                                @endcan
                        </div>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <div>@lang('common.no_subscriptions')</div>
@endif
