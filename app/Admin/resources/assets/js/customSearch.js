import {searchRequest} from './common.js';

jQuery(document).ready(function ($) {

    /**
     *  Client Search
     */
    $('#client_search').on('click', function () {
        const url = '/admin/clients/search';

        searchRequest({
            search_name: $('input[name=search_name]').val(),
        }, url);
    });

    $('#clear_search').on('click', function () {
        clearSearch();
    });

    /** Trigger event when enter is pressed  */
    $('input[name=search_name]').keydown(function (event) {
        if (event.keyCode === 13) {
            $('#ingredient_tag_search').trigger('click');
            $('#recipe_search').trigger('click');
            $('#recipe_tag_search').trigger('click');
            return false;
        }
    });

    let clearSearch = function () {
        $('input[name=search_name]').val('');
        $('#ingredients,#variable_ingredients').val('');
        $('#seach_result').remove();
        $(this).hide();
        $('.card.card-default').show();
        $('#DataTables_Table_0_processing').hide();
    };

});