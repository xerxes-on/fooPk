<div class="alert bg-info">
    <label for="ingredient_tag_search_input" class="text-white">@lang('common.search')</label>
    <div class="form-group input-group">
        <input
                type="text"
                class="form-control mr-sm-2"
                name="search_name"
                id="recipe_tag_search_input"
                placeholder="@lang('admin.recipe_tag.type_title_of_recipe_tag')">
        <span class="input-group-btn">
			 <button class="btn btn-primary" id="recipe_tag_search" type="submit">{{trans('common.search')}}<i
                         style="display: none;margin-left: 5px;"
                         class="fa fa-spinner fa-spin search-request-spinner"></i></button>
            <button class="btn btn-danger" id="clear_search" type="button" style="display: none;"><i
                        class="fas fa-times"></i></button>
        </span>
    </div>
</div>

<div>
    <button class="btn btn-primary mb-3" type="button" data-toggle="collapse" data-target=".collapse"
            aria-expanded="false">Toggle all
    </button>
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


</script>