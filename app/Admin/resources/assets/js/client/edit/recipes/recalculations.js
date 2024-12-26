import {$tableRecipes} from './recipesConst.js';

export function recalculateUserRecipes() {
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

            $.ajax({
                type: 'POST',
                url: window.FoodPunk.route.recalculateToUser,
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
                            title: window.FoodPunk.i18n.success,
                            text: window.FoodPunk.i18n.recordRecalculatedSuccessfully,
                            html: data.message,
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: data.message,
                        });
                    }
                    $tableRecipes.ajax.reload();
                },
                error: function (data) {
                    Swal.hideLoading();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: data.responseJSON.message,
                    });
                    $tableRecipes.ajax.reload();
                },
            });
        }
    });
}
