import {$tableRecipes} from './recipesConst.js';
import {renderCounterToolbarData} from './renderCounterToolbarData.js';

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
        if (result.value) {
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
                    $tableRecipes.ajax.reload();
                    renderCounterToolbarData();
                    Swal.hideLoading();

                    if (result.success) {
                        Swal.fire({
                            title: window.FoodPunk.i18n.success,
                            html: result.message || 'Success',
                            icon: 'success',
                        });
                    } else {
                        Swal.fire({
                            title: window.FoodPunk.i18n.error,
                            html: result.message || 'Something went wrong',
                            icon: 'error',
                        });
                        console.error(result);
                    }
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
        }
    });
}

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
        if (result.value) {
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
                    $tableRecipes.ajax.reload();
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
                        html:
                            result.responseJSON.message ||
                            window.FoodPunk.i18n.somethingWentWrong,
                        icon: 'error',
                    });
                },
            });
        }
    });
}
