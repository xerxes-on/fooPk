<div class="form-group form-element-text">
    @php
        $diets = '';
        if(isset($ingredient) && !is_null($ingredient?->category?->diets) && count($ingredient->category->diets)){
            foreach($ingredient->category->diets as $diet){
                $diets .= $diet->name.' ';
            }
        }else{
            $diets = 'Any';
        }
    @endphp
    <strong>@lang('common.diets'):</strong>
    <span id="diets_list">{{$diets}}</span>
</div>

<div class="form-group form-element-text">
    <label for="category_id" class="control-label">
        @lang('common.category')
        <span class="form-element-required">*</span>
    </label>
    <select name="category_id" id="category_select" class="form-control" required>
        <option value=""></option>

        @foreach($ingredient_categories->where('parent_id', null) as $main_category)
            <optgroup label="{{$main_category->name}}">
            @foreach($ingredient_categories->where('parent_id', $main_category->id) as $mid_category)
                <optgroup label="—{{$mid_category->name}}">
                    @foreach($ingredient_categories->where('parent_id', $mid_category->id) as $category)
                        @php
                            $diets = '';

                            foreach($category->diets as $diet){
                                $diets .= $diet->name.' ';
                            }
                            $isSelected = isset($ingredient) && $ingredient->category_id == $category->id;
                        @endphp
                        <option value="{{$category->id}}"
                                data-diets="{{$diets}}"
                                @selected($isSelected)>
                            &nbsp;&nbsp;&nbsp;&nbsp;{{$category->name}}
                        </option>
                    @endforeach
                </optgroup>
                @endforeach
                </optgroup>
            @endforeach
    </select>
</div>
