export default function initQuestionnaire() {
    $('#approve_formular').change(function () {
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
                return
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
                url: window.FoodPunk.route.questionnaireApprove,
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
                        if (approve) location.reload();
                        return;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: data.message,
                    });
                },
                error: function (data) {
                    Swal.fire({
                        icon: 'error',
                        title: JSON.parse(data.responseText).message,
                    });
                },
            });
        });
    });

    $('#toggle_formular').change(function () {
        let currentState = $(this).prop('checked');
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
                $(this).prop('checked', currentState);
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
                url: window.FoodPunk.route.questionnaireToggle,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    clientId: window.FoodPunk.pageInfo.clientId,
                    is_editable: currentState,
                },
                success: function (data) {
                    Swal.hideLoading();
                    if (data.success === true) {
                        Swal.fire({
                            icon: 'success',
                            title: window.FoodPunk.i18n.changesApplied,
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
                error: function (data) {
                    console.log(data);
                    Swal.fire({
                        icon: 'error',
                        title: JSON.parse(data.responseText).message,
                    });
                },
            });
        });
    });

    $('.compare-formular').on('click', function (e) {
        let questionnaireId = $(this).attr('data-formular');

        $.ajax({
            type: 'GET',
            url: window.FoodPunk.route.questionnaireCompare,
            dataType: 'json',
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                clientId: window.FoodPunk.pageInfo.clientId,
                questionnaireId: questionnaireId,
            },
            success: function (data) {
                if (data.success === true) {
                    $.each(data.data, function (index, value) {
                        $('tr[data-answer=\'' + index + '\'] .compare-answer').html(value);
                    });
                    $('.compare-answer-id').html('(#' + questionnaireId + ')');
                    return;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: data.message,
                });
            },
            error: function (data) {
                Swal.fire({
                    icon: 'error',
                    title: JSON.parse(data.responseText).message,
                });
            },
        });
    });
}
