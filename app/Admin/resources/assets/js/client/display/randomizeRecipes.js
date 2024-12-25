import { getUserTable } from './userSelection.js';

/**
 * Opens a SweetAlert to ask for random recipe distribution info.
 * Returns an object with user-selected values or undefined if canceled.
 */
export async function inputRecipeAmount() {
    const { value: formValues } = await Swal.fire({
        title: window.FoodPunk.i18n.randomizeRecipesSettings,
        icon: 'question',
        html: `<div id="randomizeRecipeComponent"></div>`,
        willOpen: () => {
            Swal.showLoading();
            $.get(window.FoodPunk.route.randomizeRecipeTemplate, {}, (payload) => {
                Swal.hideLoading();
                Swal.getHtmlContainer().querySelector('#randomizeRecipeComponent').innerHTML = payload;
            });
        },
        preConfirm: () => {
            const container = Swal.getHtmlContainer();
            const seasons = [];
            const items = container.getElementsByClassName('selected_seasons');
            for (let i = 0; i < items.length; i++) {
                const val = items[i].value;
                if (items[i].checked && val.length > 0) {
                    seasons.push(val);
                }
            }
            return {
                amount: container.querySelector('input[name="amount_of_recipes"]').value,
                seasons: seasons,
                distribution_type: container.querySelector('input[name="distribution_type"]:checked').value,
                breakfast_snack: container.querySelector('input[name="breakfast_snack"]').value,
                lunch_dinner: container.querySelector('input[name="lunch_dinner"]').value,
                recipes_tag: container.querySelector('input[name="recipes_tag"]:checked').value,
                distribution_mode: container.querySelector('input[name="distribution_mode"]:checked').value,
            };
        },
    });

    return formValues;
}

export async function addRandomizeRecipes2selectUsers() {
    const $tableUsers = getUserTable();
    const usersSelected = $tableUsers.rows({ selected: true }).data();

    if (usersSelected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noUserSelected,
        });
        return false;
    }

    // Prompt for randomization details
    const initFormData = await inputRecipeAmount();
    if (!initFormData) return false;

    const {
        amount,
        seasons,
        distribution_type,
        breakfast_snack,
        lunch_dinner,
        recipes_tag,
        distribution_mode,
    } = initFormData;

    if (!amount || parseInt(amount) === 0) {
        return false;
    }

    // Collect user IDs
    const userIds = [];
    $.each(usersSelected, function (_, row) {
        userIds.push(row.id);
    });

    // Send AJAX request
    $.ajax({
        type: 'POST',
        url: window.FoodPunk.route.addToUserRandom,
        dataType: 'json',
        data: {
            _token: $('meta[name=csrf-token]').attr('content'),
            userIds,
            amount,
            seasons,
            distribution_type,
            breakfast_snack,
            lunch_dinner,
            recipes_tag,
            distribution_mode,
        },
        beforeSend: function () {
            Swal.fire({
                title: window.FoodPunk.i18n.wait,
                text: window.FoodPunk.i18n.inProgress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        },
        success: function (data) {
            Swal.hideLoading();
            if (data.success === true) {
                Swal.fire({
                    icon: 'success',
                    title: window.FoodPunk.i18n.saved,
                    html: data.message,
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: data.message,
                });
            }
        },
        error: function (jqXHR) {
            Swal.hideLoading();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: jqXHR.responseJSON?.message || 'Request Failed',
            });
            console.error(jqXHR);
        },
    });
}
