@php $steps = is_null($recipe) ? null : $recipe->steps()->get() @endphp
<div class="mb-3">
    <h4>@lang('common.steps')</h4>

    @error('steps.*')
    <ul class="form-element-errors">
        @php printf('<li>%s</li>', implode('</li><li>', Arr::flatten($errors->get('steps.*')))); @endphp
    </ul>
    @enderror

    <div id="recipe_steps_container">
        @if(!is_null($steps) && $steps->isNotEmpty())
            @foreach($steps as $key => $step)
                <div class="form-group form-element-text step-row" id="step_{{ $key + 1 }}">
                    <div class="d-flex align-center mb-2">
                        <label for="step_{{ $key + 1 }}_description" class="control-label mr-1">
                            @lang('common.step') {{ $key + 1 }}
                        </label>
                        <button type="button"
                                class="delete-step-anchor button-round btn-danger"
                                aria-label="Delete recipe step"
                                data-step="{{ $key + 1 }}">
                            <span class="fa fa-trash" aria-hidden="true"></span>
                        </button>
                    </div>
                    <textarea name="steps[{{ $key + 1 }}][description]"
                              id="step_{{ $key + 1 }}_description"
                              class="form-control step-description"
                              cols="30"
                              rows="5"
                    >{{ $step['description'] }}</textarea>
                    <input name="steps[{{ $key + 1 }}][id]" type="hidden" value="{{ $step['id'] }}" class="step-id">
                </div>
            @endforeach
        @endif
    </div>
    <button id="new_step_button" class="btn btn-primary">@lang('common.add_step')</button>
</div>
