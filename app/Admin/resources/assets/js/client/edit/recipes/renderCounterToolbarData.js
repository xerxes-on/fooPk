/**
 * Updates the counter toolbar with user-specific recipe data.
 *
 * @function renderCounterToolbarData
 *
 * @description
 * - Sends a GET request to retrieve recipe count data for the current user.
 * - Updates the `#counterToolbar` element with the retrieved data on success.
 * - Displays an error message in the toolbar if the request fails.
 *
 * @returns {void}
 */

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
