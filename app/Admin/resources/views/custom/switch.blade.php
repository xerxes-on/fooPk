<div class="form-horizontal {{$extraWrapClass ?? ''}}">
    <div class="form-group form-element-switch row {{ $errors->has($name) ? 'has-error' : '' }}">
        <label class="control-label col-md-9" style="line-height: 23px; padding-top: 0;">{{ $label }}</label>

        <div class="switch-wrapper col-md-3">
            <input {!! $attributes !!} @disabled($readonly) type="checkbox" value="1" @checked($value) />
            <label for="{{ $name }}">{{ $label }}</label>
        </div>
    </div>
</div>