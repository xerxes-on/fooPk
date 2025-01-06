{{--<div class="form-inline">

    <div class="form-group mb-2">
        --}}{{--		<a href="{{ route('admin.clients.formular.edit', ['id' => $client->id]) }}" class="btn btn-primary">--}}{{--
        --}}{{--			@if($formularExist)--}}{{--
        --}}{{--				@lang('common.edit')--}}{{--
        --}}{{--			@else--}}{{--
        --}}{{--				@lang('common.create')--}}{{--
        --}}{{--			@endif--}}{{--
        --}}{{--		</a>--}}{{--
    </div>
    @if($formularExist)
        <div class="form-group mx-sm-3 mb-2" style="margin-left: 10px">
            <input readonly
                   disabled
                   type="checkbox"
                   id="old_approve_formular" {{ $client->formular->approved ? 'checked' : '' }}>
            <label for="old_approve_formular">@lang('common.approve_formular')</label>
        </div>
    @endif

    @if($formularExist)
        <div class="form-group mx-sm-3 mb-2" style="margin-left: 10px">
            <input readonly
                   disabled
                   type="checkbox"
                   class="form-check-input"
                   id="old_toggle_formular" {{$client->formular->forced_visibility ? 'checked' : '' }}>
            <label for="old_toggle_formular" class="form-check-label">@lang('common.force_visibility_for_user')</label>
        </div>
    @endif
</div>--}}

@if($formularExist && !empty($formular))
    <table class="table">
        <thead>
        <tr>
            <th style="width: 3%">#</th>
            <th style="width: 48%">@lang('common.question')</th>
            <th @if($formularCount >= 2) style="width: 25%" @endif>
                {{ trans('common.answer') .' (#'. $client->formular->id .' - '. \Carbon\Carbon::parse($client->formular->created_at)->format('d.m.Y') .')' }}
            </th>
            {{--            <th class="compare-answer-title @if($formularCount < 2) hidden @endif" style="width: 25%">--}}
            {{--                @lang('common.compare_answer')--}}
            {{--            </th>--}}
        </tr>
        </thead>

        <tbody>
        @foreach($questions as $question)
            <tr data-answer="{{ $question->key_code }}">
                <td>{{ $question->id }}</td>
                <td>@lang('survey_questions.'. $question->key_code)</td>
                <td>
                    @if(key_exists($question->id, $formular))
                        {{ implode(', ', $formular[$question->id]['answer']) }}
                    @endif
                </td>
                <td class="hidden"></td>
            </tr>
        @endforeach
        </tbody>

    </table>
@endif

<h3 class="text-center">@lang('common.formular_history')</h3>
@if($formularExist && $formularCount > 0)
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>@lang('common.name')</th>
            <th>@lang('common.data')</th>
            <th>@lang('common.creator')</th>
            <th>@lang('common.creation_method')</th>
            <th>@lang('common.action')</th>
        </tr>
        </thead>

        <tbody>
        @php $index = 1; @endphp
        @foreach($client->formulars()->get() as $formular)
            <tr @if($formular->id == $client->formular->id) style="background-color: aliceblue;" @endif>
                <td>{{ $index++ }}</td>
                <td>{{ 'Formular (#'. $formular->id .')' }}</td>
                <td>{{ parseDateString($formular->created_at,'d.m.Y') }}</td>
                <td>
                    @php $creator = $formular->getCreator(); @endphp
                    <span>{{ $creator === null ? trans('common.user') : $creator->name .' ('. $creator->email .')' }}</span>
                </td>
                <td>
                    <span>{{ $formular->creationMethodName }}</span>
                <td>
                    @if($formular->id != $client->formular->id)
                        Compare not available
                    @else
                        <span>@lang('common.current')</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>
@else
    <div>@lang('common.formular_history_empty')</div>
@endif
