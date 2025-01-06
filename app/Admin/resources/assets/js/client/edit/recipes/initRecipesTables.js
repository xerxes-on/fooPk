import {selectedRecipesStorage} from './recipesConst';
import {renderCounterToolbarData} from './renderCounterToolbarData';

/**
 * Initializes DataTables for "Recipes By Challenge" and "Recipes By User" tabs.
 *
 * @function initRecipesTables
 *
 * @description
 * - Binds DataTable initialization to the activation of corresponding tabs.
 * - Configures "Recipes By Challenge" DataTable with server-side processing and challenge-specific data.
 * - Configures "Recipes By User" DataTable with server-side processing, user-specific data, and toolbar features.
 * - Manages state persistence for selected recipes and updates the UI accordingly.
 * - Provides functionality for deleting individual recipes and accessing detailed recipe information.
 */

export function initRecipesTables() {
    // Recipes in Weekly plan tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $($(e.target).attr('href')).find('#recipesByChallenge');

        if (target.length && !$.fn.DataTable.isDataTable(target)) {
            // Initialize challenge-based DataTable
            const tableInstance = $('#recipesByChallenge').DataTable({
                lengthChange: false,
                processing: true,
                serverSide: true,
                pageLength: 12,
                searchDelay: 450,
                paging: {
                    type: 'input',
                    buttons: 10,
                },
                ajax: {
                    url: window.FoodPunk.route.datatableAsync,
                    data: function (d) {
                        d.method = 'recipesByUserFromActiveChallenge';
                        d.userId = window.FoodPunk.pageInfo.clientId;
                    },
                },
                order: [[4, 'asc']],
                columns: [
                    {data: 'id', width: '5%'},
                    {data: '_image', orderable: false},
                    {data: 'title', width: '30%', orderable: false},
                    {data: 'challenge_title', width: '10%'},
                    {data: 'meal_date', width: '8%'},
                    {data: 'meal_time', width: '8%'},
                    {data: 'invalid', orderable: false, width: '5%'},
                    {data: '_kcal', orderable: false, width: '8%'},
                    {data: '_kh', orderable: false, width: '8%'},
                    {data: 'calculated', orderable: false, width: '10%'},
                    {data: '_diets', width: '25%', orderable: false},
                ],
            });

            // Keep reference if you need it globally
            $.extend(true, window, {FoodPunk: {...window.FoodPunk, $recipesByChallenge: tableInstance}});
        }
    });

    // User Recipes in Recipes tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $($(e.target).attr('href')).find('#recipesByUser');

        if (target.length && !$.fn.DataTable.isDataTable(target)) {
            const tableInstance = $('#recipesByUser').DataTable({
                lengthChange: true,
                processing: true,
                serverSide: true,
                deferRender: true,
                searchDelay: 450,
                autoWidth: false,
                paging: {
                    type: 'input',
                    buttons: 10,
                },
                layout: {
                    bottom2Start: function () {
                        let toolbar = document.createElement('div');
                        toolbar.id = 'counterToolbar';
                        toolbar.innerHTML = '<span aria-hidden="true" class="fa fa-spinner fa-spin"></span>';
                        return toolbar;
                    },
                },
                ajax: {
                    url: window.FoodPunk.route.datatableAsync,
                    data: function (d) {
                        d.method = 'recipesByUser';
                        d.userId = window.FoodPunk.pageInfo.clientId;
                    },
                },
                order: [[1, 'desc']],
                columns: [
                    {
                        orderable: false,
                        data: null,
                        className: 'text-center',
                        width: '3%',
                        render: function (data, type, row) {
                            return `<input type="checkbox" class="js-delete-recipes" data-id="${row.id}">`;
                        },
                    },
                    {data: 'id', width: '5%', orderable: true},
                    {data: '_image', orderable: false, width: '5%'},
                    {data: 'title', orderable: false, width: '15%'},
                    {data: '_cooking_time', width: '8%'},
                    {data: '_complexity', width: '5%'},
                    {data: '_mealTime', width: '10%'},
                    {
                        data: 'invalid',
                        width: '10%',
                        className: 'text-center',
                        orderSequence: ['desc', 'asc']
                    },
                    {data: 'calculated', width: '10%'},
                    {data: '_diets', width: '10%', orderable: false},
                    {data: 'status', width: '5%', orderSequence: ['desc', 'asc']},
                    {
                        data: 'favorite',
                        width: '5%',
                        className: 'text-center',
                        orderSequence: ['desc', 'asc']
                    },
                    {
                        data: null,
                        className: 'text-center',
                        width: '7%',
                        orderable: false,
                        render: function (data, type, row) {
                            return `<button type="button" class="btn btn-xs btn-secondary" onclick="window.FoodPunk.functions.openInfoModal(this, ${row.id})"> ${window.FoodPunk.i18n.notificationInfoModalButton} </button>`;
                        },
                    },
                    {
                        data: null,
                        className: 'text-center',
                        width: '3%',
                        orderable: false,
                        render: function (data, type, row) {
                            if (!window.FoodPunk.pageInfo.canDeleteAllUserRecipes) return '';
                            return `
                <button 
                  type="button" 
                  class="btn btn-xs btn-danger" 
                  title="Delete" 
                  data-id="${row.id}"
                  onclick="window.FoodPunk.functions.deleteRecipe(this)">
                  <span class="fa fa-trash" aria-hidden="true"></span>
                </button>`;
                        },
                    },
                ],
            });

            // Keep reference if you need it
            $.extend(true, window, {FoodPunk: {...window.FoodPunk, $tableRecipes: tableInstance}});

            // Re-check selected items after data load
            tableInstance
                .on('xhr.dt', function (e, settings, json, xhr) {
                    let alreadySelected = localStorage.getItem(selectedRecipesStorage);
                    if (!alreadySelected) {
                        localStorage.setItem(selectedRecipesStorage, JSON.stringify({selected: []}));
                        return;
                    }
                    alreadySelected = JSON.parse(alreadySelected);
                    if (alreadySelected.selected.length > 0) {
                        setTimeout(function () {
                            alreadySelected.selected.forEach(function (recipeId) {
                                $(`.js-delete-recipes[data-id="${recipeId}"]`).prop('checked', true);
                            });
                        }, 10);
                    }
                })
                .on('init', function () {
                    renderCounterToolbarData();
                });
        }
    });
}
