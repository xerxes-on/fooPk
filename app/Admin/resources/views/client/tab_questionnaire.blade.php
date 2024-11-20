<div class="form-inline">

    <div class="form-group mb-2">
        @if($questionnaireExist)
            <a href="{{ route('admin.clients.questionnaire.edit', ['clientId' => $clientID]) }}"
               class="btn btn-primary">
                @lang('admin.questionnaire.buttons.edit')
            </a>
        @else
            <a href="{{ route('admin.clients.questionnaire.create', ['clientId' => $clientID]) }}"
               class="btn btn-primary">
                @lang('admin.questionnaire.buttons.create')
            </a>
        @endif
    </div>
    @if($questionnaireExist)
        <div class="form-group mx-sm-3 mb-2">
            <input id="approve_formular"
                   type="checkbox"
                   class="form-check-input"
                   name="approve_formular"
                    @checked($latestQuestionnaire->is_approved)>
            <label for="approve_formular" class="form-check-label">@lang('admin.questionnaire.buttons.approve')</label>
        </div>
    @endif

    @if($questionnaireExist)
        <div class="form-group mx-sm-3 mb-2">
            <input id="toggle_formular"
                   type="checkbox"
                   class="form-check-input"
                   name="toggle_formular"
                    @checked($latestQuestionnaire->is_editable)>
            <label for="toggle_formular"
                   class="form-check-label">@lang('admin.questionnaire.buttons.enable_user_editing')</label>
        </div>
    @endif
</div>

{{-- Latest questionnaire base questions --}}
@if($questionnaireExist && !empty($latestQuestionnaire))
    <table class="table">
        <caption class="heading-caption">@lang('admin.questionnaire.labels.base_questions')</caption>
        <thead>
        <tr>
            <th>#</th>
            <th style="width: 48%">@lang('admin.questionnaire.labels.question')</th>
            <th @style(['width: 25%' => $questionnaireCount >= 2])>
                {{ sprintf('%s (#%s - %s)', trans('admin.questionnaire.labels.answer'), $latestQuestionnaire->id, parseDateString($latestQuestionnaire->created_at,'d.m.Y')) }}
            </th>
            <th @class(['compare-answer-title', 'hidden' => $questionnaireCount < 2]) style="width: 25%">
                @lang('admin.questionnaire.labels.compare_answer') <span class="compare-answer-id"></span>
            </th>
        </tr>
        </thead>

        <tbody>
        @foreach($baseQuestions as $question)
            <tr data-answer="{{ $question->slug }}">
                <td>{{ $question->id }}</td>
                <td>@lang("questionnaire.questions.$question->slug.title")</td>
                <td>{{$latestAnswers[$question->slug] ?? ''}}</td>
                <td @class(['compare-answer', 'hidden' => $questionnaireCount < 2])></td>
            </tr>
        @endforeach
        </tbody>

    </table>
@endif

@if($questionnaireExist && $questionnaireCount > 0)
    <table class="table">
        <caption class="heading-caption">@lang('admin.questionnaire.labels.history')</caption>
        <thead>
        <tr>
            <th>#</th>
            <th>@lang('admin.questionnaire.labels.name')</th>
            <th>@lang('admin.questionnaire.labels.date')</th>
            <th>@lang('admin.questionnaire.labels.creator')</th>
            <th>@lang('admin.questionnaire.labels.creation_method')</th>
            <th>@lang('admin.questionnaire.labels.action')</th>
        </tr>
        </thead>

        <tbody>
        @php $index = 1; @endphp
        @foreach($clientQuestionnaire as $questionnaire)
            <tr @style(['background-color: aliceblue' => $questionnaire->id === $latestQuestionnaire->id])>
                <td>{{ $index++ }}</td>
                <td>{{ sprintf('%s (#%s)', trans('questionnaire.page_title'),$questionnaire->id) }}</td>
                <td>{{ $questionnaire->created_at }}</td>
                <td>
                    @php $creator = $questionnaire->creator; @endphp
                    {{ $creator === null ? trans('common.user') : "$creator->name ($creator->email)" }}
                </td>
                <td>{{ $questionnaire->creation_method->ucName() }}</td>
                <td>
                    @if($questionnaire->id !== $latestQuestionnaire->id)
                        <button type="button"
                                class="button-round bg-transparent compare-formular"
                                data-formular="{{ $questionnaire->id }}"
                                title="@lang('admin.questionnaire.labels.compare_formular')">
                            <i class="fa fa-compress" aria-hidden="true"></i>
                        </button>
                    @else
                        @lang('admin.questionnaire.labels.current')
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <div>@lang('admin.questionnaire.messages.error.no_history')</div>
@endif

