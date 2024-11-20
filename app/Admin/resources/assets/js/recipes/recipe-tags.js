import {clearFilters, searchRequest, triggerEvent} from '../common.js';

/**
 * Blade file path - app/Admin/resources/ingredient/recipes/searchResult.blade.php
 */
jQuery(document).ready(function ($) {
    let elements = {
        button: $('#recipe_tag_search'),
        clearButton: $('#clear_search'),
        filterUrl: '/admin/recipe_tags/search',
    };

    let filters = {
        name: $('#recipe_tag_search_input'),
    };

    let filterRequest = () => {
        searchRequest({
            search_name: filters.name.val(),
        }, elements.filterUrl, 'GET');
    };

    /**
     * Recipe tag search
     */
    elements.button.on('click', filterRequest);

    /**
     * Recipe tags pagination
     */
    $(document).on('click', '.recipe-tags-pagination .pagination a', function (event) {
        event.preventDefault();
        $('li').removeClass('active');
        $(this).parent('li').addClass('active');
        let page = $(this).attr('href').split('page=')[1];

        searchRequest({
            search_name: filters.name.val(),
            page: page,
        }, elements.filterUrl, 'GET');
    });

    /**
     *  Trigger event when enter is pressed
     */
    triggerEvent(filters.name, 13, elements.button);

    /**
     * Clear filters when enter is pressed
     */
    elements.clearButton.on('click', function () {
        clearFilters(filters);
    });

});