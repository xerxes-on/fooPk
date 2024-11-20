@php
    use App\Enums\Recipe\RecipeStatusEnum;
@endphp
<div class="alert bg-info">
    <label for="ingredient_tag_search_input" class="text-white">@lang('common.search')</label>
    <div class="form-group input-group">
        <input type="hidden" name="search_url" value="{{route('admin.recipe.search')}}">
        <input
                type="text"
                class="form-control mr-sm-2"
                name="search_name"
                id="recipe_search_input"
                placeholder="@lang('admin.recipes.type_title_of_recipe')"
        >
        <span class="input-group-btn">
            <button class="btn btn-primary" id="recipe_search" type="submit">
				@lang('common.search')
				<i style="display: none;margin-left: 5px;" class="fa fa-spinner fa-spin search-request-spinner"></i>
			</button>
            <button class="btn btn-danger" id="clear_search" type="button" style="display: none;" title="Clear search">
				<i class="fas fa-times" aria-hidden="true"></i>
			</button>
        </span>
    </div>
    <div class="row my-md-n20">

        <div class="col-sm-2">
            <label for="status" class="text-white">@lang('common.status')</label>
            <select name="status" id="status" class="form-control changeable-element-recipe">
                <option value="" selected>@lang('common.all')</option>
                @foreach(RecipeStatusEnum::forSelect() as $key => $value)
                    <option value="{{$key}}">
                        @lang('common.'.strtolower($value))
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-2">
            <label for="translations_done" class="text-white">@lang('admin.recipes.translations_done')</label>
            <select name="translations_done" id="translations_done" class="form-control changeable-element-recipe">
                <option value="" selected>@lang('common.all')</option>
                <option value="0">@lang('common.no')</option>
                <option value="1">@lang('common.yes')</option>
            </select>
        </div>

        <div class="col-sm-2">
            <label for="ingestion" class="text-white">@lang('common.meal')</label>
            <select name="ingestion" id="ingestion" class="form-control changeable-element-recipe">
                <option value=""></option>
                @foreach($ingestions as $ingestion)
                    <option value="{{$ingestion->id}}"
                            @selected(isset($url_addons['ingestion']) && $url_addons['ingestion'] == $ingestion->id)
                    >
                        {{$ingestion->title}}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-2">
            <label for="complexity" class="text-white">@lang('common.complexity')</label>
            <select name="complexity" id="complexity" class="form-control changeable-element-recipe">
                <option value=""></option>
                @foreach($complexities as $complexity)
                    <option value="{{$complexity->id}}"
                            @selected(isset($url_addons['complexity']) && $url_addons['complexity'] == $complexity->id)
                    >
                        {{$complexity->title}}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-2">
            <label for="cost" class="text-white">@lang('common.cost')</label>
            <select name="cost" id="cost" class="form-control changeable-element-recipe">
                <option value=""></option>
                @foreach($costs as $cost)
                    <option value="{{$cost->id}}"
                            @selected(isset($url_addons['cost']) && $url_addons['cost'] == $cost->id)
                    >
                        {{$cost->title}}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-2">
            <label for="diet" class="text-white">@lang('common.diets')</label>
            <select name="diet" id="diet" class="form-control changeable-element-recipe">
                <option value=""></option>
                @foreach($diets as $diet)
                    <option value="{{$diet->id}}"
                            @selected(isset($url_addons['diet']) && $url_addons['diet'] == $diet->id)
                    >
                        {{$diet->name}}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-4 my-3">
            <label for="ingredients" class="text-white">@lang('common.ingredients')</label>
            <input name='ingredients'
                   id="ingredients"
                   class='form-control changeable-element-recipe'
                   placeholder="@lang('common.select_ingredients')"
                   value=""/>
        </div>

        <div class="col-sm-4 my-3">
            <label for="variable_ingredients" class="text-white">@lang('common.variable_ingredients')</label>
            <input name="variable_ingredients"
                   id="variable_ingredients"
                   class="form-control changeable-element-recipe"
                   placeholder="@lang('common.select_ingredients')"
                   value=""/>
        </div>

        <div class="col-sm-4 my-3">
            <label for="recipe_tags" class="text-white">@lang('common.tags')</label>
            <input name="tags"
                   id="recipe_tags"
                   class="form-control changeable-element-recipe"
                   placeholder="@lang('common.select_tag')"
                   value=""/>
        </div>
    </div>
