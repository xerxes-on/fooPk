export function initCalculationStatusCheck() {

    if ($('#js-check-refresh').length === 0) {
        return;
    }

    let time = 30;
    setInterval(() => {
        $('#js-check-refresh').html(--time);
    }, 1000);

    setInterval(() => {
        checkCalculationStatus();
        time = 30;
    }, 30000);

    checkCalculationStatus();

    function checkCalculationStatus() {
        $.ajax({
            type: 'GET',
            url: window.FoodPunk.route.checkCalculationStatus,
            dataType: 'json',
            beforeSend: function () {
                $('#calculation-status').html('<span class="fa fa-spinner fa-spin" aria-hidden="true"></span>');
            },
            success: function (data) {
                if (data.success) {
                    $('#calculation-status')
                        .removeAttr('class')
                        .addClass(`alert alert-${data.status}`)
                        .html(data.message)
                        .show();
                } else {
                    $('#calculation-status').hide();
                }
            },
            error: function (data) {
                console.error(data);
            },
        });
    }
}
