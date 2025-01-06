import {$recipesByChallenge} from './recipesConst.js';

/**
 * Generates a recipe for a specific user after user confirmation.
 *
 * @function generateRecipe
 *
 * @description
 * - Prompts the user for confirmation before initiating recipe generation.
 * - Sends a POST request to the server to generate a recipe for the specified user.
 * - Displays success or error messages based on the server response.
 * - Reloads the recipes table if `$recipesByChallenge` is available.
 */
export function generateRecipe() {
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
        if (!result.value) return;

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
            type: 'POST',
            url: window.FoodPunk.route.recipesGenerateToSub,
            dataType: 'json',
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                userId: window.FoodPunk.pageInfo.clientId,
            },
            success: function (data) {
                Swal.hideLoading();
                if (data.success) {
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
                if ($recipesByChallenge) {
                    $recipesByChallenge.ajax.reload();
                }
            },
        });
    });
}