</div>

<div id="search_result_wrapper"></div>
<script>

    function lvDelayCallback(callback, ms) {
        var timer = 0;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    function lvCheckIfElementExistsByClass(selector) {
        var result = false;
        if (document.querySelector(selector) !== null) {
            result = true;
        }
        return result;
    }

    function lvCheckIfElementHasChilds(selector) {
        return document.querySelector(selector).hasChildNodes();
    }

    function setTablePage(page) {
        var table = $.fn.dataTable.Api('#DataTables_Table_0');
        table.page(page).draw('page');
    }

    function lvCheckAndAddPageSelector() {
        if (lvCheckIfElementExistsByClass('.dataTables_paginate') == true && lvCheckIfElementHasChilds('.dataTables_paginate')) {
            var existsPaginationBlock = true;
        }
        var existsPaginationPageSelector = lvCheckIfElementExistsByClass('.pagination_page_selector');

        if (existsPaginationBlock == true && existsPaginationPageSelector == false) {

            var new_html = '<img src="@php echo e(asset("/images/icons/go-to.png")); @endphp" width="32"/><input type="text" class="pagination_page_selector paginate_input" name="pagination_page_selector" id="pagination_page_selector" style="padding:6px;font-size:14px; width:50px;"/>';
            var new_elem = document.createElement('li');
            new_elem.classList.add('paginate_button');
            new_elem.innerHTML = new_html;
            document.querySelector('.pagination').appendChild(new_elem);

            if (!setupedKeyUpEvent) {
                $(document).on('keyup', '.pagination_page_selector', lvDelayCallback(function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    setTablePage(parseInt($(this).val()) - 1);
                }, 500));
                setupedKeyUpEvent = true;
            }
        }
    }

    var setupedKeyUpEvent = false;
    var paginationTimer;
    document.addEventListener('DOMContentLoaded', function (event) {
        paginationTimer = setInterval(lvCheckAndAddPageSelector, 500);
    });

    document.addEventListener('DOMContentLoaded', function (event) {
        // ingredients
        let ingredientsInput = document.getElementById('ingredients');
        // init Tagify script on the above inputs
        let ingredientsTagify = new Tagify(ingredientsInput, {
            whitelist: @json($ingredients->map(function($i){ return ['value' => $i->name, 'id' => $i->id ]; })),
            delimiters: 'false',
            maxTags: 10,
            dropdown: {
                maxItems: 20,           // <- max allowed rendered suggestions
                classname: 'tags-look', // <- custom classname for this dropdown, so it could be targeted
                enabled: 0,             // <- show suggestions on focus
                closeOnSelect: false,   // <- do not hide the suggestions dropdown once an item has been selected
            },
        });

        // variable ingredients
        let variableIngredientsInput = document.getElementById('variable_ingredients');
        // init Tagify script on the above inputs
        let variableIngredientsTagify = new Tagify(variableIngredientsInput, {
            whitelist: @json($ingredients->map(function($i){ return ['value' => $i->name, 'id' => $i->id ]; })),
            delimiters: 'false',
            maxTags: 10,
            dropdown: {
                maxItems: 20,           // <- max allowed rendered suggestions
                classname: 'tags-look', // <- custom classname for this dropdown, so it could be targeted
                enabled: 0,             // <- show suggestions on focus
                closeOnSelect: false,   // <- do not hide the suggestions dropdown once an item has been selected
            },
        });

        // recipe tags
        let recipeTagInput = document.getElementById('recipe_tags');
        // init Tagify script on the above inputs
        let recipeTagTagify = new Tagify(recipeTagInput, {
            whitelist: @json($tags->map(function($i){ return ['value' => $i->title, 'id' => $i->id ]; })),
            delimiters: 'false',
            maxTags: 10,
            dropdown: {
                maxItems: 20,           // <- max allowed rendered suggestions
                classname: 'tags-look', // <- custom classname for this dropdown, so it could be targeted
                enabled: 0,             // <- show suggestions on focus
                closeOnSelect: false,   // <- do not hide the suggestions dropdown once an item has been selected
            },
        });
    });
</script>
