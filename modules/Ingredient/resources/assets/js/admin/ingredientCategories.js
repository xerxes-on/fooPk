jQuery(document).ready(function ($) {

    $('#diets_select').select2();

    let mainCategory = $('#main_category');
    let midCategory = $('#mid_category');

    if (typeof mainCategory.val() === 'string' && mainCategory.val().length ===
        0) {
        $('#mid_category_wrapper').hide();
        $('#diets_select_wrapper').hide();
    }

    if (typeof midCategory.val() === 'string' && midCategory.val().length === 0) {
        $('#diets_select_wrapper').hide();
    }

    // main category select action
    mainCategory.on('change', function () {
        const id = $(this).find(':selected').val();

        $('#mid_category').remove();

        if (id !== '') {
            loadChildCategories(id, 'mid_category');
        } else {
            $('#mid_category_wrapper').hide();
            $('#diets_select_wrapper').hide();
            $('#diets_select').removeAttr('required');
            $('#diets_select').val(null).trigger('change');
        }
    });

    $('.diets-wrapper').on('change', '#mid_category', function () {
        const id = $(this).find(':selected').val();

        if (id !== '') {
            $('#diets_select_wrapper').show();
            $('#diets_select_wrapper').attr('hidden', false);
            $('#diets_select').attr('required', true);
            $('#diets_select').select2();
        } else {
            $('#diets_select_wrapper').hide();
            $('#diets_select_wrapper').attr('hidden', true);
            $('#diets_select').removeAttr('required');
            $('#diets_select').val(null).trigger('change');
        }
    });

    // load and create child category select
    loadChildCategories = (id, category_type) => {
        // /admin/ingredient_categories/create
        $.ajax({
            type: 'POST',
            url: $('input[name=child_category_url]').val(),
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                id: id,
            },
            success: function (resp) {
                if (resp.length) {

                    $options = resp.map(category => {
                        return `<option value="${category.id}">${category.name}</option>`;
                    });

                    let select = `
                    <select name="${category_type}" id="${category_type}" class="form-control">
                        <option value=""></option>
                        ${$options}
                    </select>`;

                    $(`#${category_type}_container`).append(select);
                    $(`#${category_type}_wrapper`).show();
                } else {
                    $(`#${category_type}_wrapper`).hide();
                }
            },
            error: function (err) {
                console.log(err);
            },
        });
    };
});