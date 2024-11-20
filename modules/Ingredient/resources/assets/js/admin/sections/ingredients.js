import {clearFilters, searchRequest, triggerEvent} from '../../../../../../../app/Admin/resources/assets/js/common.js';

/**
 * Blade file path - app/Admin/resources/ingredients/searchResult.blade.php
 */
jQuery(document).ready(function ($) {

    let elements = {
        button: $('#ingredient_search'),
        clearButton: $('#clear_search'),
        changeableElements: $('.changeable-element-ingredient'),
        filterUrl: '/admin/ingredients/search',
    };

    let filters = {
        name: $('#ingredient_search_input'),
        category: $('#ingredient_category'),
        tags: $('#ingredient_tags'),
    };

    let filterRequest = () => {
        searchRequest({
            search_name: filters.name.val(),
            category_id: filters.category.val(),
            tags: filters.tags.val() ?
                JSON.parse(filters.tags.val()).map(i => i.id) :
                null,
        }, elements.filterUrl, 'GET');
    };

    /**
     Ingredient Search
     */
    elements.button.on('click', filterRequest);
    elements.changeableElements.on('change', filterRequest);

    /**
     * Ingredients pagination
     */
    $(document).on('click', '.ingredients-pagination .pagination a', function (event) {
        event.preventDefault();
        $('li').removeClass('active');
        $(this).parent('li').addClass('active');
        let page = $(this).attr('href').split('page=')[1];

        searchRequest({
            search_name: filters.name.val(),
            category_id: filters.category.val(),
            tags: filters.tags.val() ?
                JSON.parse(filters.tags.val()).map(i => i.id) :
                null,
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