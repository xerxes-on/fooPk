<button class="btn btn-primary"
        type="button"
        data-toggle="collapse"
        data-target="#{{$id}}"
        aria-expanded="false"
        aria-controls="{{$id}}">
    @lang('PushNotification::admin.notification_info_modal.button')
    <span class="badge badge-light">
        {{ $count }}
    </span>
</button>

<div class="collapse" id="{{$id}}">
    <div class="card card-body my-2">
        <p>
            @foreach($titles as $title)
                <b class="badge badge-primary">{{ $title }}</b>
            @endforeach
        </p>
    </div>
</div>