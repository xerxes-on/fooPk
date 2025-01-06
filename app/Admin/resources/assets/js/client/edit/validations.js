export function setupValidations() {
    const $form = $('#formularEdit');
    $form.validate({
        lang: 'de',
        ignore: [],
        focusInvalid: false,
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('help-block alert alert-danger');
            error.insertAfter($(element).closest('.form-group'));
        },
        highlight: function (element) {
            $(element).closest('.form-group')
                .addClass('has-error')
                .removeClass('has-success');
        },
        unhighlight: function (element) {
            $(element).closest('.form-group')
                .addClass('has-success')
                .removeClass('has-error');
        },
        invalidHandler: function (form, validator) {
            if (!validator.numberOfInvalids()) return;
            $('html, body').animate({
                scrollTop: $(validator.errorList[0].element).offset().top - 150,
            }, 1000);
        },
        groups: {
            particularly_important:
                '14[answer][ketogenic] 14[answer][low_carb] 14[answer][moderate_carb] ' +
                '14[answer][paleo] 14[answer][vegetarian] 14[answer][vegan] 14[answer][pescetarisch] ' +
                '14[answer][aip] 14[answer][no_matter]',
        },
    });
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
                    url: window.FoodPunk.route.clientCalcAuto,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.pageInfo.clientId,
                        approve: approve,
                    },
                    success: function (data) {
                        if (data.success === true) {
                            Swal.hideLoading();
                            Swal.fire({
                                icon: 'success',
                                title: window.FoodPunk.i18n.saved,
                                html: data.message,
                            });
                        } else {
                            Swal.hideLoading();
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: data.message,
                            });
                        }
                    },
                });
            } else {
                $(this).prop('checked', !approve);
            }
        });

    });
}
