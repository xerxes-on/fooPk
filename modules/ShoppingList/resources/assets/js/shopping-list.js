(function ($) {

    'use strict';

    $(document).ready(function () {
        $('.date').datepicker().on('change', function (e) {
            $('#createByDate').valid();
        });

        $.validator.addMethod('dateMask', function (value, element) {
            //let re = /^([0]?[1-9]|[1|2][0-9]|[3][0|1])[./-]([0]?[1-9]|[1][0-2])[./-]([0-9]{4}|[0-9]{2})$/;
            let re = /^([0]?[1-9]|[1|2][0-9]|[3][0|1])[.]([0]?[1-9]|[1][0-2])[.]([0-9]{4}|[0-9]{2})$/;
            return this.optional(element) || re.test(value);
        }, window.foodPunk.i18n.dateFormat);

        $('#createByDate').validate({
            rules: {
                date_start: {
                    dateMask: true,
                    required: true,
                },
                date_end: {
                    dateMask: true,
                    required: true,
                },
            },
            errorElement: 'em',
            errorPlacement: function (error, element) {
                // Add the `help-block` class to the error element
                error.addClass('help-block').css({
                    'font-size': '12px',
                    'color': '#a94442',
                });
                error.insertAfter($(element).parents('.input-group.date'));
            },
            highlight: function (element, errorClass, validClass) {
                $(element).parents('.input-group.date').addClass('has-error').removeClass('has-success');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).parents('.input-group.date').addClass('has-success').removeClass('has-error');
            },
            submitHandler: function (form) {
                Swal.fire({
                    title: window.foodPunk.i18n.generateListTitle,
                    text: window.foodPunk.i18n.generateListText,
                    icon: 'question',
                    showCancelButton: true,
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                    allowEnterKey: true,
                    confirmButtonColor: '#3097d1',
                    cancelButtonColor: '#e6007e',
                    confirmButtonText: window.foodPunk.i18n.generate,
                    cancelButtonText: window.foodPunk.i18n.cancel,
                }).then((result) => {
                    if (result.value) {
                        form.submit();
                    }
                });
            },
        });

        // add custom message
        $.validator.messages.required = window.foodPunk.i18n.requiredField;

        $('#clear_list').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            Swal.fire({
                title: window.foodPunk.i18n.clearListQuestion,
                icon: 'question',
                showCancelButton: true,
                allowOutsideClick: true,
                allowEscapeKey: true,
                allowEnterKey: true,
                confirmButtonColor: '#3097d1',
                cancelButtonColor: '#e6007e',
                confirmButtonText: window.foodPunk.i18n.confirm,
                cancelButtonText: window.foodPunk.i18n.cancel,
            }).then((result) => {
                if (result.value) {
                    location.href = window.foodPunk.routes.clearList;
                }
            });
        });

        // purchase list item check
        $('.ingredient-list-element').on('click', function () {
            // user/purchases/ingredient/check
            $.ajax({
                type: 'POST',
                url: window.foodPunk.routes.checkIngredient,
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    ingredient_id: $(this).attr('data-item-id'),
                    completed: $(this).is(':checked') === true ? 1 : 0, // To pass validation on boolean
                },
                success: function (resp) {
                    if (!resp.success) {
                        console.error(resp.message);
                    }
                },
                error: function (err) {
                    console.error(err);
                },
            });

        });

        // delete recipe from purchase list
        $('.recipe-delete-anchor').on('click', function () {
            const target = $(this).attr('data-target');

            // user/purchases/list
            Swal.fire({
                title: window.foodPunk.i18n.areYouSure,
                icon: 'question',
                showCancelButton: true,
                allowOutsideClick: true,
                allowEscapeKey: true,
                allowEnterKey: true,
                confirmButtonColor: '#3097d1',
                cancelButtonColor: '#e6007e',
                confirmButtonText: window.foodPunk.i18n.confirm,
                cancelButtonText: window.foodPunk.i18n.cancel,
            }).then((result) => {
                if (!result.value) {
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: window.foodPunk.routes.deleteRecipe,
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        list_id: $(this).attr('data-list-id'),
                        recipe_id: $(this).attr('data-recipe-id'),
                        recipe_type: $(this).attr('data-recipe-type'),
                        mealtime: $(this).attr('data-meal-time'),
                        meal_day: $(this).attr('data-meal-day'),
                    },
                    success: function (resp) {
                        if (resp.success === true) {
                            $(target).remove();
                            $('#ingredients_wrapper').empty().append(resp.data);
                        }
                        Swal.fire({
                            title: resp.message,
                            timer: 1000,
                            timerProgressBar: true,
                        });
                    },
                    error: function (err) {
                        console.log(err);
                    },
                });

            });
        });

        // Change Recipe servings
        $('.recipe-serving-anchor').on('change', function () {
            $.ajax({
                type: 'POST',
                url: window.foodPunk.routes.changeServing,
                // user/purchases/list
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    recipe_id: $(this).attr('data-recipe-id'),
                    servings: $(this).find(':selected').val(),
                    mealtime: $(this).attr('data-meal-time'),
                    recipe_type: $(this).attr('data-recipe-type'),
                    meal_day: $(this).attr('data-meal-day'),
                },
                success: function (resp) {
                    if (!resp) {
                        return;
                    }
                    if (resp.data) {
                        $('#ingredients_wrapper').empty().append(resp.data);
                    }

                    Swal.fire({
                        title: resp.message,
                        timer: 1000,
                        timerProgressBar: true,
                    });
                },
                error: function (err) {
                    console.error(err);
                },
            });
        });

        // add custom ingredient button handler
        $('#new_list_ingredient').on('click', function () {
            const description = $('#custom_ingredient').val();

            if (description === '') {
                Swal.fire({
                    title: window.foodPunk.i18n.emptyField,
                    icon: 'error',
                });
                return false;
            }

            // /user/purchases/list
            $.ajax({
                type: 'POST',
                url: window.foodPunk.routes.addIngredient,
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    custom_ingredient: description,
                },
                success: function (resp) {
                    // create block with custom ingredients if one doesn't exist
                    let custom = $(`#custom`);
                    if (!custom.length) {
                        $('#empty_ingredients_list').remove();
                        $('#ingredient_container').append(`
                            <div class="card" id="custom">
                                <h4 class="shopping-list_ingredients_title"><b>${window.foodPunk.i18n.customIngredientLabel}</b></h4>
                                <ul class="shopping-list_ingredients_list"></ul>
                            </div>`);
                        custom = $(`#custom`);
                    }

                    custom.find('ul.shopping-list_ingredients_list').append(`
                            <li class="shopping-list_ingredients_list_item" id="ingredient_${resp.data.id}">
                                <input
                                    id="${resp.data.id}"
                                    type="checkbox"
                                    class="form-check ingredient-list-element shopping-list_ingredients_list_item_check"
                                    data-item-id="${resp.data.id}" />

                                <label for="${resp.data.id}" class="shopping-list_ingredients_list_item_label">
                                    <div class="cross-line"></div>
                                    ${description}
                                </label>

                            <button type="button"
                                    class="shopping-list_recipes_item_right_label_rounded ingredient-delete-anchor btn-with-icon btn-with-icon-delete"
                                    data-id="${resp.data.id}"
                                    data-category-id="custom"
                                    onclick="window.foodPunk.deleteIngredient(this)"
                                    title="Delete ingredient"
                                    aria-label="Delete ingredient"></button>
                            </li>`);

                    $('#custom_ingredient').val('');
                    Swal.fire({
                        title: resp.message,
                        timer: 1000,
                        timerProgressBar: true,
                    });
                },
                error: function (err) {
                    if (err.responseJSON.message) {
                        Swal.fire({
                            title: err.responseJSON.message,
                            icon: 'error',
                            timer: 1500,
                            timerProgressBar: true,
                        });
                        return;
                    }

                    console.log(err);
                },
            });

        });

        $('.shopping-list_panel_btn').click(function () {
            $(this).parent().toggleClass('active');
            $(this).parent().find($('.shopping-list_panel_content')).slideToggle();
        });

    });

})(jQuery);