{{-- marketings questions --}}
@if(!empty($answersMarketing))
    <table class="table">
        <caption class="heading-caption">@lang('admin.questionnaire.labels.marketing_questions')</caption>
        <thead>
        <tr>
            <th>#</th>
            <th style="width: 48%">@lang('admin.questionnaire.labels.question')</th>
            <th>@lang('admin.questionnaire.labels.answer')</th>
        </tr>
        </thead>

        <tbody>
        @foreach($questionsMarketing->answers as $data)
            <tr>
                <td>{{ $data->question->id }}</td>
                <td>@lang("questionnaire.questions.{$data->question->slug}.title")</td>
                <td>{{$answersMarketing[$data->question->slug] ?? 'ooops'}}</td>
            </tr>
        @endforeach
        </tbody>

    </table>
@endif

@push('footer-scripts')
    {{-- TODO: Move to separate file --}}
    <script>
        jQuery(document).ready(function ($) {

            $('#approve_formular').change(function () {
                let approve = $(this).prop('checked');

                Swal.fire({
                    title: '{{trans('admin.messages.confirmation')}}',
                    text: '{{trans('admin.messages.revert_warning')}}',
                    icon: 'warning',
                    showCancelButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                }).then((result) => {
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
                            url: "{{ route('admin.client.questionnaire.approve') }}",
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                userId: '{{ $clientID }}',
                                approve: approve,
                            },
                            success: function (data) {
                                if (data.success === true) {
                                    Swal.hideLoading();
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{trans('admin.messages.saved')}}',
                                        html: data.message,
                                    });
                                    if (approve) location.reload();
                                } else {
                                    Swal.hideLoading();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        html: data.message,
                                    });
                                }
                            },
                            error: function (data) {
                                let error = JSON.parse(data.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: error.message,
                                });
                            },
                        });
                    } else {
                        $(this).prop('checked', !approve);
                    }
                });
            });

            $('#toggle_formular').change(function () {
                let currentState = $(this).prop('checked');
                Swal.fire({
                    title: '{{trans('admin.messages.confirmation')}}',
                    text: '{{trans('admin.messages.revert_info')}}',
                    icon: 'warning',
                    showCancelButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                }).then((result) => {
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
                            url: "{{ route('admin.clients.questionnaire.toggle') }}",
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                clientId: {{ $clientID }},
                                is_editable: currentState,
                            },
                            success: function (data) {
                                if (data.success === true) {
                                    Swal.hideLoading();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Changes has been applied!',
                                        html: data.message,
                                    });
                                } else {
                                    Swal.hideLoading();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        html: data.message,
                                    });
                                }
                            },
                            error: function (data) {
                                console.log(data);
                                let error = JSON.parse(data.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: error.message,
                                });
                            },
                        });
                    } else {
                        $(this).prop('checked', currentState);
                    }
                });

            });

            $('.compare-formular').on('click', function (e) {
                let questionnaireId = $(this).attr('data-formular');

                $.ajax({
                    type: 'GET',
                    url: "{{ route('admin.client.questionnaire.compare') }}",
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        clientId: '{{ $clientID }}',
                        questionnaireId: questionnaireId,
                    },
                    success: function (data) {
                        if (data.success === true) {
                            $.each(data.data, function (index, value) {
                                $('tr[data-answer=\'' + index + '\'] .compare-answer').html(value);
                            });
                            $('.compare-answer-id').html('(#' + questionnaireId + ')');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: data.message,
                            });
                        }
                    },
                    error: function (data) {
                        let error = JSON.parse(data.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: error.message,
                        });
                    },
                });

            });

        });
    </script>
@endpush