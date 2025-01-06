@php
    use \App\Enums\Admin\Permission\RoleEnum;

    if (!$user) {
        return;
    }

    $isConsultant = $user->hasRole(RoleEnum::CONSULTANT->value)
@endphp

@if(!$isConsultant)
    <li>
        <a href="#" data-run-artisan-optimize-clear>
            <i class="fa fa-btn fa-eraser"></i> @lang('common.clear_system_cache')
        </a>
    </li>
@endif

<li class="input-sm" style="position: relative; display: block; padding: 10px 15px;">
    <label for="lang-switch" class="sr-only">Language switch</label>
    <select onchange="location = this.value;" id="lang-switch" class="form-control">
        @foreach (config('translatable.locales') as $lang => $name)
            <option @selected($user->lang === $lang) value="{{ route('lang.switch', $lang) }}">
                @lang("admin.filters.language.$lang")
            </option>
        @endforeach
    </select>
</li>

@if(!$isConsultant)
    <li>
        <a href="/" target="_blank">
            <span class="fa fa-btn fa-globe" aria-hidden="true"></span> @lang('sleeping_owl::lang.links.index_page')
        </a>
    </li>
@endif

<li class="dropdown user user-menu" style="margin-right: 20px;">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
        <img src="{{ $user->avatar_url_or_blank }}" class="user-image" alt=""/>
        <span class="hidden-xs">{{ $user->name }}</span>
    </a>
    <ul class="dropdown-menu">
        <!-- User image -->
        <li class="user-header">
            <img src="{{ $user->avatar_url_or_blank }}" class="img-circle" alt=""/>
            <p>{{ $user->name }}</p>
        </li>
        <!-- Menu Footer-->
        <li class="user-footer">
            <a href="{{ route('logout.get') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-btn fa-sign-out"></i> @lang('sleeping_owl::lang.auth.logout')
            </a>
            <form id="logout-form" action="{{ route('logout.post') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
        </li>
    </ul>
</li>

<div id="progress-bar-template" style="display: none" type="text/x-custom-template">
    <svg class="radial-progress" data-percentage="0" viewBox="0 0 80 80">
        <circle class="incomplete" cx="40" cy="40" r="35"></circle>
        <circle class="complete" cx="40" cy="40" r="35"></circle>
        <text class="percentage" x="50%" y="57%" transform="matrix(0, 1, -1, 0, 80, 0)">0%</text>
    </svg>
</div>

@if(!$isConsultant)
    @push('footer-scripts')
        <script>
            jQuery(document).ready(function ($) {
                $('[data-run-artisan-optimize-clear]').on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    let url = '{{ route("admin.artisan.optimize.clear" ) }}';
                    Swal.fire({
                        title: "@lang('common.please_confirm_system_cache_clearing')",
                        icon: 'question',
                        showCancelButton: true,
                        cancelButtonText: '@lang("common.cancel")',
                        didOpen: function () {
                            //
                        },
                    }).then(function (result) {
                        if (result.value) {

                            Swal.fire({
                                title: '{{trans('admin.messages.wait')}}',
                                text: '{{trans('admin.messages.in_progress')}}',
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
                                },
                                success: function (res) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'OK',
                                        html: `<ul style="text-align: left;"><li>${res.message}</li></ul>`,
                                    });
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
@endif
