jQuery(document).ready(function ($) {
    // Validate on number fields
    $('.js-validate-number-input').on('input', function () {
        // Update the input's value allowing only digits
        const value = this.value;
        const regex = /^(?:\d{1,3}(?:,\d{3})*|\d+)(?:\.\d+)?$/;

        if (!regex.test(value)) {
            // Remove any non-digit and non-',' character from the input value
            this.value = value.replace(regex, '');
        }
    });

    // Validate Main gail
    const mainGoalValidation = function () {
        const current = $(this);
        const weightGoal = $('#weight_goal');
        weightGoal.attr('disabled', true);
        $('#question_extra_goal input').each(function () {
            $(this).removeAttr('disabled');
        });
        switch (current.val()) {
            case 'lose_weight':
                weightGoal.removeAttr('disabled');
                $('#extra_goal_reduce_body_fat').attr('disabled', true);
                break;
            case 'improve_fitness':
                $('#extra_goal_improve_daily_energy').attr('disabled', true);
                break;
            case 'build_muscle':
                $('#extra_goal_build_muscle').attr('disabled', true);
                break;
            case 'gain_weight':
                weightGoal.removeAttr('disabled');
                break;
            default:
        }
    };
    $('#question_main_goal input[type="radio"]').on('change', mainGoalValidation);
    $('#question_main_goal input[type="radio"]:checked').each(mainGoalValidation);

    // Validate Weight Goal
    const weightGoal = $('#weight_goal');
    weightGoal.attr('min', 40).attr('max', 200);
    weightGoal.on('change', function () {
        this.value = validateMinMaxValue(this.value, 40, 200);
    });

    // Validate Diets
    const dietsValidation = function () {
        // Check if no diet is selected,We need to release the locks
        let allUnchecked = true;
        $('#question_diets input[type="checkbox"]').each(function () {
            if (this.checked) {
                allUnchecked = false;
            }
        });
        if (allUnchecked) {
            $('#question_diets input[type="checkbox"]').not(this).attr('disabled', false);
            $('#meals_per_day_breakfast_lunch').attr('disabled', false);
            $('#meals_per_day_breakfast_dinner').attr('disabled', false);
            return;
        }

        // Unlock all except the current
        $('#question_diets input[type="checkbox"]').not(this).attr('disabled', false);

        // Disable excluded
        this.dataset.exclude.split(';').forEach(function (item) {
            $('#diets_' + item).prop('checked', false);
        });

        // TODO: temporary solution, remove as recipes would be available
        let selectedValues = [];
        $('#question_diets input[type="checkbox"]:checked').each(function (key, item) {
            selectedValues.push(item.value);
        });
        if (selectedValues.includes('moderate_carb')) {
            $('#meals_per_day_breakfast_lunch').prop('checked', false).attr('disabled', true);
            $('#meals_per_day_breakfast_dinner').prop('checked', false).attr('disabled', true);
            return;
        }
        $('#meals_per_day_breakfast_lunch').attr('disabled', false);
        $('#meals_per_day_breakfast_dinner').attr('disabled', false);
    };
    $('#question_diets input[type="checkbox"]').on('change', dietsValidation);
    $('#question_diets input[type="checkbox"]:checked').each(dietsValidation);

    // Validate Allergies
    $('#allergies_other').on('change', function () {
        if (this.value === 'other' && this.checked) {
            $('.js-allergies-other').show();
            return;
        }
        $('.js-allergies-other').hide();
        $('#allergies_other_text').val('');
    });

    // Setup Searchable select2
    $('#exclude_ingredients').select2({
        ajax: {
            url: function (params) {
                return $(this).attr('data-route');
            },
            dataType: 'json',
            cache: true,
            delay: 1000,
        },
        multiple: true,
        minimumInputLength: 3,
    }).on('select2:select', function (e) {
        let ids = [];
        const select = $('#exclude_ingredients');
        if (e.params.data.ingredients && e.params.data.ingredients.length > 0) {
            e.params.data.ingredients.forEach(function (item) {
                // Creating a new option if necessary
                if (select.find('option[value=\'' + item.id + '\']').length === 0) {
                    select.append(new Option(item.text, item.id, false, false)).trigger('change');
                }

                ids.push(item.id);
            });
            ids.filter(function (i) {
                return i != e.params.data.id;
            });
        } else {
            ids.push(e.params.data.id);
        }

        // select every previously selected value
        select.select2('data').forEach(function (item) {
            if (item.ingredients) {

                // Add all previous groups
                item.ingredients.forEach(function (ingredient) {
                    ids.push(ingredient.id);
                });
                // remove main group if existed
                ids.filter(function (i) {
                    return i != e.params.data.id;
                });
                return;
            }
            ids.push(item.id);
        });

        // Remove duplications
        ids = ids.filter(onlyUnique);

        // Assign the new value
        $(this).val(ids).trigger('change');
    });

    // Validate Sport
    $('#question_sports input[type="checkbox"]').on('change', function () {
        switch (this.value) {
            case 'easy':
                toggleVisibility('.js-sports-easy', this.checked);
                break;
            case 'medium':
                toggleVisibility('.js-sports-medium', this.checked);
                break;
            case 'intensive':
                toggleVisibility('.js-sports-intensive', this.checked);
                break;
        }
    });

    // Validate Sport duration & frequency
    const sportsFrequency = $('.js-sports-frequency');
    const sportsDuration = $('.js-sports-duration');
    sportsFrequency.attr('max', 7);
    sportsDuration.attr('max', 120);
    sportsFrequency.on('change', function () {
        this.value = validateMinMaxValue(this.value, 1, 7);
    }).attr('min', 1);
    sportsDuration.on('change', function () {
        this.value = validateMinMaxValue(this.value, 1, 120);
    }).attr('min', 1);

    // Validate Diseases
    $('#diseases_other').on('change', function () {
        if (this.value === 'other' && this.checked) {
            $('.js-diseases-other').show();
            return;
        }
        $('.js-diseases-other').hide();
        $('#diseases_other_text').val('');
    });

    // Validate Birthdate
    const birthdate = $('#birthdate');
    birthdate.on('change, blur', function () {
        // Get the entered date value
        const enteredDate = $(this).datepicker('getDate');

        if (!enteredDate && !this.value) {
            $(this).datepicker('setDate', new Date(this.dataset.oldValue));
            this.setCustomValidity('Age must be between 16 and 100 years.');
            this.reportValidity();
            $('#question_birthdate').addClass('has-error');
            return;
        }

        console.log(this.value,
            enteredDate);

        // Calculate the user's age in milliseconds
        const ageInMs = Date.now() - enteredDate.getTime();
        // Calculate the user's age in years
        const ageInYears = Math.floor(ageInMs / (1000 * 60 * 60 * 24 * 365.25));

        // Check if age is within the allowed range
        if (ageInYears < 16 || ageInYears > 100) {
            this.setCustomValidity('Age must be between 16 and 100 years.');
            this.reportValidity();
            $('#question_birthdate').addClass('has-error');
            $(this).datepicker('setDate', new Date(this.dataset.oldValue));
            return;
        }
        this.setCustomValidity('');
        $('#question_birthdate').removeClass('has-error');
    });

    // Validate Height
    $('#height').on('change', function () {
        this.value = validateMinMaxValue(this.value, 100, 250);
    });

    // Validate Weight
    $('#weight').on('change', function () {
        this.value = validateMinMaxValue(this.value, 40, 200);
    });

    // Validate on required fields
    $('.needs-validation .form-group input').on('change', function () {
        $(this).parents('.form-group').removeClass('has-error');
    });
    $('.needs-validation').on('submit', function (event) {
        // TODO: maybe add loader icon to button to show ongoing process?
        $(this).find('.form-group.required').each(function () {
            if ($(this).find('input[type="checkbox"]').length > 0) {
                if ($(this).find('input[type="checkbox"]:checked').length === 0) {
                    $(this).addClass('has-error');
                } else {
                    $(this).removeClass('has-error');
                }
            } else if ($(this).find('input[type="radio"]').length > 0) {
                if ($(this).find('input[type="radio"]:checked').length === 0) {
                    $(this).addClass('has-error');
                } else {
                    $(this).removeClass('has-error');
                }
            } else if ($(this).find('input[type="date"]').length > 0) {
                if ($(this).find('input[type="date"]').val() === '') {
                    $(this).addClass('has-error');
                } else {
                    $(this).removeClass('has-error');
                }
            } else if ($(this).find('input[type="number"]').length > 0) {
                if ($(this).find('input[type="number"]').val() === '') {
                    $(this).addClass('has-error');
                } else {
                    $(this).removeClass('has-error');
                }
            } else {
                if ($(this).find('input').val() === '') {
                    $(this).addClass('has-error');
                } else {
                    $(this).removeClass('has-error');
                }
            }
        });
        if ($(this).find('.form-group.required.has-error').length > 0) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
    // end
});

function toggleVisibility(selector, show) {
    if (show) {
        $(selector).show();
    } else {
        $(selector).hide();
        $(selector + ' input').each(function () {
            this.value = '';
        });
    }
}

function validateMinMaxValue(value, min, max) {
    // Ensure the value is within the desired range
    if (value < min) {
        value = min;
    } else if (value > max) {
        value = max;
    }

    // Update the value
    return value;
}

function onlyUnique(value, index, array) {
    return array.indexOf(value) === index;
}