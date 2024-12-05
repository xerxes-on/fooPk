<div class="text-left mb-3">
    {!! Form::open(['route' => 'admin.client.course.store', 'method' => 'POST', 'class' => 'form-inline']) !!}
    <input type="hidden" name="user_id" value="{{ $client->getKey() }}">
    <div class="form-group pull-left">
        <label for="course_id" class="sr-only">@lang('course::common.all')</label>
        <select class="form-control" id="course_id" name="course_id" required>
            <option value=""></option>
            @foreach($courses as $key => $course)
                <option value="{{ $key }}">{{ $course }} (ID: {{ $key }})</option>
            @endforeach
        </select>
    </div>

    <div class="form-group col-md-2">
        <div class="input-date input-group">
            <label for="start_at" class="sr-only">@lang('course::common.date_start')</label>
            <input type="text"
                   id="start_at"
                   name="start_at"
                   value="{{ \Carbon\Carbon::now()->format('d.m.Y') }}"
                   class="form-control start_at"
                   required>
        </div>
    </div>
    {!! Form::button(trans('course::common.create'), ['type' => 'submit', 'id' => 'challenge-create', 'class' => 'btn btn-info']) !!}
    {!! Form::close() !!}
</div>

<h3 class="text-center">@lang('course::common.history')</h3>
<div id="user-id" data-user-id="{{ $client->id }}"></div>
@if($userCourses->count() > 0)
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>@lang('common.title') (ID)</th>
            <th>@lang('common.foodpoints')</th>
            <th>@lang('common.duration')</th>
            <th>@lang('common.status')</th>
            <th>@lang('course::common.date_start')</th>
            <th>@lang('course::common.date_end')</th>
            <th></th>
        </tr>
        </thead>

        <tbody>
        @foreach($userCourses as $index => $userCourse)
            @php $challengeIsActive = $userCourse->getActiveDays();
                $status = $userCourse->getStatus();
            @endphp
            <tr @class(['active-challenge', 'success' => $challengeIsActive > 0 && $challengeIsActive <= $userCourse->duration])>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $userCourse->title . ' (#'. $userCourse->getKey() .')' }}</td>
                <td>{{ $userCourse->foodpoints }}</td>
                <td>{{ $userCourse->duration }}</td>
                <th>
                    @lang('course::common.status.' . $status->lowerName())
                </th>
                <td class="start_at">{{ Carbon\Carbon::parse($userCourse->pivot->start_at)->format('d.m.Y') }}</td>
                <td>{{ Carbon\Carbon::parse($userCourse->pivot->ends_at)->format('d.m.Y') }}</td>
                <td class="text-right">
                    <div class="btn-group">
                        @if($challengeIsActive >= 0 && $challengeIsActive <= $userCourse->duration)
                            <button type="button"
                                    class="button-round user-challenge-edit mx-1"
                                    data-userCourse="{{ $userCourse->pivot->getKey() }}"
                                    data-course="{{ $userCourse->id }}"
                                    data-courseStartDate="{{ Carbon\Carbon::parse($userCourse->pivot->start_at)->format('d.m.Y') }}"
                                    title="@lang('common.edit')">
                                <i class="fas fa-pencil-alt fa-lg" aria-hidden="true"></i>
                            </button>
                        @endif

                        @can(\App\Enums\Admin\Permission\PermissionEnum::DELETE_CLIENT_CHALLENGES->value, '\App\Models\Admin')
                            <button type="button"
                                    class="button-round user-challenge-delete"
                                    data-userCourse="{{ $userCourse->pivot->getKey() }}"
                                    title="User Challenge delete">
                                <i class="fas fa-trash-alt fa-lg" aria-hidden="true"></i>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <div>@lang('course::admin.empty_user_courses')</div>
@endif

@push('footer-scripts')
    <script id="hidden-template" type="text/x-custom-template">
        <label for="datetimepicker" class="sr-only">@lang('course::common.date_start')</label>
        <input id="datetimepicker" class="form-control">

        window.foodPunk.i18n = {
        messages_confirmation: "@lang('admin.messages.confirmation')",
        messages_revert_info: "@lang('admin.messages.revert_info')",
        messages_wait: "@lang('admin.messages.wait')",
        messages_in_progress: "@lang('admin.messages.in_progress')",
        common_change_date_title: "@lang('course::common.change_date.title')",
        messages_saved: "@lang('admin.messages.saved')",
        };
    </script>
    <script src="{{ mix('js/admin/client/main.js') }}"></script>
@endpush
