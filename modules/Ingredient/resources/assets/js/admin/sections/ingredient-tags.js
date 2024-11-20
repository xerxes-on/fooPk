import {clearFilters, searchRequest, triggerEvent} from '../../../../../../../app/Admin/resources/assets/js/common.js';

/**
 * Blade file path - app/Admin/resources/ingredient/tags/searchResult.blade.php
 */
jQuery(document).ready(function ($) {
    let elements = {
        button: $('#ingredient_tag_search'),
        clearButton: $('#clear_search'),
        changeableElements: $('.changeable-element-ingredient'),
        filterUrl: '/admin/ingredient_tags/search',
    };

    let filters = {
        name: $('#ingredient_tag_search_input'),
    };

    let filterRequest = () => {
        searchRequest({
            search_name: filters.name.val(),
        }, elements.filterUrl, 'GET');
    };

    /**
     Ingredient tag Search
     */
    elements.button.on('click', filterRequest);
    elements.changeableElements.on('change', filterRequest);

    /**
     * Ingredient tags pagination
     */
    $(document).on('click', '.ingredient-tags-pagination .pagination a', function (event) {
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