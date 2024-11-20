jQuery(document).ready(function ($) {

    window.setActiveTab = function () {

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('activeTab', $(e.target).attr('href'));
        });

        // Here, save the index to which the tab corresponds. You can see it
        // in the chrome dev tool.
        let activeTab = localStorage.getItem('activeTab');

        if (activeTab) {
            $('a[href="' + activeTab + '"]').tab('show');
        }
    };

    window.recalculateAllUsersRecipe = function (recipeId) {

        Swal.fire({
            title: 'Are you sure?',
            text: 'You will not be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'yes',
            cancelButtonText: 'no',

        }).then(function (result) {
            if (result.value) {

                Swal.fire({
                    title: 'Please wait',
                    text: 'It is working..',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    didOpen: () => {
                        Swal.hideLoading();
                    },
                });

                $.ajax({
                    type: 'POST',
                    url: '/admin/recipes/recalculate-for-all-users',
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        recipeId: recipeId,
                    },
                    success: function (data) {
                        if (data.success === true) {
                            Swal.hideLoading();
                            Swal.fire({
                                icon: 'success',
                                title: 'Recalculated!',
                                text: 'All recipes have been recalculated.',
                                html: data.message,
                            });
                        } else {
                            Swal.hideLoading();
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: data.message,
                            });
                        }
                    },
                });
            }
        });
    };

    function getSelect2Options() {
        return {
            templateSelection: function (selection, container) {
                if (selection.data) {
                    $.each(selection.data, function (key, value) {
                        selection.element.dataset[key] = value;
                    });
                }

                return selection.text;
            },
        };
    }

    $('.autocomplete-select').select2(getSelect2Options());

    function calculateRecipeDiet() {

        let ingredients_id = [];

        $('.choosen-ingredient-anchor').each(function () {
            if ($(this).val() !== '0') {
                ingredients_id.push($(this).val());
            }
        });

        if (!ingredients_id.length) {
            return true;
        }

        // /admin/recipes/create
        $.ajax({
            type: 'POST',
            url: '/admin/recipes/get-diets',
            dataType: 'json',
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                ingredients_id: ingredients_id,
            },
            success: function (resp) {
                const element = $('#recipe-diets');
                if (resp.length) {
                    let diets = '';

                    resp.forEach(function (diet) {
                        diets += `${diet['name']}; `;
                    });

                    element.text(diets);
                    return;
                }
                element.text('No diets');
            },
            error: function (err) {
                console.log(err);
            },
        });
    }

    /**
     * Ingredients
     */

    // add ingredient handler
    $('#new_ingredient_btn').on('click', function (e) {
        e.preventDefault();

        $('#ingredient_tbody').append(generateIngredient().show());
    });

    // delete ingredient handler
    $('#ingredient_tbody').on('click', '.delete-ingredient-anchor', function () {
        if (confirm('Delete ingredient?')) {
            deleteIngredient($(this).attr('data-id'));
        }
    });

    // choose ingredient handler
    $('#ingredient_tbody').on('change', '.ingredient-picker-anchor', function () {
        // get row id
        const id = $(this).attr('data-row-id');

        calculateRowValues(id, true);
        calculateRecipeDiet();
    });

    $('#ingredient_tbody').on('input', '.amount-anchor', function () {
        // get row id
        const id = $(this).attr('data-row-id');

        calculateRowValues(id);
    });

    function calculateRowValues(id, setDefault = false) {
        // get row
        let row = $(`#ingredient_${id}_row`);
        // get selected option
        const selected = $(`#ingredient_${id}_select`).find(':selected');
        // get unit const need for calculating values per unit
        const unitConst = selected.attr('data-unit-default-amount');
        // if choose new ingredient - set default unit value
        if (setDefault) {
            row.find('.amount-anchor').val(unitConst);
        }
        // get current item amount
        const amount = row.find('.amount-anchor').val();

        let diets = '<div class="d-flex">';
        selected.attr('data-diets').split('|').forEach((diet) => {
            diets += `<span class="label label-info mr-1">${diet}</span>`;
        });

        if (selected.attr('data-diets') === '') {
            diets += '—';
        }

        diets += '</div>';

        if (unitConst == 0) {
            row.find('.diets').empty().append('—');
            row.find('.proteins').text(0);
            row.find('.fats').text(0);
            row.find('.carbohydrates').text(0);
            row.find('.calories').text(0);
            row.find('.units').text(selected.attr('data-unit'));
        } else {
            // change row values
            row.find('.diets').empty().append(diets);
            row.find('.proteins').text(calculateValue(selected.attr('data-proteins'), amount,
                unitConst));
            row.find('.fats').text(calculateValue(selected.attr('data-fats'), amount, unitConst));
            row.find('.carbohydrates').text(calculateValue(selected.attr('data-carbohydrates'), amount,
                unitConst));
            row.find('.calories').text(calculateValue(selected.attr('data-calories'), amount,
                unitConst));
            row.find('.units').text(selected.attr('data-unit'));

            calculateIngredientsTotal();
        }

    }

    function calculateValue(value, amount, unitConst, decimals) {
        if (typeof decimals == 'undefined') decimals = 2;
        return (parseFloat(value) / parseFloat(unitConst) *
            parseFloat(amount)).toFixed(decimals);
    }

    function formatValue(value, decimals) {
        if (typeof decimals == 'undefined') decimals = 2;

        return isNaN(value) ? '—' : parseFloat(value).toFixed(decimals);
    }

    function generateIngredient() {
        const rowNumber = $('.ingredient').length;

        // get pattern
        let row = $('#row_pattern').clone();

        // change tr attributes
        row.attr('id', `ingredient_${rowNumber}_row`);
        row.addClass('ingredient');

        // change td attributes
        row.find('.proteins').attr('id', `ingredient_${rowNumber}_proteins`);
        row.find('.fats').attr('id', `ingredient_${rowNumber}_fats`);
        row.find('.carbohydrates').attr('id', `ingredient_${rowNumber}_carbohydrates`);
        row.find('.calories').attr('id', `ingredient_${rowNumber}_calories`);
        row.find('.units').attr('id', `ingredient_${rowNumber}_units`);

        // change select
        let select = row.find('.ingredient-id');
        select.addClass('choosen-ingredient-anchor');
        select.attr('name', `ingredients[${rowNumber}][ingredient_id]`);
        select.attr('id', `ingredient_${rowNumber}_select`);
        select.attr('data-row-id', rowNumber);
        select.select2(getSelect2Options());

        // change amount
        let amount = row.find('.amount-anchor');
        amount.attr('name', `ingredients[${rowNumber}][amount]`);
        amount.attr('data-row-id', rowNumber);

        // add delete button
        row.append(
            `<td class="row-text"><button class="delete-ingredient-anchor button-round btn-danger" type="button" data-id="${rowNumber}" aria-label="Delete ingredient"><i class="fa fa-trash" aria-hidden="true"></i></button></td>`);

        return row;
    }

    function deleteIngredient(id) {
        $(`#ingredient_${id}_row`).remove();

        updateIngredientNumbers();
        calculateIngredientsTotal();
        calculateRecipeDiet();
    }

    function updateIngredientNumbers() {
        let counter = 0;

        $('.ingredient').each(function () {
            // update row id
            $(this).attr('id', `ingredient_${counter}_row`);

            // update row fields
            $(this).find('.proteins').attr('id', `ingredient_${counter}_proteins`);
            $(this).find('.fats').attr('id', `ingredient_${counter}_fats`);
            $(this).find('.carbohydrates').attr('id', `ingredient_${counter}_carbohydrates`);
            $(this).find('.calories').attr('id', `ingredient_${counter}_calories`);
            $(this).find('.units').attr('id', `ingredient_${counter}_units`);
            $(this).find('.delete-ingredient-anchor').attr('data-id', counter);

            let select = $(this).find('.ingredient-id');
            select.select2('destroy');
            select.attr('id', `ingredient_${counter}_select`);
            select.attr('name', `ingredients[${counter}][ingredient_id]`);
            select.attr('data-select2-id', `ingredient_${counter}_select`); // TODO: incorrect data-select2-id [object HTMLInputElement]
            select.attr('data-row-id', counter);
            select.select2(getSelect2Options());

            let amount = $(this).find('.amount-anchor');
            amount.attr('name', `ingredients[${counter}][amount]`);
            amount.attr('data-row-id', counter);

            counter++;
        });
    }

    function calculateIngredientsTotal() {
        let totals = {
            proteins: 0,
            fats: 0,
            carbohydrates: 0,
            calories: 0,
        };

        // calculate
        $('.ingredient').each(function () {
            if (!isNaN(parseFloat($(this).find('.proteins').text()))) {
                totals.proteins += parseFloat($(this).find('.proteins').text());
                totals.fats += parseFloat($(this).find('.fats').text());
                totals.carbohydrates += parseFloat(
                    $(this).find('.carbohydrates').text());
                totals.calories += parseFloat($(this).find('.calories').text());
            }
        });

        // write to total row
        $('#total_proteins').text(totals.proteins.toFixed(2));
        $('#total_proteins_input').val(totals.proteins.toFixed(2));

        $('#total_fats').text(totals.fats.toFixed(2));
        $('#total_fats_input').val(totals.fats.toFixed(2));

        $('#total_carbohydrates').text(totals.carbohydrates.toFixed(2));
        $('#total_carbohydrates_input').val(totals.carbohydrates.toFixed(2));

        $('#total_calories').text(totals.calories.toFixed(2));
        $('#total_calories_input').val(totals.calories.toFixed(2));
    }

    /**
     * Variable Ingredients
     */

    // choose variable ingredient action
    $('.variable-ingredient-anchor').on('change', function () {
        // get row type
        const id = $(this).attr('data-category-id');
        // get selected option
        const selected = $(this).find(':selected');
        // get row
        let row = $(`#ingredient_category_${id}_row`);

        let diets = '<div class="d-flex">';

        if (selected.attr('data-diets') === '') {
            diets += '—';
        } else {
            selected.attr('data-diets').split('|').forEach((diet) => {
                diets += `<span class="label label-info mr-1">${diet}</span> `;
            });
        }

        diets += '</div>';

        row.find('.diets').empty().append(diets);
        row.find('.proteins').text(formatValue(selected.attr('data-proteins'), 1));
        row.find('.fats').text(formatValue(selected.attr('data-fats'), 1));
        row.find('.carbohydrates').text(formatValue(selected.attr('data-carbohydrates'), 1));
        row.find('.calories').text(formatValue(selected.attr('data-calories'), 0));
        row.find('.units').text(selected.attr('data-unit-default-amount') + ' ' +
            selected.attr('data-unit'));

        let calculatedRow = $(`#calculated_ingredient_category_${id}_row`);
        calculatedRow.addClass(`variable-ingredient-${selected.val()}-anchor`);
        calculatedRow.find('.name').text(selected.text());
        calculatedRow.find('.proteins').text(formatValue(selected.attr('data-proteins'), 1));
        calculatedRow.find('.fats').text(formatValue(selected.attr('data-fats'), 1));
        calculatedRow.find('.carbohydrates').text(formatValue(selected.attr('data-carbohydrates'), 1));
        calculatedRow.find('.calories').text(formatValue(selected.attr('data-calories'), 0));
        calculatedRow.find('.units').text(selected.attr('data-unit-default-amount') + ' ' +
            selected.attr('data-unit'));

        calculateRecipeDiet();
    });

    // calculate variable ingredients
    $('#calculate_variable_ingredients').on('click', function (e) {
        e.preventDefault();

        const kh = $('input[name=calculate_kh]').val();
        const kcal = $('input[name=calculate_kcal]').val();

        if (kh === '' || kcal === '') {
            $('#calculation_error_alert').text('Empty values').show();
            return false;
        } else {
            $('#calculation_error_alert').hide();
        }

        let ingredients = {
            'common': [],
            'variable': [],
        };

        $('.choosen-ingredient-anchor').each(function () {
            if ($(this).val() !== '0') {
                if ($(this).hasClass('variable-ingredient-anchor')) {
                    ingredients.variable.push($(this).val());
                } else {
                    ingredients.common.push({
                        'id': $(this).val(),
                        'amount': $(this).parents('.ingredient').find('.amount-anchor').val(),
                    });
                    // ingredients.common.push($(this).val());
                }
            }
        });

        // /admin/recipes/create
        $.ajax({
            type: 'POST',
            url: '/admin/recipes/calculate-variable-ingredients',
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                ingredients: ingredients,
                kh: kh,
                kcal: kcal,
            },
            beforeSend: function () {
                $('#loading').show();
            },
            complete: function () {
                $('#loading').hide();
            },
            success: function (resp) {
                if (resp.errors) {
                    $('#calculation_error_alert').text(resp.notices).show();
                }

                resp.ingradients_variable.forEach((ingredient) => {

                    let unitConst = 0;
                    let selected = null;
                    $('.variable-ingredient-anchor').each(function () {
                        if ($(this).find(':selected').val() == ingredient.id) {
                            unitConst = $(this).find(':selected').attr('data-unit-default-amount');
                            selected = $(this).find(':selected');
                        }
                    });

                    let row = $('#calculated_variable_ingredients_tbody').find(`.variable-ingredient-${ingredient.id}-anchor`);
                    row.find('.proteins').text(calculateValue(selected.attr('data-proteins'),
                        ingredient.amount, unitConst, 1));
                    row.find('.fats').text(calculateValue(selected.attr('data-fats'), ingredient.amount,
                        unitConst, 1));
                    row.find('.carbohydrates').text(calculateValue(selected.attr('data-carbohydrates'),
                        ingredient.amount, unitConst, 1));
                    row.find('.calories').text(calculateValue(selected.attr('data-calories'),
                        ingredient.amount, unitConst, 0));
                    row.find('.units').text(ingredient.amount + ' ' + ingredient.unit.full_name);
                });

            },
            error: function (err) {
                console.log(err);
            },
        });
    });

    /**
     * Steps
     */

    // new step handler
    $('#new_step_button').on('click', function (e) {
        e.preventDefault();

        generateRecipeStep();
    });

    // delete step handler
    $('#recipe_steps_container').on('click', '.delete-step-anchor', function (e) {
        e.preventDefault();

        if (confirm('Delete step?')) {
            deleteRecipeStep($(this).attr('data-step'));
        }
    });

    function generateRecipeStep() {
        let stepNumber = $('.step-row').length + 1;

        let element = `
        <div class="form-group form-element-text step-row" id="step_${stepNumber}">
            <div class="d-flex align-center mb-2">
                <label for="step_${stepNumber}_description" class="control-label mr-1">Step ${stepNumber}</label>
                <button type="button" class="delete-step-anchor button-round btn-danger" aria-label="Delete recipe step" data-step="${stepNumber}">
                    <span class="fa fa-trash" aria-hidden="true"></span>
                </button>
            </div>
            <textarea name="steps[${stepNumber}][description]" id="step_${stepNumber}_description" class="form-control step-description" cols="30" rows="5"></textarea>
        </div>`;

        $('#recipe_steps_container').append(element);
    }

    function deleteRecipeStep(id) {
        $(`#step_${id}`).remove();

        updateStepsNumbers();
    }

    function updateStepsNumbers() {
        let counter = 1;

        $('.step-row').each(function () {
            // update row id
            $(this).attr('id', `step_${counter}`);

            let label = $(this).find('.control-label');
            label.attr('for', `step_${counter}_description`);
            label.text(`Step ${counter}`);

            let stepDesc = $(this).find('.step-description');
            stepDesc.attr('name', `steps[${counter}][description]`);
            stepDesc.attr('id', `step_${counter}_description`);

            let stepId = $(this).find('.step-id');
            stepId.attr('name', `steps[${counter}][id]`);

            counter++;
        });
    }

    $('.variable-ingredient-anchor').trigger('change');

    // set active tabs
    setActiveTab();
});
