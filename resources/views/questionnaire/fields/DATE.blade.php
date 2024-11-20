@php
    if ($question['slug'] === 'birthdate') {
        $now = Carbon\Carbon::now();
        $max = $now->format('d.m.Y');
        $min = $now->subYears(100)->format('d.m.Y');
    }

    $answer = is_string($question['answer']) ? parseDateString($question['answer'], 'd.m.Y') : old($question['slug']);
@endphp
<div class="form-group{{ $question['is_required'] ? ' required' : '' }}  @error($question['slug']) has-error @enderror"
     id="question_{{$question['slug']}}"
     data-key="{{($question['id'])}}">
    <label class="form-check-label" for="{{$question['slug']}}">
        {{$question['title']}}
    </label>

    <div class="input-group">
        <input class="form-control date"
               type="text"
               name="{{$question['slug']}}"
               id="{{$question['slug']}}"
               value="{{$answer}}"
               data-old-value="{{ Carbon\Carbon::parse($answer)->toDateString() }}"
               data-provide="datepicker"
               data-date-format="dd.mm.yyyy"
               data-date-autoclose="true"
               data-date-today-highlight="true"
               data-date-start-date="-100y"
               data-date-end-date="-16y"
               data-date-week-start="1"
               data-date-language="{{ app()->getLocale() }}">
        <span class="input-group-addon glyphicon glyphicon-calendar" aria-hidden="true"></span>
    </div>
</div>