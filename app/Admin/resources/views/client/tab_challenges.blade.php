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
    </script>
    <script>
        jQuery(document).ready(function ($) {
            $('.start_at').datepicker({
                dateFormat: 'dd.mm.yy',
            });
            $('.user-challenge-edit').on('click', function (e) {
                let userCourseId = $(this).attr('data-userCourse'),
                    courseId = $(this).attr('data-course'),
                    courseStartDate = $(this).attr('data-courseStartDate'),
                    pattern = /(\d{2})\.(\d{2})\.(\d{4})/,
                    dt = new Date(courseStartDate.replace(pattern, '$3-$2-$1'));

                Swal.fire({
                    title: "@lang('course::common.change_date.title')",
                    html: $('#hidden-template').html(),
                    icon: 'question',
                    showCancelButton: true,
                    didOpen: function () {
                        $('#datetimepicker').datepicker({
                            dateFormat: 'dd.mm.yy',
                            defaultDate: dt,
                        }).datepicker('setDate', dt);
                    },
                }).then(function (result) {
                    if (!result.value) {
                        return;
                    }
                    const date = $('#datetimepicker').val();
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
                        url: "{{ route('admin.client.course.edit') }}",
                        dataType: 'json',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            user_course_id: userCourseId,
                            course_id: courseId,
                            start_at: date,
                        },
                        success: function (result) {
                            location.reload();
                        },
                        error: function (result) {
                            Swal.hideLoading();
                            Swal.fire({
                                title: 'Error!',
                                html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                                icon: 'error',
                            });
                        },
                    });
                });
            });

            $('.user-challenge-delete').on('click', function (e) {
                let userCourseId = $(this).attr('data-userCourse');

                Swal.fire({
                    title: '{{trans('admin.messages.confirmation')}}',
                    text: '{{trans('admin.messages.revert_info')}}',
                    type: 'warning',
                    showCancelButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (!result.value) {
                        return;
                    }
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
                        type: 'DELETE',
                        url: "{{ route('admin.client.course.destroy') }}",
                        dataType: 'json',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            user_course_id: userCourseId,
                        },
                        success: function (result) {
                            location.reload();
                        }, error: function (result) {
                            Swal.hideLoading();
                            Swal.fire({
                                title: 'Error!',
                                html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                                icon: 'error',
                            });
                        },
                    });
                });
            });
        });
    </script>
@endpush
