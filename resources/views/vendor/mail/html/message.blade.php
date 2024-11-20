@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url_meinplan')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} Foodpunk GmbH. @lang('All rights reserved.') <br>
            @lang('email.footer.full_address')<br>
            <a href="https://foodpunk.com/de/impressum/">@lang('email.footer.address')</a>
        @endcomponent
    @endslot
@endcomponent
