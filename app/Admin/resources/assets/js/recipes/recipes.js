import {clearFilters, searchRequest, triggerEvent} from '../common.js';

/**
 * Blade file path - app/Admin/resources/recipes/searchResult.blade.php
 */
jQuery(document).ready(function ($) {

    let elements = {
        button: $('#recipe_search'),
        clearButton: $('#clear_search'),
        changeableElements: $('.changeable-element-recipe'),
        filterUrl: '/admin/recipes/search',
    };

    const filters = {
        name: $('#recipe_search_input'),
        ingestion: $('#ingestion'),
        complexity: $('#complexity'),
        cost: $('#cost'),
        diet: $('#diet'),
        status: $('#status'),
        translations_done: $('#translations_done'),
        ingredients: $('#ingredients'),
        recipe_tags: $('#recipe_tags'),
        variable_ingredients: $('#variable_ingredients'),
    };

    let filterRequest = () => {
        searchRequest({
            search_name: filters.name.val(),
            ingestion: filters.ingestion.val(),
            complexity: filters.complexity.val(),
            cost: filters.cost.val(),
            diet: filters.diet.val(),
            status: filters.status.val(),
            translations_done: filters.translations_done.val(),
            ingredients: filters.ingredients.val() ?
                JSON.parse(filters.ingredients.val()).map(i => i.id) :
                null,
            recipe_tags: filters.recipe_tags.val() ?
                JSON.parse(filters.recipe_tags.val()).map(i => i.id) :
                null,
            variable_ingredients: filters.variable_ingredients.val() ?
                JSON.parse(filters.variable_ingredients.val()).map(i => i.id) :
                null,
        }, elements.filterUrl, 'GET');
    };

    /**
     * Recipes Search
     */
    elements.button.on('click', filterRequest);
    elements.changeableElements.on('change', filterRequest);

    /**
     * recipes pagination
     */
    $(document).on('click', '.recipes-pagination .pagination a', function (event) {
        event.preventDefault();
        $('li').removeClass('active');
        $(this).parent('li').addClass('active');
        let page = $(this).attr('href').split('page=')[1];

        searchRequest({
            search_name: filters.name.val(),
            ingestion: filters.ingestion.val(),
            complexity: filters.complexity.val(),
            cost: filters.cost.val(),
            diet: filters.diet.val(),
            status: filters.status.val(),
            translations_done: filters.translations_done.val(),
            ingredients: filters.ingredients.val() ?
                JSON.parse(filters.ingredients.val()).map(i => i.id) :
                null,
            recipe_tags: filters.recipe_tags.val() ?
                JSON.parse(filters.recipe_tags.val()).map(i => i.id) :
                null,
            variable_ingredients: filters.variable_ingredients.val() ?
                JSON.parse(filters.variable_ingredients.val()).map(i => i.id) :
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
