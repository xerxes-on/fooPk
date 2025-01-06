export function initCourses() {
    $('.start_at').datepicker({
        dateFormat: 'dd.mm.yy',
    });

    $('.user-challenge-edit').on('click', function () {
        const userCourseId = $(this).attr('data-userCourse');
        const courseId = $(this).attr('data-course');
        const courseStartDate = $(this).attr('data-courseStartDate');
        const pattern = /(\d{2})\.(\d{2})\.(\d{4})/;
        const dt = new Date(courseStartDate.replace(pattern, '$3-$2-$1'));

        Swal.fire({
            title: window.FoodPunk.i18n.changeDateTitle,
            html: $('#hidden-template').html(),
            icon: 'question',
            showCancelButton: true,
            didOpen: function () {
                $('#datetimepicker').datepicker({
                    dateFormat: 'dd.mm.yy',
                    defaultDate: dt,
                }).datepicker('setDate', dt);
            },
        }).then(function (result) {
            if (!result.value) return;

            const date = $('#datetimepicker').val();
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
                url: window.FoodPunk.route.courseEdit,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    user_course_id: userCourseId,
                    course_id: courseId,
                    start_at: date,
                },
                success: function () {
                    location.reload();
                },
                error: function (result) {
                    Swal.hideLoading();
                    Swal.fire({
                        title: window.FoodPunk.i18n.error,
                        html: result.responseJSON?.message
                            ? result.responseJSON.message
                            : window.FoodPunk.i18n.somethingWentWrong,
                        icon: 'error',
                    });
                },
            });
        });
    });

    $('.user-challenge-delete').on('click', function () {
        const userCourseId = $(this).attr('data-userCourse');

        Swal.fire({
            title: window.FoodPunk.i18n.confirmation,
            text: window.FoodPunk.i18n.revertInfo,
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
                type: 'DELETE',
                url: window.FoodPunk.route.courseDestroy,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    user_course_id: userCourseId,
                },
                success: function () {
                    location.reload();
                },
                error: function (result) {
                    Swal.hideLoading();
                    Swal.fire({
                        title: window.FoodPunk.i18n.error,
                        html: result.responseJSON?.message
                            ? result.responseJSON.message
                            : window.FoodPunk.i18n.somethingWentWrong,
                        icon: 'error',
                    });
                },
            });
        });
    });
}
