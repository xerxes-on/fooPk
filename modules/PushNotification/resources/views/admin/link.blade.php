<div class="mb-3">
    <h2>@lang('PushNotification::admin.notification_link')</h2>
    <div class="row">
        <div class="col-6">
            <div class="form-group form-element-text">
                <label for="link_de" class="control-label">@lang('PushNotification::admin.notification_title', ['lang' => 'DE'])</label>
                <input class="form-control"
                       type="text"
                       id="link_de"
                       name="de[link_title]"
                       maxlength="190"
                       value="{{old('de.link_title', $model?->translations?->where('locale', 'de')?->first()?->link_title)}}">
                @include('admin::partials.error', ['errorsCollection' => $errors->get('de.link_title')])
            </div>
        </div>
        <div class="col-6">
            <div class="form-group form-element-text">
                <label for="link_en" class="control-label">@lang('PushNotification::admin.notification_title', ['lang' => 'EN'])</label>
                <input class="form-control"
                       type="text"
                       id="link_en"
                       name="en[link_title]"
                       maxlength="190"
                       value="{{old('en.link_title', $model?->translations?->where('locale', 'en')?->first()?->link_title)}}">
                @include('admin::partials.error', ['errorsCollection' => $errors->get('en.link_title')])
            </div>
        </div>
        <div class="col-12">
            <p><small class="form-element-helptext">@lang('admin_help_text.notification_link.title')</small></p>
        </div>
        <div class="col-12">
            <div class="form-group form-element-text">
                <label for="link" class="control-label">@lang('PushNotification::admin.notification_link_url')</label>
                <input class="form-control"
                       type="text"
                       placeholder="https://www.example.com"
                       id="link"
                       name="link"
                       maxlength="190"
                       value="{{old('link', $model->link)}}">
                <p><small class="form-element-helptext">@lang('admin_help_text.notification_link.url')</small></p>
                @include('admin::partials.error', ['errorsCollection' => $errors->get('link')])
            </div>
        </div>
    </div>
</div>
