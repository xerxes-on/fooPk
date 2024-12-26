import {selectedPopupRecipesStorage} from './recipesConst.js';

export function addRecipes() {
    $.colorbox({
        inline: true,
        width: '95%',
        top: 0,
        maxHeight: '98%',
        href: '#allRecipes-popup-wrapper',
        scrolling: true,
        onComplete: function () {
            if ($.fn.DataTable.isDataTable('#allRecipes-popup')) {
                return;
            }

            // Initialize the $tablePopup DataTable
            const tableInstance = $('#allRecipes-popup').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                select: {
                    style: 'multi',
                    info: false
                },
                searchDelay: 450,
                scrollY: '400px',
                pagingType: 'input',
                order: [[0, 'asc']],
                rowId: 'id',
                ajax: {
                    url: window.FoodPunk.route.datatableAsync,
                    data: function (d) {
                        d.method = 'allRecipes';
                        d.userId = window.FoodPunk.pageInfo.clientId;
                        d.filters = {
                            ingestion: $('#recipeIngestionFilter').val(),
                            diet: $('#recipeDietFilter').val(),
                            complexity: $('#recipeComplexityFilter').val(),
                            cost: $('#recipeCostFilter').val(),
                            tag: $('#recipeTagFilter').val(),
                        };
                    },
                },
                drawCallback: function () {
                    setTimeout(function () {
                        $('#allRecipes-popup-wrapper').colorbox.resize();
                    }, 5);
                },
                columns: [
                    {orderable: true, paging: true, data: 'id', width: '5%'},
                    {data: 'title', orderable: false, width: '20%'},
                    {data: 'ingestions', orderable: false, width: '10%'},
                    {data: 'diets', orderable: false, width: '20%'},
                    {data: 'complexity', orderable: false, width: '8%'},
                    {data: 'price', orderable: false, width: '5%'},
                    {data: 'public_tags', orderable: false, width: '20%'},
                    {data: 'status', orderable: false, width: '5%'},
                ],
            });

            // Keep reference to the newly created table in our exported variable
            // so we can reference it outside of this function if needed.
            $.extend(true, window, {FoodPunk: {...window.FoodPunk, $tablePopup: tableInstance}});

            // Bind events for select/deselect
            tableInstance
                .on('draw', function () {
                    const alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
                    if (!alreadySelected || !alreadySelected.selected?.length) {
                        return;
                    }
                    tableInstance.rows().every(function () {
                        let rowData = this.data();
                        if (alreadySelected.selected.includes(rowData.id)) {
                            this.select();
                        }
                    });
                })
                .on('select', function (e, dt, type, indexes) {
                    let rowData = tableInstance.rows(indexes).data();
                    let alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage)) || {selected: []};

                    $.each(rowData, function (index, row) {
                        if (!alreadySelected.selected.includes(row.id)) {
                            alreadySelected.selected.push(row.id);
                        }
                    });
                    localStorage.setItem(selectedPopupRecipesStorage, JSON.stringify(alreadySelected));
                })
                .on('deselect', function (e, dt, type, indexes) {
                    let rowData = tableInstance.rows(indexes).data();
                    let alreadySelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));
                    if (!alreadySelected) return;

                    $.each(rowData, function (index, row) {
                        const location = alreadySelected.selected.indexOf(row.id);
                        if (location !== -1) {
                            alreadySelected.selected.splice(location, 1);
                        }
                    });

                    localStorage.setItem(selectedPopupRecipesStorage, JSON.stringify(alreadySelected));
                });

            // Trigger filter
            $('#js-dt-filter').click(function () {
                tableInstance.ajax.reload();
            });
        },
    });
}
