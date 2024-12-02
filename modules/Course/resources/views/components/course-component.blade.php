<li class="course-list-item">
    @if ($isForPurchase === false && $isGuide !== true )
        <div class="ribbon"><span>{{trans('course::common.status.' . $courseStatus->lowerName())}}</span></div>
    @endif
    <div class="course-card" data-id="{{ $course->id }}"
         style="background-image: url('{{ asset($course->image->url('medium')) }}');'">
        <div class="course-card-container">
            <div class="course-card-button">
                @if(isset($course->minimum_start_at_for_js))
                    <button class="subbtn js-unlock-course" type="submit">@lang('course::common.unlock')</button>
                @else
                    <a class="subbtn" href="{{ route('articles.list') }}">@lang('course::common.view')</a>
                @endif
            </div>
        </div>
        <div class="course-card-info-container">
            <h2 class="course-title">{{ $course->title }}</h2>
            <p class="course-author">by FOODPUNK</p>
            <p class="course-price">{!! $coursePrice !!}</p>
            @if($isForPurchase)
                <p>{{$course->minimum_start_at_for_js}}</p>
            @endif
            <div class="course-card-info-details">
                <div class="course-card-info-details-container">
                    @if($isForPurchase)
                        <div class="course-details-box course-details-box-info js-createByDate">
                            <label for="startat_{{$course->id}}">@lang('common.Select your Date')</label>
                            <div class="input-group">
                                {!! Form::text('start_at', null, [
                                        'id' => "startat_$course->id",
                                        'required' => true,
                                        'class' => 'shopping-list_panel_content_input input-group date',
                                        'placeholder' => 'dd.mm.yyyy',
                                        'autocomplete' => 'off',
                                        'data-provide' => 'datepicker',
                                        'data-date-format' => 'dd.mm.yyyy',
                                        'data-date-autoclose' => 'true',
                                        'data-date-start-date' => $course->minimum_start_at_for_js,
                                        'data-date-today-highlight' => 'true',
                                        'data-date-week-start' => '1',
                                        'data-date-language' => app()->getLocale(),
                                    ]) !!}
                                <div class="input-group-addon shopping-list_panel_content_calendar">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>
                        <div class="course-details-box course-details-box-info">
                            <span class="course-details-day">@lang('course::common.total')</span>
                            <span class="course-details-duration" id="duration">{{ $course->duration }}</span>
                            <span class="course-details-days">@lang('course::common.days')</span>
                        </div>
                    @else
                        <div class="course-details-box course-details-box-info course-details-box-info--big">
                            <p>@lang('course::common.date_start')
                                <br>{{parseDateString($course->pivot->start_at,'d.m.Y')}}
                            </p>
                            @if($courseIsFinished)
                                <button class="btn btn-orange js-restart-course"
                                        data-id="{{ $course->id }}"
                                        type="button"
                                        data-start-at="{{ $restartDate }}">@lang('course::common.restart.button')</button>
                            @else
                                <button class="btn btn-orange js-change-course-date"
                                        data-id="{{ $course->id }}"
                                        type="button"
                                        data-start-at="{{ $restartDate }}">@lang('course::common.change_date.button')</button>
                            @endif
                        </div>
                        <div class="course-details-box course-details-box-info course-details-box-info--small">
                        <span class="vertical-text">
                            <span>@lang('course::common.total')</span>
                            <b class="heading-text">{{ $course->duration}}</b>
                            <span>@lang('course::common.days')</span>
                        </span>
                        </div>
                    @endif
                </div>
                <p class="course-description">{{ $course->description }}</p>
            </div>
        </div>
    </div>
</li>