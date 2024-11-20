@php
    use Modules\PushNotification\Enums\UserGroupOptionEnum;
    /**
    * In order to build the form, you need to build inputs like this:
    * `<input type="type" name="params[name]" value="value">`.
    * leave hidden field with name intact!
    * change translations to PushNotification::admin.notification_config when adding new param fields
    */
@endphp
<p>@lang('PushNotification::admin.notification_config')</p>
<form>
    {{Form::input('hidden', 'id', $id)}}

    <fieldset class="mb-3">
        <legend>@lang('PushNotification::admin.notification_dispatch_options.user_groups')<sup class="text-danger">*</sup></legend>
        @foreach(UserGroupOptionEnum::cases() as $option)
            <div class="form-check form-check-inline">
                <input class="form-check-input"
                       type="radio"
                       name="params[{{UserGroupOptionEnum::NAME}}]"
                       id="{{UserGroupOptionEnum::NAME . $option->name}}"
                       value="{{$option->value}}"
                        @checked($option === UserGroupOptionEnum::DEFAULT)>
                <label class="form-check-label"
                       for="{{UserGroupOptionEnum::NAME . $option->name}}">{{$option->name}}</label>
            </div>
        @endforeach
    </fieldset>

    <fieldset class="overflow-hidden mb-3">
        <legend>@lang('course::common.courses')</legend>
        @if ($courses->isNotEmpty())
            <div class="form-row">
                <div class="col-7">
                    <label for="course_id">@lang('PushNotification::admin.notification_dispatch_options.course.label_course')</label>
                    <select name="params[course][id]" id="course_id" class="form-control">
                        <option value="" readonly hidden>-</option>
                        @foreach($courses as $id => $title)
                            <option value="{{$id}}">{{$title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="course_status">@lang('PushNotification::admin.notification_dispatch_options.course.label_status')</label>
                    <select name="params[course][status]" id="course_status" class="form-control">
                        <option value="" readonly hidden>-</option>
                        @foreach($courseStatus as $id => $title)
                            <option value="{{$id}}">@lang('course::common.status.' . strtolower($title))</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    </fieldset>
</form>
