// Generates new recipes for a user. Sends a request to the server and reloads the "Recipes By Challenge" table upon success
import {$recipesByChallenge} from './recipesConst.js';

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
