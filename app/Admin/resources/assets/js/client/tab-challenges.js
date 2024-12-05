jQuery(document).ready(function ($) {
    $('.start_at').datepicker({
        dateFormat: 'dd.mm.yy',
    });
    $('.user-challenge-edit').on('click', function (e) {
        let userCourseId = $(this).attr('data-userCourse'),
            courseId = $(this).attr('data-course'),
            courseStartDate = $(this).attr('data-courseStartDate'),
            pattern = /(\d{2})\.(\d{2})\.(\d{4})/,
            dt = new Date(courseStartDate.replace(pattern, '$3-$2-$1'));

        Swal.fire({
            title: window.foodPunk.i18n.common_change_date_title,
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
            if (!result.value) {
                return;
            }
            const date = $('#datetimepicker').val();
            Swal.fire({
                title: window.foodPunk.i18n.messages_wait,
                text: window.foodPunk.i18n.messages_in_progress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.ajax({
                type: 'POST',
                url: "{{ route('admin.client.course.edit') }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    user_course_id: userCourseId,
                    course_id: courseId,
                    start_at: date,
                },
                success: function (result) {
                    location.reload();
                },
                error: function (result) {
                    Swal.hideLoading();
                    Swal.fire({
                        title: 'Error!',
                        html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                        icon: 'error',
                    });
                },
            });
        });
    });

    $('.user-challenge-delete').on('click', function (e) {
        let userCourseId = $(this).attr('data-userCourse');

        Swal.fire({
            title:  window.foodPunk.i18n.messages_confirmation,
            text:  window.foodPunk.i18n.messages_revert_info,
            type: 'warning',
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            if (!result.value) {
                return;
            }
            Swal.fire({
                title: window.foodPunk.i18n.messages_wait,
                text: window.foodPunk.i18n.messages_in_progress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.ajax({
                type: 'DELETE',
                url: "{{ route('admin.client.course.destroy') }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    user_course_id: userCourseId,
                },
                success: function (result) {
                    location.reload();
                }, error: function (result) {
                    Swal.hideLoading();
                    Swal.fire({
                        title: 'Error!',
                        html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                        icon: 'error',
                    });
                },
            });
        });
    });
});