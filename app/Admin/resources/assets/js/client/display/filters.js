/**
 * Serializes form data into an object for use with DataTables filtering.
 *
 * @function getFormData
 * @param {jQuery} $form - A jQuery object representing the form to serialize.
 * @returns {Object} An object containing the form's data where keys are input names and values are their respective values.
 */
export function getFormData($form) {
    const unindexedArray = $form.serializeArray();
    const indexedObject = {};

    $.map(unindexedArray, function (item) {
        if (!item.value) return null;
        indexedObject[item.name] = item.value;
    });

    return indexedObject;
}

/**
 * Initializes the filter functionality for the user DataTable. Binds actions to "Apply" and "Reset" buttons.
 *
 * @function initFilters
 * @param {Object} userDataTable - A DataTable instance for which the filters are applied.
 *
 * @description
 * - The "Apply" button triggers the table redraw with the current filter criteria.
 * - The "Reset" button clears the form and redraws the table with no filters applied.

 */
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