window.foodPunk.deleteIngredient = function (e) {
    const id = $(e).attr('data-id');
    const category_id = $(e).attr('data-category-id');

    // user/purchases/ingredient/delete
    Swal.fire({
        title: window.foodPunk.i18n.areYouSure,
        icon: 'question',
        showCancelButton: true,
        allowOutsideClick: true,
        backdrop: true,
        allowEscapeKey: true,
        allowEnterKey: true,
        confirmButtonColor: '#3097d1',
        cancelButtonColor: '#e6007e',
        confirmButtonText: window.foodPunk.i18n.confirm,
        cancelButtonText: window.foodPunk.i18n.cancel,
    }).then((result) => {
        if (!result.value) {
            return;
        }
        $.ajax({
            type: 'POST',
            url: window.foodPunk.routes.deleteIngredient,
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                ingredient_id: id,
            },
            success: function (resp) {
                if (resp.success === false) {
                    return;
                }

                $(`#ingredient_${id}`).remove();

                if (resp.data && resp.data.length > 0) {
                    resp.data.forEach(pivotId => {
                        const recipeElement = $(`#recipe_${pivotId}`);
                        const recipeGroup = recipeElement.closest('.shopping-list_recipes-group');

                        recipeElement.remove();

                        // If group is empty after removing recipe, removing thr group
                        if (recipeGroup.find('.shopping-list_recipes_item').length === 0) {
                            recipeGroup.remove();
                        }
                    });
                }
                let item = $(`#${category_id}`);
                if (!item.find('li').length) {
                    item.remove();
                }

                // Custom category parent cleanup
                let customItem = $('#custom');
                if (!customItem.find('li').length) {
                    customItem.remove();
                }
            },
            error: function (err) {
                console.log(err);
            },
        });
    });
};
