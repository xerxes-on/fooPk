import {$SubmitAddRecipes, selectedPopupRecipesStorage, selectedUsersStorage} from './recipesConst.js';

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
            Swal.hideLoading();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: jqXHR.responseJSON?.message || 'Request Failed',
            });
            console.error(jqXHR);
            if (window.FoodPunk.pageInfo.hideRecipesRandomizer && $SubmitAddRecipes) {
                $SubmitAddRecipes.stop();
            }
        },
    });
}
