jQuery(document).ready(function ($) {

    $('#allow_custom_nutrients').on('change', function () {
        let allowed = $(this).prop('checked');
        $('.allowed_custom_nutrients').attr('readonly', !allowed);
        $('.allowed_custom_nutrients_btn').css('display', (allowed) ? 'inline-block' : 'none');
    });
    $('#allow_custom_nutrients').trigger('click');

    $('#calc_auto').on('change', function () {
        let approve = $(this).prop('checked');

        Swal.fire({
            title: window.foodPunk.i18n.messages_confirmation,
            text: window.foodPunk.i18n.messages_revert_warning,
            icon: 'warning',
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    title: window.foodPunk.i18n/messages_wait,
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
                    url: "{{ route('admin.client.calc-auto') }}",
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: '{{ $user_id }}',
                        approve: approve,
                    },
                    success: function (data) {
                        if (data.success === true) {
                            Swal.hideLoading();
                            Swal.fire({
                                icon: 'success',
                                title: window.foodPunk.i18n.messages_saved,
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

});