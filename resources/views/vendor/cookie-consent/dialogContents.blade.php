@if (!request()->has('is_app') && !Cookie::get('is_app'))
    <div class="js-cookie-consent cookie-consent alert alert-warning">

    <span class="cookie-consent__message">
        {!! trans('cookie-consent::texts.message') !!}
    </span>

        <button class="js-cookie-consent-agree cookie-consent__agree btn  btn-outline-warning">
            {{ trans('cookie-consent::texts.agree') }}
        </button>

    </div>
@endif
