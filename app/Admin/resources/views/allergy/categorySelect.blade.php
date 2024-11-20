<div class="form-group">
    <label for="category_id" class="control-label">
        {{trans('common.excluded_category')}}
    </label>

    <select name="categories[]" multiple="multiple" id="category_select" class="form-control">
        <option value=""></option>

        @foreach($ingredient_categories->where('parent_id', null) as $main_category)
            <optgroup label="{{$main_category->name}}">
            @foreach($ingredient_categories->where('parent_id', $main_category->id) as $mid_category)
                <optgroup label="—{{$mid_category->name}}">
                    @foreach($ingredient_categories->where('parent_id', $mid_category->id) as $category)
                        <option value="{{$category->id}}"
                                {{!is_null($allergy) && $allergy->ingredientCategories->find($category->id) ? 'selected' : ''}}
                        >
                            &nbsp;&nbsp;&nbsp;&nbsp;{{$category->name}}
                        </option>
                    @endforeach
                </optgroup>
                @endforeach
                </optgroup>
            @endforeach
    </select>
</div>