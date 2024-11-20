jQuery(document).ready(function ($) {

    $('#category_select').select2();

    $('#category_select').on('change', function () {
        let diets = $(this).find(':selected').attr('data-diets');

        if (diets === '') {
            diets = 'Any';
        }

        $('#diets_list').text(diets);
    });
});