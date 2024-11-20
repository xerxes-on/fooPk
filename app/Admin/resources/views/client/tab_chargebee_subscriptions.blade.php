<div class="row">
    <div class="col-md-3">
        {!! Form::button(trans('common.subscriptions_chargebee_add'), ['type' => 'submit', 'id' => 'chargebee-subscription-add', 'class' => 'btn btn-info']) !!}
    </div>
</div>

<h3 class="text-center">@lang('common.subscription')</h3>

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

@push('footer-scripts')
    <script>
        jQuery(document).ready(function ($) {

            $('#chargebee-subscription-add').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                let url = '{{ route("admin.client.assign-chargebee-subscription" ) }}';

                Swal.fire({
                    title: 'Enter Chargebee subscription id',
                    html: '<input id="chargebee-subscription-id" class="form-control" /><span id="chargebee-subscription-id-info-text"></span>',
                    icon: 'question',
                    didOpen: function () {
                        //
                    },
                }).then(function (result) {
                    if (result.value) {

                        let subscriptionId = $('#chargebee-subscription-id').val();

                        Swal.fire({
                            title: 'Please Wait..!',
                            text: 'Is working..',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: 'POST',
                            url: url,
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                chargebee_subscription_id: subscriptionId,
                                client_id: {{ $client->id }}
                            },
                            success: function (result) {
                                location.reload();
                            },
                            error: function (data) {
                                let response = JSON.parse(data.responseText),
                                    errorString = '<ul style="text-align: left;">';
                                $.each(response.errors, function (key, value) {
                                    errorString += '<li>' + value + '</li>';
                                });
                                errorString += '</ul>';

                                Swal.fire({
                                    icon: 'error',
                                    title: response.message,
                                    html: errorString,
                                });
                            },
                        });
                    }
                });
            });
        });
    </script>
@endpush