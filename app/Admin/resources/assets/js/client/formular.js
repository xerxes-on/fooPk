$(document).ready(function () {
    let $form = $('#formularEdit');

    $form.validate({
        lang: 'de',
        ignore: [],
        focusInvalid: false,
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('help-block alert alert-danger');
            error.insertAfter($(element).closest('.form-group'));
        },
        highlight: function (element, errorClass, validClass) {
            $(element).closest('.form-group').addClass('has-error').removeClass('has-success');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).closest('.form-group').addClass('has-success').removeClass('has-error');
        },
        invalidHandler: function (form, validator) {
            if (!validator.numberOfInvalids()) return;

            $('html, body').animate({
                scrollTop: $(validator.errorList[0].element).offset().top - 150,
            }, 1000);
        },
        groups: {
            particularly_important: '14[answer][ketogenic] 14[answer][low_carb] 14[answer][moderate_carb] 14[answer][paleo] 14[answer][vegetarian] 14[answer][vegan] 14[answer][pescetarisch] 14[answer][aip] 14[answer][no_matter]',
        },
    });
});