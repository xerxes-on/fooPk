export let pageElements = {
    dataTableClass: '.card.card-default',
    dataTableLoaderId: '#DataTables_Table_0_processing',
    searchResultId: '#seach_result',
    searchResultWrapperId: '#search_result_wrapper',
    clearButtonId: '#clear_search',
};

export let searchRequest = (params, url, verb = 'POST') => {
    // /admin/recipes and /admin/ingredients
    if (Object.values(params).join('') === 'all') {
        clearFilters();
        return;
    }

    let loadingSpinner = $('.search-request-spinner');
    loadingSpinner.show();
    $.ajax({
        type: verb,
        url: url,
        data: {
            _token: $('meta[name=csrf-token]').attr('content'),
            ...params,
        },
        success: function (resp) {

            $(pageElements.searchResultId).remove();
            $(pageElements.searchResultWrapperId).append(resp);
            $(pageElements.clearButtonId).show();

            $(pageElements.dataTableClass).hide();
        },
        error: function (err) {
            console.log(err);
        },
        complete() {
            loadingSpinner.hide();
        },
    });
};

export let triggerEvent = (element, keyCode, button) => {
    element.keydown(function (event) {
        if (event.keyCode === keyCode) {
            button.trigger('click');
        }
    });
};

export function clearFilters(filters) {
    Object.values(filters).forEach(filter => filter.val(''));
    $(pageElements.searchResultId).remove();
    $(pageElements.dataTableClass).show();
    $(pageElements.dataTableLoaderId).hide();
    $(pageElements.clearButtonId).hide();
}
