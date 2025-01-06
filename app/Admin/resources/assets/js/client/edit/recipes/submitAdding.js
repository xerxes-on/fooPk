import {$SubmitAddRecipes, selectedPopupRecipesStorage, selectedUsersStorage} from './recipesConst';

/**
 * Submits selected recipes to be added to a user.
 *
 * @function submitAdding
 *
 * @description
 * - Retrieves selected recipes and users from localStorage.
 * - Sends a POST request to associate the selected recipes with the specified user.
 * - Displays a loading indicator during the request and success or error messages based on the response.
 * - Clears the selected recipes and users from localStorage after the operation completes.
 *
 * @returns {void}
 */

export function submitAdding() {
    const rowsSelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
    const usersSelected = JSON.parse(localStorage.getItem(selectedUsersStorage));

    $.ajax({
        type: 'POST',
        url: window.FoodPunk.route.addToUser,
        dataType: 'json',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            userIds: [window.FoodPunk.pageInfo.clientId],
            recipeIds: rowsSelected.selected,
        },
        beforeSend: function () {
            $.colorbox.close();
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
            if (window.FoodPunk.pageInfo.hideRecipesRandomizer && $SubmitAddRecipes) {
                $SubmitAddRecipes.start();
            }
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

            if (window.FoodPunk.pageInfo.hideRecipesRandomizer && $SubmitAddRecipes) {
                $SubmitAddRecipes.stop();
            }

            localStorage.removeItem(selectedPopupRecipesStorage);
            localStorage.removeItem(selectedUsersStorage);
        },
        error: function (jqXHR) {
            console.error(jqXHR);
            Swal.hideLoading();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: jqXHR.responseJSON?.message || 'Request Failed',
            });
            if (window.FoodPunk.pageInfo.hideRecipesRandomizer && $SubmitAddRecipes) {
                $SubmitAddRecipes.stop();
            }
        },
    });
}
