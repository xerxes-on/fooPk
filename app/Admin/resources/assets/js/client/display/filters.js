export function getFormData($form) {
    const unindexedArray = $form.serializeArray();
    const indexedObject = {};

    $.map(unindexedArray, function (item) {
        if (!item.value) return null;
        indexedObject[item.name] = item.value;
    });

    return indexedObject;
}

export function initFilters(userDataTable) {
    // "Apply" filter
    $('#apply-filter').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.target.blur();

        userDataTable.draw();
    });

    // "Reset" filter
    $('#reset-filter').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.target.blur();

        $('#user-filter')[0].reset();
        userDataTable.draw();
    });
}
