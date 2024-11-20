<div class="alert bg-info">
    <label for="ingredient_tag_search_input" class="text-white">@lang('common.search')</label>
    <div class="form-group input-group">
        <input
                type="text"
                class="form-control mr-sm-2"
                name="search_name"
                id="ingredient_search_input"
                placeholder="@lang('admin.ingredients.label.type_title_of_ingredient')">
        <span class="input-group-btn">
			<button class="btn btn-primary" id="ingredient_search" type="button">{{trans('common.search')}}<i
                        style="display: none;margin-left: 5px;"
                        class="fa fa-spinner fa-spin search-request-spinner"></i></button>

            <button class="btn btn-danger" id="clear_search" type="button" style="display: none;"><i
                        class="fas fa-times"></i></button>
        </span>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <label for="ingredient_category" class="text-white">@lang('common.category')</label>
            <select name="ingredient_category" id="ingredient_category"
                    class="form-control changeable-element-ingredient">
                <option value=""></option>

                @foreach($ingredient_categories->where('parent_id', null) as $main_category)
                    <optgroup label="{{ $main_category->name }}">
                    @foreach($ingredient_categories->where('parent_id', $main_category->id) as $mid_category)
                        <optgroup label="—{{ $mid_category->name }}">
                            @foreach($ingredient_categories->where('parent_id', $mid_category->id) as $category)
                                @php
                                    $diets = '';

                                    foreach($category->diets as $diet) {
                                        $diets .= $diet->name.' ';
                                    }
                                @endphp
                                <option value="{{ $category->id }}"
                                        data-diets="{{$diets}}"
                                        {{ isset($ingredient) && $ingredient->category_id == $category->id ? 'selected' : '' }}
                                >
                                    &nbsp;&nbsp;&nbsp;&nbsp;{{ $category->name }}
                                </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                        </optgroup>
                    @endforeach
            </select>
        </div>

        <div class="col-sm-6">
            <label for="ingredient_tags" class="text-white">@lang('common.tags')</label>
            <input name='ingredient_tags' id="ingredient_tags" class='form-control changeable-element-ingredient'
                   placeholder='@lang('common.select_tag')' value=''/>
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

        //ingredients
        let recipeTagInput = document.getElementById('ingredient_tags');
        // init Tagify script on the above inputs
        let recipeTagtagify = new Tagify(recipeTagInput, {
            whitelist: @json($ingredient_tags->map(function($i){ return ['value' => $i->title, 'id' => $i->id ]; })),
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