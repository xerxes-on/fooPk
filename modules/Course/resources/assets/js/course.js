$(document).ready(function () {
    $('.js-unlock-course').on('click', function () {
        const parent = $(this).closest('.course-card');
        const startDate = parent.find('.date').val();

        if (startDate === '') {
            Swal.fire({
                title: window.foodPunk.course.i18n.start_date,
                timer: 2000,
                timerProgressBar: true,
                showCloseButton: true,
                showConfirmButton: false,
                toast: true,
            });
            return;
        }
        Swal.fire({
            title: window.foodPunk.course.i18n.confirm,
            showCancelButton: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            allowEnterKey: true,
            customClass: {
                confirmButton: 'btn btn-base btn-tiffany mr-10',
                cancelButton: 'btn btn-base btn-pink',
            },
            confirmButtonText: window.foodPunk.course.i18n.yes,
            cancelButtonText: window.foodPunk.course.i18n.cancel,
        })
            .then((result) => {
                if (!result.value) {
                    return;
                }

                // /user/challenge/buying
                $.ajax({
                    type: 'POST',
                    url: window.foodPunk.course.routes.buy,
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        challengeId: parent.attr('data-id'),
                        startDate: startDate,
                    },
                    beforeSend: function () {
                        $('#loading').show();
                    },
                    success: function (data) {
                        $('#loading').hide();
                        if (data.success) {
                            location.href = window.foodPunk.course.routes.index;
                        }
                    },
                    error: function (result) {
                        const data = result.responseJSON;
                        $('#loading').hide();
                        let params = {
                            title: 'Error!',
                            html: data.message ? data.message : 'Something went wrong.',
                            showCloseButton: true,
                            showCancelButton: false,
                            showConfirmButton: false,
                            toast: true,
                        }
                        if (data.errors.link && data.errors.link.url && data.errors.link.text) {
                            params.html = params.html + '<br><a href="' + data.errors.link.url + '" target="_blank">' + data.errors.link.text + '</a>';
                        }
                        Swal.fire(params);
                    },
                });
            });
    });

    $('.js-restart-course').on('click', function () {
        rescheduleCourse($(this), 'restart');
    });

    $('.js-change-course-date').on('click', function () {
        rescheduleCourse($(this), 'changeDate');
    });

    function rescheduleCourse($elem, $context) {
        Swal.fire({
            title: window.foodPunk.course.i18n[$context].title,
            showCancelButton: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            allowEnterKey: true,
            input: 'date',
            customClass: {
                confirmButton: 'btn btn-base btn-tiffany mr-10',
                cancelButton: 'btn btn-base btn-pink',
            },
            confirmButtonText: window.foodPunk.course.i18n[$context].approve,
            cancelButtonText: window.foodPunk.course.i18n.cancel,
            didOpen: () => {
                const today = (new Date()).toISOString().split("T")[0];
                const input = Swal.getInput();
                input.min = today;
                input.required = 'required';
                input.value = today;
            },
            inputValidator: (value) => {
                let initialValue = new Date(Swal.getInput().min);
                if (new Date(value) < initialValue) {
                    return window.foodPunk.course.i18n.dateValidationError.replace('@date', initialValue.toLocaleDateString("en-US"));
                }
            },
        }).then((result) => {
            if (!result.value) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: window.foodPunk.course.routes.reschedule,
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    courseId: $elem.attr('data-id'),
                    startDate: result.value,
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#loading').hide();
                    if (data.success) {
                        Swal.fire({
                            title: data.message,
                            timer: 2000,
                            timerProgressBar: true,
                            showCloseButton: true,
                            showCancelButton: false,
                            showConfirmButton: false,
                            toast: true,
                        }).then(() => {
                            location.href = window.foodPunk.course.routes.index
                        });
                    }
                },
                error: function (result) {
                    const data = result.responseJSON;
                    $('#loading').hide();
                    Swal.fire({
                        title: 'Error!',
                        html: data.message ? data.message : 'Something went wrong.',
                        showCloseButton: true,
                        showCancelButton: false,
                        showConfirmButton: false,
                        toast: true,
                    });
                },
            });
        });
    }
});