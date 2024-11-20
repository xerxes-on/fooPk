<div class="diets-wrapper">
    <input type="hidden" name="child_category_url"
           value="{{ route('admin.ingredientCategories.getChild') }}">
    <input type="hidden" name="id"
           value="{{ !is_null($category) ? $category->id : null }}">
    <input type="hidden" name="jobExists"
           value="{{ $calculationsJobExists }}">


    <div class="form-group form-element-text">
        <label for="main_category">@lang('common.main_category')</label>
        <select name="main_category" id="main_category"
                class="form-control" @disabled($calculationsJobExists)>
            <option value=""></option>
            @foreach($mainCategory as $list_category)
                <option
                        value="{{ $list_category->id }}" {{ isset($category) && $category->tree_information['main_category'] == $list_category->id ? 'selected' : '' }}>
                    {{ $list_category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div id="mid_category_wrapper">
        <label for="mid_category">@lang('common.mid_category')</label>
        <div id="mid_category_container" class="form-group form-element-text">
            <select name="mid_category" id="mid_category"
                    class="form-control" @disabled($calculationsJobExists)>
                <option value=""></option>
                @if ($midCategories)
                    @foreach($midCategories as $list_category)
                        <option
                                value="{{ $list_category->id }}" {{ (isset($category) && $category->tree_information['mid_category'] == $list_category->id) ? 'selected' : '' }}>
                            {{ $list_category->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    <div class="form-group" id="diets_select_wrapper">
        <label for="diets_select">@lang('common.diets')</label>
        <select class="form-control" name="diets[]" multiple="multiple"
                @disabled($calculationsJobExists)
                id="diets_select">
            @foreach($diets as $diet)
                <option value="{{ $diet->id }}" {{ ($categoryDiets && in_array($diet->id, $categoryDiets)) ? 'selected' : '' }}>
                    {{ $diet->name }}
                </option>
            @endforeach
        </select>
    </div>

</div>
