jQuery(document).ready(function ($) {

    $('#chargebee-subscription-add').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let url = "/admin/clients/assign-chargebee-subscription";

        Swal.fire({
            title: 'Enter Chargebee subscription id',
            html: '<input id="chargebee-subscription-id" class="form-control" /><span id="chargebee-subscription-id-info-text"></span>',
            icon: 'question',
            didOpen: function () {
                //
            },
        }).then(function (result) {
            if (result.value) {

                let subscriptionId = $('#chargebee-subscription-id').val();
                const clientId = document.getElementById('client-id').dataset.clientId;

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
                    url: url,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        chargebee_subscription_id: subscriptionId,
                        client_id:clientId
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
});
