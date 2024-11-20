@php
    use Modules\PushNotification\Enums\UserGroupOptionEnum;
    // todo: plans for improvement, use a single modal and use ajax to load the data
@endphp

<p>
    @if($model->report['targets_hit'] > 0 && $model->report['errors'])
        @lang('PushNotification::admin.notification_report.status.with_errors')
    @elseif($model->report['targets_hit'] === 0 || $model->report['errors'])
        @lang('PushNotification::admin.notification_report.status.failed')
    @else
        @lang('PushNotification::admin.notification_report.status.success')
    @endif
</p>

<button type="button" class="btn btn-info" data-toggle="modal" data-target="#notificationReport{{$model->id}}">
    @lang('PushNotification::admin.notification_report.modal.button')
</button>

<div class="modal fade"
     id="notificationReport{{$model->id}}"
     tabindex="-1"
     role="dialog"
     aria-labelledby="notificationReport{{$model->id}}Label"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationReport{{$model->id}}Label">
                    @lang('PushNotification::admin.notification_report.modal.title')
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center">@lang('PushNotification::admin.notification_report.modal.error')</p>
                <ul class="list-group list-group-flush">
                    @foreach($model->report['errors'] as $key =>$error)
                        <li class="list-group-item">
                            <span class="badge badge-primary badge-pill text-uppercase">{{$error['count']}}</span>
                            {{$error['title']}}
                        </li>
                    @endforeach
                </ul>
                <hr>
                <p class="text-center">@lang('PushNotification::admin.notification_report.modal.info')</p>
                <ul class="list-group list-group-flush">
                    @foreach($model->report['info'] as $key =>$error)
                        <li class="list-group-item">
                            @if(is_array($error))
                                <span class="badge badge-primary badge-pill text-uppercase">{{$key}}</span>
                                <ol class="list-group">
                                    @foreach($error as $key => $value)
                                        <li class="list-group-item">{{$value}}</li>
                                    @endforeach
                                </ol>
                            @else
                                {{$error}}
                            @endif
                        </li>
                    @endforeach
                </ul>
                <hr>
                <p class="text-center">@lang('PushNotification::admin.notification_report.modal.params')</p>
                <ul class="list-group list-group-flush">
                    @if(isset($model->report['params'][UserGroupOptionEnum::NAME]))
                        <li class="list-group-item">@lang('PushNotification::admin.notification_report.modal.user_group')
                            <span class="badge badge-primary badge-pill text-uppercase">{{$model->report['params'][UserGroupOptionEnum::NAME]}}</span>
                        </li>
                    @endif
                    @if(isset($model->report['params']['course']))
                        <li class="list-group-item">@lang('course::common.course')
                            <span class="badge badge-primary badge-pill text-uppercase">{{$model->report['params']['course']}}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
