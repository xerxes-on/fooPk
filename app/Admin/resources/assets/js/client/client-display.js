let $tablePopup, $tableUsers;
const selectedPopupRecipesStorage = 'selected_popup_recipes';
const selectedUsersStorage = 'selected_users';
let $SubmitAddRecipes;
if (window.FoodPunk.static.hideRecipesRandomizer) {
    $SubmitAddRecipes = Ladda.create(document.querySelector('#submit-add-recipes'));
}
localStorage.removeItem(selectedPopupRecipesStorage);
jQuery(document).ready(function ($) {
    $(document).on('click change', 'input[type="radio"].random_recipe_distribution_type', function () {
        let randomization_type = $(this).val();
        $(document).find('.randomization_type_input').attr('disabled', 'disabled');
        $(document).find('.randomization_type_' + randomization_type).removeAttr('disabled');
    });

    $tableUsers = $('#table-users')
        .DataTable({
            searching: false,
            lengthChange: false,
            processing: true,
            serverSide: true,
            deferRender: true,
            pageLength: 20,
            paging: {
                type: 'input',
                buttons: 10,
            },
            select: {
                style: 'multi',
                selector: 'td:not(:last-child)',
            },
            order: [[0, 'desc']],
            ajax: {
                url: window.FoodPunk.route.datatableAsync,
                data: function (d) {
                    d.method = 'allUsers';
                    d.filter = getFormData($('#user-filter'));
                },
            },
            layout: {
                topStart: null,
                topEnd: 'info',
                bottomStart: 'info',
                bottomEnd: 'paging'
            },
            columns: [
                {orderable: true, data: 'id'},
                {data: 'first_name'},
                {data: 'last_name'},
                {data: 'email'},
                {data: 'formular_approved', orderable: false},
                {data: 'subscription', orderable: false},
                {data: 'status', orderable: false},
                {data: 'lang'},
                {data: 'created_at'},
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    width: '80px',
                    render: function (data, type, row) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // Get CSRF token

                        let editButton =
                            '<a href="' + window.FoodPunk.static.url + '/admin/users/' + row.id + '/edit" class="btn btn-xs btn-primary" title="Edit" data-toggle="tooltip">' +
                            '<span class="fas fa-pencil-alt"></span>' +
                            '</a>';

                        let deleteButton = '';
                        if (window.FoodPunk.static.canDelete) {
                            deleteButton =
                                '<form action="' + window.FoodPunk.static.url + '/admin/users/' + row.id + '/delete" method="POST" style="display:inline-block;">' +
                                '<input type="hidden" name="_token" value="' + csrfToken + '">' + // Include CSRF token
                                '<input type="hidden" name="_method" value="delete">' +
                                '<button class="btn btn-xs btn-danger btn-delete" title="Delete" data-toggle="tooltip">' +
                                '<i class="fas fa-trash-alt"></i>' +
                                '</button>' +
                                '</form>';
                        }

                        return editButton + deleteButton;
                    },
                },
            ],
        })
        .on('draw', function () {
            $tableUsers.rows().every(function () {
                let rowData = this.data();
                let alreadySelected = JSON.parse(localStorage.getItem(selectedUsersStorage));
                if (alreadySelected === null || alreadySelected.selected.length === 0) {
                    return;
                }
                if (alreadySelected.selected.includes(rowData.id)) {
                    this.select();
                }
            });
        })
        .on('select', function (e, dt, type, indexes) {
            let rowData = $tableUsers.rows(indexes).data();
            let alreadySelected = JSON.parse(localStorage.getItem(selectedUsersStorage))
            if (alreadySelected === null) {
                alreadySelected = {'selected': []};
            }

            // serialize selected rows
            $.each(rowData, function (index, row) {
                if (!alreadySelected.selected.includes(row.id)) {
                    alreadySelected.selected.push(row.id);
                }
            });
            localStorage.setItem(selectedUsersStorage, JSON.stringify(alreadySelected));
        })
        .on('deselect', function (e, dt, type, indexes) {
            let rowData = $tableUsers.rows(indexes).data();
            let alreadySelected = JSON.parse(localStorage.getItem(selectedUsersStorage))
            if (alreadySelected === null) {
                return;
            }
            $.each(rowData, function (index, row) {
                let location = alreadySelected.selected.indexOf(row.id);

                if (location !== -1) {
                    alreadySelected.selected.splice(location, 1);
                }
            });

            localStorage.setItem(selectedUsersStorage, JSON.stringify(alreadySelected));
        });

    $('#apply-filter').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.target.blur(); // button focus disabled

        $tableUsers.draw();
    });

    $('#reset-filter').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.target.blur(); // button focus disabled

        $('#user-filter')[0].reset();
        $tableUsers.draw();
    });

});

