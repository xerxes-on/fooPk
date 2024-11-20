<div id="questionnaire" class="tab-pane fade in">
    @if ($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(config('formular.ability_forced_formular_editing_by_client_enabled'))
        <p>
            @if(auth()->user()->canEditQuestionnaire())
                <a href="{{route('questionnaire.edit')}}"
                   class="btn btn-primary js-process-questionnaire-edit"
                   id="processEdit">@lang('common.edit')</a>
            @else
                <a href="{{route('questionnaire.edit.buy')}}"
                   class="btn btn-primary js-process-questionnaire-edit js-process-questionnaire-buy-edit"
                   id="buyEdit">@lang('questionnaire.buttons.edit_for_fp')</a>
            @endif
        </p>
    @endif

    @if($latestQuestionnaire?->answers)
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>@lang('common.question')</th>
                <th>@lang('common.answer')</th>
            </tr>
            </thead>
            <tbody>

            @foreach($baseQuestions as $question)
                <tr data-answer="{{ $question->slug }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>@lang("questionnaire.questions.$question->slug.title")</td>
                    <td>{{$latestAnswers[$question->slug]  ?? ''}}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
    @endif
</div>

@section('scripts_after')
    <script>
        $(document).on('click', '.js-process-questionnaire-edit', function (e) {
            const foodpoints = {{ auth()->user()->balance }};
            const editingPrice = {{ config('formular.formular_editing_price_foodpoints') }};

            const $this = $(this);
            if (!$this.hasClass('js-process-questionnaire-buy-edit')) {
                return;
            }

            if (foodpoints < editingPrice) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    title: "{{trans('questionnaire.info.insufficient_fund', ['amount' => config('questionnaire.formular_editing_price_foodpoints')])}}",
                    icon: 'error',
                    showCancelButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    confirmButtonColor: '#3097d1',
                    cancelButtonColor: '#e6007e',
                    confirmButtonText: "@lang('common.buy_foodpoints')",
                    cancelButtonText: "@lang('common.close')",
                }).then((result) => {
                    if (result.value) {
                        window.location.href = "{{ route('pages.wallet') }}";
                    }
                });

                return;
            }

            Swal.fire({
                title: "@lang('questionnaire.info.update_confirmation')",
                icon: 'question',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                confirmButtonColor: '#3097d1',
                cancelButtonColor: '#e6007e',
                html: `<div id="editFormularSwal"></div>`,
                confirmButtonText: "@lang('common.yes')",
                cancelButtonText: "@lang('common.no')",
                willOpen: () => {
                    e.preventDefault();
                    Swal.showLoading();
                    $.get(
                        "{{route('questionnaire.checkEditPeriod')}}",
                        (payload) => {
                            Swal.hideLoading();
                            Swal.getHtmlContainer().querySelector('#editFormularSwal').innerHTML = payload;
                        });
                },
            }).then((result) => {
                if (result.value) {
                    console.log($this.attr('href'));
                    window.location.href = $this.attr('href');
                }
            });
        });
    </script>
@endsection