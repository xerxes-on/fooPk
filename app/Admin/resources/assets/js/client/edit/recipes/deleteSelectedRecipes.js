import {$tableRecipes, selectedRecipesStorage} from './recipesConst.js';
import {renderCounterToolbarData} from './renderCounterToolbarData.js';

export function toggleSelect(element) {
    const status = $(element).prop('checked');
    const elements = $('.js-delete-recipes');

    if (elements.length === 0) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noItem,
        });
        $('#delete-all-selected-recipes').hide();
        return;
    }

    if (status) {
        $('#delete-all-selected-recipes').show();
    } else {
        $('#delete-all-selected-recipes').hide();
    }

    let alreadySelected = JSON.parse(localStorage.getItem(selectedRecipesStorage));
    if (!alreadySelected) {
        alreadySelected = {selected: []};
    }

    elements.each(function () {
        if (status === true) {
            // Add if not present
            if (!alreadySelected.selected.includes(this.dataset.id)) {
                alreadySelected.selected.push(this.dataset.id);
            }
        } else {
            // Remove
            alreadySelected.selected = alreadySelected.selected.filter((value) => {
                return value !== this.dataset.id;
            });
        }
        $(this).prop('checked', status);
    });

    // Deduplicate just in case
    alreadySelected.selected = alreadySelected.selected.filter((value, index, array) => {
        return array.indexOf(value) === index;
    });
    localStorage.setItem(selectedRecipesStorage, JSON.stringify(alreadySelected));
}

export function deleteSelectedRecipes() {
    const data = JSON.parse(localStorage.getItem(selectedRecipesStorage));
    if (!data?.selected?.length) {
        Swal.fire({
            icon: 'error',
            title: window.FoodPunk.i18n.noItemSelected,
        });
        return;
    }

    // Prompt user for confirmation
    Swal.fire({
        title: window.FoodPunk.i18n.confirmation,
        text: window.FoodPunk.i18n.revertWarning,
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: window.FoodPunk.i18n.defaultsExist,
        cancelButtonText: window.FoodPunk.i18n.defaultsMissing,
    }).then((result) => {
        if (!result.value) return;

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

        $.ajax({
            type: 'POST',
            url: window.FoodPunk.route.deleteSelectedRecipes,
            dataType: 'json',
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                _method: 'DELETE',
                userId: window.FoodPunk.pageInfo.clientId,
                recipes: data.selected,
            },
            success: function (result) {
                $tableRecipes.ajax.reload();
                renderCounterToolbarData();
                Swal.hideLoading();
                Swal.fire({
                    title: window.FoodPunk.i18n.deleted,
                    html: result.message,
                    icon: result.status,
                });

                localStorage.removeItem(selectedRecipesStorage);
                $('#delete-all-selected-recipes').hide();
            },
            error: function (result) {
                Swal.hideLoading();
                Swal.fire({
                    title: window.FoodPunk.i18n.error,
                    html:
                        result.responseJSON?.message ||
                        window.FoodPunk.i18n.somethingWentWrong,
                    icon: 'error',
                });
            },
        });
    });
}