function getFormData($form) {
    let unIndexed_array = $form.serializeArray(),
        indexed_array = {};

    $.map(unIndexed_array, function (item) {
        if (!item.value) return null;
        indexed_array[item.name] = item.value;
    });

    return indexed_array;
}
const inputRecipeAmount = async function () {
    const {value: formValues} = await Swal.fire({
        title: window.FoodPunk.i18n.randomizeRecipesSettings,
        icon: 'question',
        html: `<div id="randomizeRecipeComponent"></div>`,
        willOpen: () => {
            Swal.showLoading();
            $.get(
                window.FoodPunk.route.randomizeRecipeTemplate,
                {}, (payload) => {
                    Swal.hideLoading();
                    Swal.getHtmlContainer().querySelector('#randomizeRecipeComponent').innerHTML = payload;
                });
        },
        preConfirm: () => {
            const container = Swal.getHtmlContainer();
            let seasons = [];
            let items = container.getElementsByClassName('selected_seasons');
            for (let i = 0; i < items.length; i++) {
                let val = items[i].value;
                if (items[i].checked && val.length > 0) {
                    seasons.push(val);
                }
            }
            return {
                amount: container.querySelector('input[name="amount_of_recipes"]').value,
                seasons: seasons,
                distribution_type: container.querySelector('input[name="distribution_type"]:checked').value,
                breakfast_snack: container.querySelector('input[name="breakfast_snack"]').value,
                lunch_dinner: container.querySelector('input[name="lunch_dinner"]').value,
                recipes_tag: container.querySelector('input[name="recipes_tag"]:checked').value,
                distribution_mode: container.querySelector('input[name="distribution_mode"]:checked').value,
            };
        },
    });

    return formValues;
};

