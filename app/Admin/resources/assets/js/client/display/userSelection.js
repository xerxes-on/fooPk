import {getFormData} from './filters';

const selectedUsersStorage = 'selected_users';
let $tableUsers = null;

/**
 * Initializes the user selection DataTable with server-side processing and multi-row selection.
 *
 * @function initUserSelection
 * @returns {DataTable} The initialized DataTable instance for user selection.
 */
export function initUserSelection() {
    localStorage.removeItem(selectedUsersStorage);

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
                            '<a href="' + window.FoodPunk.pageInfo.url + '/admin/users/' + row.id + '/edit" class="btn btn-xs btn-primary" title="Edit" data-toggle="tooltip">' +
                            '<span class="fas fa-pencil-alt"></span>' +
                            '</a>';

                        let deleteButton = '';
                        if (window.FoodPunk.pageInfo.canDelete) {
                            deleteButton =
                                '<form action="' + window.FoodPunk.pageInfo.url + '/admin/users/' + row.id + '/delete" method="POST" style="display:inline-block;">' +
                                '<input type="hidden" name="_token" value="' + csrfToken + '">' + // Include CSRF token
                                '<input type="hidden" name="_method" value="delete">' +
                                '<button class="btn btn-xs btn-danger btn-delete" title="Delete" data-toggle="tooltip">' +
                                '<i class="fas fa-trash-alt"></i>' +
                                '</button>' +
                                '</form>';
                        }

                        return editButton + ' ' + deleteButton;
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

    return $tableUsers;
}

export function getUserTable() {
    return $tableUsers;
}
