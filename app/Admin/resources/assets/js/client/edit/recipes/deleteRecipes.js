import {renderCounterToolbarData} from './renderCounterToolbarData';

/**
 * Deletes a specific recipe by its ID after user confirmation.
 *
 * @function deleteRecipe
 * @param {HTMLElement} elem - The element triggering the delete action, containing a `data-id` attribute with the recipe ID.
 *
 * @description
 * - Prompts the user for confirmation before deleting the recipe.
 * - Sends a DELETE request to the server and reloads the recipes table upon success.
 * - Displays success or error messages based on the server response.
 */
export function deleteRecipe(elem) {
    const recipeId = $(elem).attr('data-id');

    Swal.fire({
        title: window.FoodPunk.i18n.confirmation,
        text: window.FoodPunk.i18n.revertWarning,
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: window.FoodPunk.i18n.defaultsExist,
        cancelButtonText: window.FoodPunk.i18n.defaultsMissing,
    }).then((result) => {
        if (!result.value) {
            return;
        }

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

        let route = window.FoodPunk.route.recipesDeleteByUser;
        route = route.replace('%', recipeId);

        $.ajax({
            type: 'DELETE',
            url: route,
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
            },
            dataType: 'json',
            success: function (result) {
                window.FoodPunk.$tableRecipes.ajax.reload();
                renderCounterToolbarData();
                Swal.hideLoading();

                if (result.success) {
                    Swal.fire({
                        title: window.FoodPunk.i18n.success,
                        html: result.message || 'Success',
                        icon: 'success',
                    });
                    return;
                }

                Swal.fire({
                    title: window.FoodPunk.i18n.error,
                    html: result.message || 'Something went wrong',
                    icon: 'error',
                });
                console.error(result);
            },
            error: function (result) {
                Swal.hideLoading();
                Swal.fire({
                    title: window.FoodPunk.i18n.error,
                    html:
                        result.responseJSON.message ||
                        window.FoodPunk.i18n.somethingWentWrong,
                    icon: 'error',
                });
            },
        });
    });
}

/**
 * Deletes all recipes for the current user after user confirmation.
 *
 * @function deleteAllRecipes
 *
 * @description
 * - Prompts the user for confirmation before deleting all recipes.
 * - Sends a DELETE request to the server and reloads the recipes table upon success.
 * - Displays success or error messages based on the server response.
 */
export function deleteAllRecipes() {
    Swal.fire({
        title: window.FoodPunk.i18n.deleteAllRecipesUser,
        text: window.FoodPunk.i18n.revertWarning,
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: window.FoodPunk.i18n.defaultsExist,
        cancelButtonText: window.FoodPunk.i18n.defaultsMissing,
    }).then((result) => {
        if (!result.value) {
            return;
        }
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

        $.ajax({
            type: 'DELETE',
            url: window.FoodPunk.route.deleteAllRecipes,
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
            },
            dataType: 'json',
            success: function (result) {
                window.FoodPunk.$tableRecipes.ajax.reload();
                $('#counterToolbar').html('');
                Swal.hideLoading();
                Swal.fire({
                    title: window.FoodPunk.i18n.deleted,
                    html: result.message,
                    icon: result.success ? 'success' : 'error',
                });
            },
            error: function (result) {
                Swal.hideLoading();
                Swal.fire({
                    title: window.FoodPunk.i18n.error,
                    html: result.responseJSON.message || window.FoodPunk.i18n.somethingWentWrong,
                    icon: 'error',
                });
            },
        });
    });
}