window.FoodPunk.functions.addRecipes2selectUsers = function () {
    let usersSelected = JSON.parse(localStorage.getItem(selectedUsersStorage));

    // check selected rows
    if (usersSelected === null || usersSelected.selected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noUserSelected,
        });
        return false;
    }

    $.colorbox({
        inline: true,
        top: '0',
        width: '96%',
        maxHeight: '96%',
        href: '#allRecipes-popup-wrapper',
        scrolling: false,
        onComplete: function () {
            if ($.fn.DataTable.isDataTable('#allRecipes-popup')) {
                return;
            }
            // TODO: only on click
            $tablePopup = $('#allRecipes-popup').DataTable({
                lengthChange: true,
                autoWidth: false,
                processing: true,
                serverSide: true,
                searchDelay: 450,
                select: {
                    style: 'multi'
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
            }).on('draw', function () {
                $tablePopup.rows().every(function () {
                    let rowData = this.data();
                    let alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
                    if (alreadySelected === null || alreadySelected.selected.length === 0) {
                        return;
                    }
                    if (alreadySelected.selected.includes(rowData.id)) {
                        this.select();
                    }
                });
            })
                .on('select', function (e, dt, type, indexes) {
                    let rowData = $tablePopup.rows(indexes).data();
                    let alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage))
                    if (alreadySelected === null) {
                        alreadySelected = {'selected': []};
                    }

                    // serialize selected rows
                    $.each(rowData, function (index, row) {
                        if (!alreadySelected.selected.includes(row.id)) {
                            alreadySelected.selected.push(row.id);
                        }
                    });
                    localStorage.setItem(selectedPopupRecipesStorage, JSON.stringify(alreadySelected));
                })
                .on('deselect', function (e, dt, type, indexes) {
                    let rowData = $tablePopup.rows(indexes).data();
                    let alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage))
                    if (alreadySelected === null) {
                        return;
                    }
                    $.each(rowData, function (index, row) {
                        let location = alreadySelected.selected.indexOf(row.id);

                        if (location !== -1) {
                            alreadySelected.selected.splice(location, 1);
                        }
                    });

                    localStorage.setItem(selectedPopupRecipesStorage, JSON.stringify(alreadySelected));
                });
        }
    });

}
window.FoodPunk.functions.addRandomizeRecipes2selectUsers = async function () {
    let usersSelected = $tableUsers.rows({selected: true}).data();
    let userIds = [];

    // check selected rows
    if (usersSelected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noUserSelected,
        });
        return false;
    }

    // get recipe amount
    const initFormData = await inputRecipeAmount();

    if (initFormData === undefined) return false;

    let amount = initFormData.amount;
    let seasons = initFormData.seasons;
    let distribution_type = initFormData.distribution_type;
    let breakfast_snack = initFormData.breakfast_snack;
    let lunch_dinner = initFormData.lunch_dinner;
    let recipes_tag = initFormData.recipes_tag;
    let distribution_mode = initFormData.distribution_mode;

    // check amount
    if (amount === undefined || amount === 0) return false;

    // serialize selected users
    $.each(usersSelected, function (index, row) {
        userIds.push(row.id);
    });

    $.ajax({
        type: 'POST',
        url: window.FoodPunk.route.addToUserRandom,
        dataType: 'json',
        data: {
            _token: $('meta[name=csrf-token]').attr('content'),
            userIds: userIds,
            amount: amount,
            seasons: seasons,
            distribution_type: distribution_type,
            breakfast_snack: breakfast_snack,
            lunch_dinner: lunch_dinner,
            recipes_tag: recipes_tag,
            distribution_mode: distribution_mode,
        },
        beforeSend: function () {
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
        },
        success: function (data) {
            if (data.success === true) {
                Swal.hideLoading();
                Swal.fire({
                    icon: 'success',
                    title: window.FoodPunk.i18n.saved,
                    html: data.message,
                });
            } else {
                Swal.hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: data.message,
                });
            }
            $tableUsers.rows({selected: true}).data();
        },
        error: function (jqXHR) {
            Swal.hideLoading();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: jqXHR.responseJSON.message,
            });
            console.error(jqXHR);
        },
    });
};


window.FoodPunk.functions.submitAdding = function () {
    let rowsSelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
    let usersSelected = JSON.parse(localStorage.getItem(selectedUsersStorage));

    // check selected rows
    if (rowsSelected === null || rowsSelected.selected.length === 0) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noItemSelected,
        });
        return false;
    }

    // check selected rows
    if (usersSelected === null || usersSelected.selected.length === 0) {
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
            _token: $('meta[name=csrf-token]').attr('content'),
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
            if (window.FoodPunk.static.hideRecipesRandomizer) {
                $SubmitAddRecipes.start();
            }
        },
        success: function (data) {
            if (data.success === true) {
                Swal.hideLoading();
                Swal.fire({
                    icon: 'success',
                    title: window.FoodPunk.i18n.saved,
                    html: data.message,
                });
            } else {
                Swal.hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: data.message,
                });
            }
            // refresh DataTable
            if (window.FoodPunk.static.hideRecipesRandomizer) {
                $SubmitAddRecipes.stop();
            }
            localStorage.removeItem(selectedPopupRecipesStorage);
            localStorage.removeItem(selectedUsersStorage);
        },
        error: function () {
            Swal.hideLoading();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: jqXHR.responseJSON.message,
            });
            console.error(jqXHR);
            $SubmitAddRecipes.stop();
        },
    });
}

