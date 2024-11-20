@php
    /**@var $model \Modules\PushNotification\Models\Notification*/
    $linkContent = '';
@endphp

<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#notificationInfo{{$model->id}}">
    @lang('PushNotification::admin.notification_info_modal.button')
</button>

<div class="modal fade"
     id="notificationInfo{{$model->id}}"
     tabindex="-1"
     role="dialog"
     aria-labelledby="notificationInfo{{$model->id}}Label"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationInfo{{$model->id}}Label">
                    @lang('PushNotification::admin.notification_info_modal.title')
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>@lang('PushNotification::admin.notification_type'): <b>({{$model->type->id}}) {{$model->type->name}}</b></p>
                <hr>
                @foreach($model->translations as $translation)
                    <p>@lang('common.title') {{strtoupper($translation->locale)}}: <b>{{$translation->title}}</b></p>
                    <p>@lang('common.content') {{strtoupper($translation->locale)}}: <b>{{$translation->content}}</b>
                    </p>
                    <hr>
                    @php
                        if($translation->link_title !== null){
                            $linkContent .= sprintf('<p>%s: <b>%s</b></p>', trans('PushNotification::admin.notification_title', ['lang' => strtoupper($translation->locale)]), $translation->link_title);
                        }
                    @endphp
                @endforeach
                @if($model->link !== null)
                    @php echo $linkContent; @endphp
                    <p>@lang('PushNotification::admin.notification_link_url'): <b>{{$model->link}}</b></p>
                @endif
            </div>
        </div>
    </div>
</div>
