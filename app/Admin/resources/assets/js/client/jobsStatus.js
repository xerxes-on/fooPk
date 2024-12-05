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

function checkCalculationStatus() {
    $.ajax({
        type: 'GET',
        url: "{{ route('admin.recipes.check-calculation-status', ['userId' => $client->id]) }}",
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