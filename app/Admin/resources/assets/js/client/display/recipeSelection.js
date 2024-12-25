const selectedPopupRecipesStorage = 'selected_popup_recipes';
const selectedUsersStorage = 'selected_users';
let $tablePopup = null;

/**
 * Opens a Colorbox popup that shows all recipes in a DataTable, and
 * allows selecting/deselecting them, storing in localStorage.
 */
export function addRecipes2selectUsers() {
    const usersSelected = JSON.parse(localStorage.getItem(selectedUsersStorage));

    // Check that at least one user is selected
    if (!usersSelected || usersSelected.selected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noUserSelected,
        });
        return;
    }

    $.colorbox({
        inline: true,
        top: '0',
        width: '96%',
        maxHeight: '96%',
        href: '#allRecipes-popup-wrapper',
        scrolling: false,
        onComplete: function () {
            // If the DataTable is already initialized, don’t re-init
            if ($.fn.DataTable.isDataTable('#allRecipes-popup')) {
                return;
            }

            // Otherwise, create the DataTable for the popup
            $tablePopup = $('#allRecipes-popup').DataTable({
                lengthChange: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                searchDelay: 450,
                select: {
                    style: 'multi',
                },
                paging: {
                    type: 'input',
                    buttons: 10,
                },
                order: [[0, 'asc']],
                ajax: {
                    url: window.FoodPunk.route.datatableAsync,
                    data: function (d) {
                        d.method = 'allRecipes';
                    },
                },
                drawCallback: function () {
                    setTimeout(function () {
                        $('#allRecipes-popup-wrapper').colorbox.resize();
                    }, 5);
                },
                columns: [
                    {
                        searchable: false,
                        data: 'id',
                        width: '5%',
                    },
                    {
                        data: 'title',
                        width: '30%',
                        orderable: false,
                    },
                    {
                        data: 'ingestions',
                        width: '15%',
                        orderable: false,
                    },
                    {
                        data: 'diets',
                        width: '30%',
                        orderable: false,
                    },
                    {
                        data: 'status',
                        width: '5%',
                    },
                ],
            })
                .on('draw', function () {
                    // Re-select previously chosen recipes
                    const alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
                    if (!alreadySelected || alreadySelected.selected.length === 0) return;

                    $tablePopup.rows().every(function () {
                        const rowData = this.data();
                        if (alreadySelected.selected.includes(rowData.id)) {
                            this.select();
                        }
                    });
                })
                .on('select', function (e, dt, type, indexes) {
                    let alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
                    if (!alreadySelected) {
                        alreadySelected = { selected: [] };
                    }

                    const rowData = $tablePopup.rows(indexes).data();
                    $.each(rowData, function (_, row) {
                        if (!alreadySelected.selected.includes(row.id)) {
                            alreadySelected.selected.push(row.id);
                        }
                    });
                    localStorage.setItem(selectedPopupRecipesStorage, JSON.stringify(alreadySelected));
                })
                .on('deselect', function (e, dt, type, indexes) {
                    let alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
                    if (!alreadySelected) return;

                    const rowData = $tablePopup.rows(indexes).data();
                    $.each(rowData, function (_, row) {
                        const location = alreadySelected.selected.indexOf(row.id);
                        if (location !== -1) {
                            alreadySelected.selected.splice(location, 1);
                        }
                    });
                    localStorage.setItem(selectedPopupRecipesStorage, JSON.stringify(alreadySelected));
                });
        },
    });
}

/**
 * Submits the selected recipes to the selected users via AJAX.
 * If `window.FoodPunk.pageInfo.hideRecipesRandomizer` is true, starts/stops the Ladda button.
 */
export function submitAdding() {
    const selectedPopupRecipesStorage = 'selected_popup_recipes';
    const usersStorage = 'selected_users';

    let rowsSelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
    let usersSelected = JSON.parse(localStorage.getItem(usersStorage));

    // Check if any recipes were selected
    if (!rowsSelected || rowsSelected.selected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noItemSelected,
        });
        return false;
    }

    // Check if any users were selected
    if (!usersSelected || usersSelected.selected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noUserSelected,
        });
        return false;
    }

    $.ajax({
        type: 'POST',
        url: window.FoodPunk.route.addToUser,
        dataType: 'json',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            userIds: usersSelected.selected,
            recipeIds: rowsSelected.selected,
        },
        beforeSend: function () {
            $.colorbox.close();
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
            if (window.FoodPunk.pageInfo.hideRecipesRandomizer && window.$SubmitAddRecipes) {
                window.$SubmitAddRecipes.start();
            }
        },
        success: function (data) {
            Swal.hideLoading();
            if (data.success === true) {
                Swal.fire({
                    icon: 'success',
                    title: window.FoodPunk.i18n.saved,
                    html: data.message,
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: data.message,
                });
            }
            if (window.FoodPunk.pageInfo.hideRecipesRandomizer && window.$SubmitAddRecipes) {
                window.$SubmitAddRecipes.stop();
            }
            localStorage.removeItem(selectedPopupRecipesStorage);
            localStorage.removeItem(usersStorage);
        },
        error: function (jqXHR) {
            Swal.hideLoading();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: jqXHR.responseJSON?.message || 'Request Failed',
            });
            console.error(jqXHR);
            if (window.FoodPunk.pageInfo.hideRecipesRandomizer && window.$SubmitAddRecipes) {
                window.$SubmitAddRecipes.stop();
            }
        },
    });
}
