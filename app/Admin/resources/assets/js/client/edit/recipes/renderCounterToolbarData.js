// Fetches and updates recipe count in the #counterToolbar using server data
export function renderCounterToolbarData() {
    $.ajax({
        type: 'GET',
        url: window.FoodPunk.route.recipesCountData,
        dataType: 'json',
        data: {
            _token: $('meta[name=csrf-token]').attr('content'),
            userId: window.FoodPunk.pageInfo.clientId,
        },
        success: function (data) {
            $('#counterToolbar').html(data.success ? data.message : '');
        },
        error: function (jqXHR) {
            $('#counterToolbar').html(`Error: <b>${jqXHR.responseJSON.message}</b>`);
        },
    });
}
