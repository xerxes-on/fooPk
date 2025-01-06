export function initNutrientsCalculations() {
    $('#allow_custom_nutrients').on('change', function () {
        let allowed = $(this).prop('checked');
        $('.allowed_custom_nutrients').attr('readonly', !allowed);
        $('.allowed_custom_nutrients_btn').css('display', (allowed) ? 'inline-block' : 'none');
    });
    $('#allow_custom_nutrients').trigger('click');

    $('#calc_auto').on('change', function () {
        let approve = $(this).prop('checked');

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
                $(this).prop('checked', !approve);
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
                type: 'POST',
                url: window.FoodPunk.route.clientCalcAuto,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    userId: window.FoodPunk.pageInfo.clientId,
                    approve: approve,
                },
                success: function (data) {
                    Swal.hideLoading();
                    if (data.success === true) {
                        Swal.fire({
                            icon: 'success',
                            title: window.FoodPunk.i18n.saved,
                            html: data.message,
                        });
                        return;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: data.message,
                    });
                },
            });
        });
    });
}
