@php
    use App\Enums\Recipe\RecipeStatusEnum;
        if(is_null($recipe)) {
            return;
        }
@endphp

<div class="form-row">
    <div class="form-group col-md-2 mb-3">
        <label class="font-weight-bold" for="status">@lang('common.status')</label>
        <select name="status" id="status" class="form-control">
            @foreach(RecipeStatusEnum::forSelect() as $key => $value)
                <option value="{{$key}}" @if($recipe->status->value === $key) selected @endif>
                    @lang('common.'.strtolower($value))
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-2 d-flex align-items-end">
        <div class="form-check form-check-inline">
            <input class="form-check-input"
                   type="checkbox"
                   id="translations_done"
                   name="translations_done"
                   value="1"
                    @checked($recipe->translations_done)/>
            <label class="form-check-label font-weight-bold"
                   for="translations_done">@lang('admin.recipes.translations_done')</label>
        </div>
    </div>
</div>