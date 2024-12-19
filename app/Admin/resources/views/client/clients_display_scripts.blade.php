@push('footer-scripts')
    @php
        use App\Enums\Admin\Permission\PermissionEnum;
    @endphp
    <script>
        jQuery(document).ready(function ($) {
            $(document).on('click change', 'input[type="radio"].random_recipe_distribution_type', function (el) {
                let randomization_type = $(this).val();
                $(document).find('.randomization_type_input').attr('disabled', 'disabled');
                $(document).find('.randomization_type_' + randomization_type).removeAttr('disabled');
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

        function addRecipes2selectUsers() {
            let usersSelected = JSON.parse(localStorage.getItem(selectedUsersStorage));

            // check selected rows
            if (usersSelected === null || usersSelected.selected.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: "{{ __('admin.messages.no_user_selected')}}",
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
                            url: "{{ route('admin.datatable.async')}}",
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
                    }).on('draw', function (e, settings) {
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
    </script>
@endpush