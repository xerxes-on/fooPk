@if($canRender)
    @if($type === 'normal')
        <section>
            <h2>@lang('course::common.course')</h2>
            <div class="account-challenge">
                <div class="account-challenge_wrapper">
                    <p class="account-challenge_wrapper_title">
                        <strong>{{ $courseData['title']  }}</strong><br>
                        <small>@lang('course::common.date_start'):
                            <time datetime="{{ $courseData['from'] }}">{{ $courseData['from'] }}</time>
                        </small><br>
                        <small>@lang('course::common.date_end'):
                            <time datetime="{{ $courseData['to'] }}">{{ $courseData['to'] }}</time>
                        </small>
                    </p>
                    <p class="account-challenge_wrapper_days">@lang('common.day') <b>{{ $courseData['curDay'] }}</b>
                        / {{ $courseData['duration'] }}
                    </p>
                </div>
            </div>
        </section>
    @elseif($type === 'mini_mobile')
        <li class="challenge-day-info challenge-day-info-row btn-header-group-item main-menu-item-mobile">
            <strong class="challenge-day-info-title">{{ $courseData['title'] }}</strong>
            <span>@lang('common.day')</span>
            <strong>{{ $courseData['curDay'] }}</strong>/<span>{{ $courseData['duration'] }}</span>
        </li>
    @elseif($type === 'mini_desktop')
        <div class="challenge-day-info challenge-day-info-row btn-header-group-item challenge-day-info-desktop">
            <strong class="challenge-day-info-title">{{ $courseData['title'] }}</strong>
            <span>@lang('common.day')</span>
            <strong>{{ $courseData['curDay'] }}</strong>/<span>{{ $courseData['duration'] }}</span>
        </div>
    @endif
@endif

