export function initSubscriptions() {
    $('#chargebee-subscription-add').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        Swal.fire({
            title: window.FoodPunk.i18n.subscriptionId,
            html: '<input id="chargebee-subscription-id" class="form-control" /><span id="chargebee-subscription-id-info-text"></span>',
            icon: 'question',
            didOpen: function () {
                //
            },
        }).then(function (result) {
            if (result.value) {

                let subscriptionId = $('#chargebee-subscription-id').val();

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
                    url: window.FoodPunk.route.assignChargebeeSubscription,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        chargebee_subscription_id: subscriptionId,
                        client_id: window.FoodPunk.pageInfo.clientId,
                    },
                    success: function (result) {
                        location.reload();
                    },
                    error: function (data) {
                        let response = JSON.parse(data.responseText),
                            errorString = '<ul style="text-align: left;">';
                        $.each(response.errors, function (key, value) {
                            errorString += '<li>' + value + '</li>';
                        });
                        errorString += '</ul>';

                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            html: errorString,
                        });
                    },
                });
            }
        });
    });
    $('.user-subscription-edit').on('click', function (e) {
        let subscriptionId = $(this).attr('data-subscription'),
            url = window.FoodPunk.route.subscriptionEdit,
            subscriptionEndDate = $(this).attr('data-subscriptionStartDate'),
            pattern = /(\d{2})\.(\d{2})\.(\d{4})/,
            dt = new Date(subscriptionEndDate.replace(pattern, '$3-$2-$1'));

        url = url.replace(':id', subscriptionId);

        Swal.fire({
            title: window.FoodPunk.i18n.confirmDetails,
            html: '<input id="datetimepicker" class="form-control">',
            icon: 'question',
            showCancelButton: true,
            didOpen: function () {
                $('#datetimepicker').datepicker({
                    dateFormat: 'dd.mm.yy',
                    defaultDate: new Date(),
                }).datepicker('setDate', dt);
            },
        }).then(function (result) {
            if (result.value) {

                let ends_at = $('#datetimepicker').val();

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
                    type: 'PUT',
                    url: url,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        ends_at: ends_at,
                    },
                    success: function (result) {
                        location.reload();
                    },
                    error: function (data) {
                        let response = JSON.parse(data.responseText),
                            errorString = '<ul style="text-align: left;">';
                        $.each(response.errors, function (key, value) {
                            errorString += '<li>' + value + '</li>';
                        });
                        errorString += '</ul>';

                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            html: errorString,
                        });
                    },
                });
            }
        });

    });
    $('.user-subscription-stop').on('click', function (e) {
        let subscriptionId = $(this).attr('data-subscription'),
            url = window.FoodPunk.route.subscriptionStop;

        url = url.replace(':id', subscriptionId);

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
                    type: 'PUT',
                    url: url,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                    },
                    success: function (result) {
                        location.reload();
                    },
                    error: function (data) {
                        let response = JSON.parse(data.responseText),
                            errorString = '<ul style="text-align: left;">';
                        $.each(response.errors, function (key, value) {
                            errorString += '<li>' + value + '</li>';
                        });
                        errorString += '</ul>';

                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            html: errorString,
                        });
                    },
                });
            }
        });

    });
    $('.user-subscription-delete').on('click', function (e) {
        let subscriptionId = $(this).attr('data-subscription'),
            url = window.FoodPunk.route.subscriptionDelete

        url = url.replace(':id', subscriptionId);

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
                    type: 'DELETE',
                    url: url,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                    },
                    success: function (result) {
                        location.reload();
                    },
                    error: function (data) {
                        let response = JSON.parse(data.responseText),
                            errorString = '<ul style="text-align: left;">';
                        $.each(response.errors, function (key, value) {
                            errorString += '<li>' + value + '</li>';
                        });
                        errorString += '</ul>';

                        Swal.fire({
                            icon: 'error',
                            title: response.message,
                            html: errorString,
                        });
                    },
                });
            }
        });
    });
    $('#subscription-create').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let activeChallenge = window.FoodPunk.pageInfo.activeChallenge

        if (activeChallenge > 0) {
            Swal.fire({
                title: window.FoodPunk.i18n.confirmation,
                text: window.FoodPunk.i18n.subscriptionStopped,
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

                    $(this).closest('form').submit();
                }
            });
        } else {
            $(this).closest('form').submit();
        }

    });
}
