jQuery(document).ready(function ($) {
    var time = 30;
    setInterval(function () {
        $('#js-check-refresh').html(--time);
    }, 1000);
    setInterval(function () {
        checkCalculationStatus();
        time = 30;
    }, 30000);
});

const clientId = document.getElementById('client-data').dataset.clientId;
function checkCalculationStatus() {
    $.ajax({
        type: 'GET',
        url: ' /admin/recipes/check-job-status?userId='. clientId,
        dataType: 'json',
        data: {
            _token: $('meta[name=csrf-token]').attr('content'),
        }, beforeSend: function () {
            $('#calculation-status').html('<span class="fa fa-spinner fa-spin" aria-hidden="true"></span>');
        },
        success: function (data) {
            if (data.success === true) {
                $('#calculation-status').removeAttr('class').addClass(`alert alert-${data.status}`).html(data.message).show();
            } else {
                $('#calculation-status').hide();
            }
        },
        error: function (data) {
            console.error(data);
        },
    });
}