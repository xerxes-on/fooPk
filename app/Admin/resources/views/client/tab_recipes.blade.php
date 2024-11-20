@php
    use App\Enums\Admin\Permission\PermissionEnum;use App\Models\Admin;
    $canDeleteAllUserRecipes = $user->can(PermissionEnum::DELETE_ALL_USER_RECIPES->value);
@endphp

<div class="text-left" style="margin-bottom: 10px">
    @can(PermissionEnum::ADD_RECIPES_TO_CLIENT->value, Admin::class)
        <button type="button" id="add-recipes" class="btn btn-info ladda-button" data-style="expand-right"
                onclick="addRecipes()">
            <span class="ladda-label">+ @lang('common.add_recipe')</span>
        </button>

        <button type="button" id="add-randomize-recipes-to-select-users" class="btn btn-info"
                onclick="addRandomizeRecipes()">
            <i class="fas fa-plus" aria-hidden="true"></i> @lang('admin.buttons.add_random_recipes')
        </button>
    @endif

    <button type="button" id="recalculate-user-recipes" class="btn btn-info ladda-button" data-style="expand-right"
            onclick="recalculateUserRecipes()">
        <span class="ladda-label">@lang('common.recalculate')</span>
    </button>

    @if($canDeleteAllUserRecipes)
        <button type="button" id="delete-all-selected-recipes" class="btn btn-danger" style="display: none"
                onclick="deleteSelectedRecipes()">
            <span class="fa fa-info-circle" aria-hidden="true"></span>
            <span class="ladda-label">@lang('common.delete_selected')</span>
        </button>
        <button type="button" id="delete-all-user-recipes" class="btn btn-danger" onclick="deleteAllRecipes()">
            <span class="ladda-label">@lang('common.delete_all_recipe')</span>
        </button>
    @endif
</div>

<table id="recipesByUser" class="table table-striped table-bordered" style="width:100%">
    <thead>
    <tr>
        <th><label><input type="checkbox" readonly onclick="toggleSelect(this)"></label></th>
        <th>#</th>
        <th>@lang('common.image')</th>
        <th>@lang('common.title')</th>
        <th>@lang('common.cooking_time')</th>
        <th>@lang('common.complexity')</th>
        <th>@lang('common.meal')</th>
        <th>@lang('common.invalid')</th>
        <th>@lang('common.recipe_calculated')</th>
        <th>@lang('common.diets')</th>
        <th>@lang('common.status')</th>
        <th>@lang('common.favorite')</th>
        <th></th>
        <th></th>
    </tr>
    </thead>
</table>

<div class="modal fade" id="recipeDetailsModal" aria-describedby="recipeDetailsTitle" tabindex="-1" role="dialog"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recipeDetailsTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

{{--Search modal for recipes--}}
<div style="display:none">
    <div id="allRecipes-popup-wrapper" style="padding:10px; background:#fff;">
        <table id="allRecipes-popup" class="table table-striped table-bordered" style="width:100%">
            <thead>
            <tr>
                <th colspan="2">@lang('common.filters')</th>
                <td>
                    <select name="ingestionFilter" id="recipeIngestionFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($ingestions as $ingestion)
                            <option value="{{$ingestion->id}}">{{$ingestion->title}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="dietFilter" id="recipeDietFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($diets as $diet)
                            <option value="{{$diet->id}}">{{$diet->name}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="complexityFilter" id="recipeComplexityFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($complexities as $complexity)
                            <option value="{{$complexity->id}}">{{$complexity->title}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="costFilter" id="recipeCostFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($costs as $cost)
                            <option value="{{$cost->id}}">{{$cost->title}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="tagFilter" id="recipeTagFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($tags as $tag)
                            <option value="{{$tag->id}}">{{$tag->title}}</option>
                        @endforeach
                    </select>
                </td>
                <th>
                    <button class="btn btn-info" id="js-dt-filter">@lang('common.apply')</button>
                </th>
            </tr>
            <tr>
                <th>#</th>
                <th>@lang('common.title')</th>
                <th><label for="recipeIngestionFilter">@lang('common.day_category')</label></th>
                <th><label for="recipeDietFilter">@lang('common.diets')</label></th>
                <th><label for="recipeComplexityFilter">@lang('common.complexity')</label></th>
                <th><label for="recipeCostFilter">@lang('common.cost')</label></th>
                <th><label for="recipeTagFilter">@lang('common.recipe_tags')</label></th>
                <th>@lang('common.status')</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th>#</th>
                <th>@lang('common.title')</th>
                <th><label for="recipeIngestionFilter">@lang('common.day_category')</label></th>
                <th><label for="recipeDietFilter">@lang('common.diets')</label></th>
                <th><label for="recipeComplexityFilter">@lang('common.complexity')</label></th>
                <th><label for="recipeCostFilter">@lang('common.cost')</label></th>
                <th><label for="recipeTagFilter">@lang('common.recipe_tags')</label></th>
                <th>@lang('common.status')</th>
            </tr>

        </table>

        <div class="text-right mt-3">
            <button type="button"
                    id="submit-add-recipes"
                    class="btn btn-info ladda-button"
                    data-style="expand-right"
                    onclick="submitAdding()">
                <span class="ladda-label">@lang('common.submit')</span>
            </button>
        </div>
    </div>
</div>

@push('footer-scripts')
    <script>
        // define global variable
        let $tablePopup, $tableRecipes;
        const $SubmitAddRecipes = Ladda.create(document.querySelector('#submit-add-recipes'));
        const selectedRecipesStorage = 'selected_recipes';
        const selectedPopupRecipesStorage = 'selected_popup_recipes';

        jQuery(document).ready(function ($) {
            localStorage.removeItem(selectedRecipesStorage);
            localStorage.removeItem(selectedPopupRecipesStorage);


            $(document).bind('cbox_open', function () {
                $('html').css({overflow: 'hidden'});
            }).bind('cbox_cleanup', function () {
                $('html').css({overflow: 'auto'});
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                let target = $($(e.target).attr("href")).find('#recipesByUser')
                if (target.length && !$.fn.DataTable.isDataTable(target)) {
                    $tableRecipes = $('#recipesByUser').DataTable({
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
                            }
                        },
                        ajax: {
                            url: '/admin/datatable/async',
                            data: function (d) {
                                d.method = 'recipesByUser';
                                d.userId = '{{ $client->id }}';
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
                                    return '<input type="checkbox" class="js-delete-recipes" data-id="' + row.id + '">';
                                },
                            },
                            {data: 'id', width: '5%', orderable: true},
                            {data: '_image', orderable: false, width: '5%'},
                            {data: 'title', orderable: false, width: '15%'},
                            {data: '_cooking_time', width: '8%'},
                            {data: '_complexity', width: '5%'},
                            {data: '_mealTime', width: '10%'},
                            {data: 'invalid', width: '10%', className: 'text-center', orderSequence: ['desc', 'asc']},
                            {data: 'calculated', width: '10%'},
                            {data: '_diets', width: '10%', orderable: false},
                            {data: 'status', width: '5%', orderSequence: ['desc', 'asc']},
                            {data: 'favorite', width: '5%', className: 'text-center', orderSequence: ['desc', 'asc']},
                            {
                                data: null,
                                className: 'text-center',
                                width: '7%',
                                orderable: false,
                                render: function (data, type, row) {
                                    return '<button type="button" class="btn btn-xs btn-secondary" onclick="openInfoModal(this, ' + row.id + ')">{{trans('PushNotification::admin.notification_info_modal.button')}}</button>';
                                },
                            },
                            {
                                data: null,
                                className: 'text-center',
                                width: '3%',
                                orderable: false,
                                render: function (data, type, row) {
                                    return '{{$canDeleteAllUserRecipes}}' ? '<button type="button" class="btn btn-xs btn-danger" title="Delete" data-id="' + row.id +
                                        '" onclick="deleteRecipe(this)">' +
                                        '<span class="fa fa-trash" aria-hidden="true"></span>' +
                                        '</button>' : '';
                                },
                            },
                        ],
                    });
                    $tableRecipes
                        .on('xhr.dt', function (e, settings, json, xhr) {
                            let alreadySelected = localStorage.getItem(selectedRecipesStorage);
                            if (alreadySelected === null) {
                                localStorage.setItem(selectedRecipesStorage, JSON.stringify({'selected': []}));
                                return;
                            }

                            alreadySelected = JSON.parse(alreadySelected);

                            // switching to new page
                            if (alreadySelected.selected.length > 0) {
                                // At the time of execution new DOM is not ready
                                setTimeout(function () {
                                    alreadySelected.selected.forEach(function (recipeId) {
                                        $('.js-delete-recipes[data-id="' + recipeId + '"]').prop('checked', true);
                                    });
                                }, 10);
                            }
                        })
                        .on('init', function (e, settings, json) {
                            renderCounterToolbarData();
                        });
                }
            });
        });

        function addRecipes() {
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
                    $tablePopup = $('#allRecipes-popup')
                        .DataTable({
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
                                url: '/admin/datatable/async',
                                data: function (d) {
                                    d.method = 'allRecipes';
                                    d.userId = '{{ $client->id }}';
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
                                {
                                    orderable: true,
                                    paging: true,
                                    data: 'id',
                                    width: '5%',
                                },
                                {
                                    data: 'title',
                                    orderable: false,
                                    width: '20%'
                                },
                                {
                                    data: 'ingestions',
                                    orderable: false,
                                    width: '10%'
                                },
                                {
                                    data: 'diets',
                                    orderable: false,
                                    width: '20%'
                                },
                                {
                                    data: 'complexity',
                                    orderable: false,
                                    width: '8%'
                                },
                                {
                                    data: 'price',
                                    orderable: false,
                                    width: '5%'
                                },
                                {
                                    data: 'public_tags',
                                    orderable: false,
                                    width: '20%'
                                },
                                {
                                    data: 'status',
                                    orderable: false,
                                    width: '5%'
                                },
                            ],
                        })
                        .on('draw', function (e, settings) {
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

                    // trigger filter TODO: maybe lock the btn and unlock on filter change?
                    $('#js-dt-filter').click(function () {
                        $tablePopup.ajax.reload();
                    });
                }
            });
        }

        function deleteRecipe(elem) {
            let recipeId = $(elem).attr('data-id');

            Swal.fire({
                title: '{{trans('admin.messages.confirmation')}}',
                text: '{{trans('admin.messages.revert_warning')}}',
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.value) {
                    Swal.fire({
                        title: '{{trans('admin.messages.wait')}}',
                        text: '{{trans('admin.messages.in_progress')}}',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    let route = '{{ route('admin.recipes.delete-by-user', ['recipeId' => '%', 'userId' => $client->id]) }}';
                    route = route.replace('%', recipeId);
                    $.ajax({
                        type: 'DELETE',
                        url: route,
                        dataType: 'json',
                        success: function (result) {
                            $tableRecipes.ajax.reload();
                            renderCounterToolbarData();
                            Swal.hideLoading();

                            if (result.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    html: result.message ? result.message : 'Success',
                                    icon: 'success',
                                });
                                return;
                            }

                            Swal.fire({
                                title: 'Error!',
                                html: result.message ? result.message : 'Something went wrong',
                                icon: 'error',
                            });
                            console.error(result);
                        },
                        error: function (result) {
                            Swal.hideLoading();
                            Swal.fire({
                                title: 'Error!',
                                html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                                icon: 'error',
                            });
                        },
                    });
                }
            });
        }

        function deleteAllRecipes() {
            Swal.fire({
                title: 'Are you sure to delete all recipes for this user?',
                text: '{{trans('admin.messages.revert_warning')}}',
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.value) {
                    Swal.fire({
                        title: '{{trans('admin.messages.wait')}}',
                        text: '{{trans('admin.messages.in_progress')}}',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: 'DELETE',
                        url: "{{ route('admin.recipes.delete-all-recipes', [ 'userId'=> $client->id]) }}",
                        dataType: 'json',
                        success: function (result) {
                            // refresh DataTable
                            $tableRecipes.ajax.reload();
                            $('#counterToolbar').html('');
                            Swal.hideLoading();
                            Swal.fire({
                                title: 'Deleted!',
                                html: result.message,
                                icon: result.success ? 'success' : 'error',
                            });
                        },
                        error: function (result) {
                            Swal.hideLoading();
                            Swal.fire({
                                title: 'Error!',
                                html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                                icon: 'error',
                            });
                        },
                    });
                }
            });
        }

        function toggleSelect(element) {
            const status = $(element).prop('checked');
            const elements = $('.js-delete-recipes');
            if (elements.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No item!',
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
            elements.each(function (index, item) {
                if (status === true) {
                    alreadySelected.selected.push(item.dataset.id);
                } else {
                    alreadySelected.selected = alreadySelected.selected.filter(function (value) {
                        return value !== item.dataset.id;
                    });
                }

                $(this).prop('checked', status);
            });
            alreadySelected.selected = alreadySelected.selected.filter(function (value, index, array) {
                return array.indexOf(value) === index;
            });
            localStorage.setItem(selectedRecipesStorage, JSON.stringify(alreadySelected));
        }

        function deleteSelectedRecipes() {
            const data = JSON.parse(localStorage.getItem(selectedRecipesStorage));
            if (data.selected.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No item selected!',
                });
                return;
            }
            // prompt for confirmation and proceed to delete items
            Swal.fire({
                title: 'Are you sure to delete selected recipes for this user?',
                text: '{{trans('admin.messages.revert_warning')}}',
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (!result.value) {
                    return;
                }
                Swal.fire({
                    title: '{{trans('admin.messages.wait')}}',
                    text: '{{trans('admin.messages.in_progress')}}',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.recipes.delete-selected-recipes') }}",
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        _method: 'DELETE',
                        userId: {{$client->id}},
                        recipes: data.selected,
                    },
                    success: function (result) {
                        // refresh DataTable
                        $tableRecipes.ajax.reload();
                        renderCounterToolbarData();
                        Swal.hideLoading();
                        Swal.fire({
                            title: 'Deleted!',
                            html: result.message,
                            icon: result.status,
                        });

                        localStorage.removeItem(selectedRecipesStorage);
                        $('#delete-all-selected-recipes').hide();
                    },
                    error: function (result) {
                        Swal.hideLoading();
                        Swal.fire({
                            title: 'Error!',
                            html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                            icon: 'error',
                        });
                    },
                });
            });
        }

        function submitAdding() {
            let rowsSelected = JSON.parse(localStorage.getItem(selectedPopupRecipesStorage));

            // check selected rows
            if (rowsSelected === null || rowsSelected.selected.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No item selected!',
                });
                return false;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('admin.recipes.add-to-user') }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    userIds: [{{ $client->id }}],
                    recipeIds: rowsSelected.selected,
                },
                beforeSend: function () {
                    $.colorbox.close();
                    Swal.fire({
                        title: '{{trans('admin.messages.wait')}}',
                        text: '{{trans('admin.messages.in_progress')}}',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                    $SubmitAddRecipes.start();
                },
                success: function (data) {
                    if (data.success === true) {
                        Swal.hideLoading();
                        Swal.fire({
                            icon: 'success',
                            title: '{{trans('admin.messages.saved')}}',
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
                    $tableRecipes.ajax.reload();
                    renderCounterToolbarData();
                    $SubmitAddRecipes.stop();
                    localStorage.removeItem(selectedPopupRecipesStorage);
                },
                error: function (data) {
                    console.error(data);
                    $SubmitAddRecipes.stop();
                },
            });
        }

        function recalculateUserRecipes() {
            Swal.fire({
                title: '{{trans('admin.messages.confirmation')}}',
                text: '{{trans('admin.messages.revert_warning')}}',
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.value) {
                    Swal.fire({
                        title: '{{trans('admin.messages.wait')}}',
                        text: '{{trans('admin.messages.in_progress')}}',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('admin.recipes.recalculate-to-user') }}",
                        dataType: 'json',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            userId: '{{ $client->id }}',
                        },
                        success: function (data) {
                            if (data.success === true) {
                                Swal.hideLoading();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Recalculated!',
                                    text: 'All recipes recalculated.',
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
                            $tableRecipes.ajax.reload();
                        }, error: function (data) {
                            Swal.hideLoading();
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: data.responseJSON.message,
                            });
                            $tableRecipes.ajax.reload();
                        },
                    });
                }
            });
        }

        const inputRecipeAmount = async function () {
            const {value: formValues} = await Swal.fire({
                title: 'Randomize recipes settings',
                icon: 'question',
                html: `<div id="randomizeRecipeComponent"></div>`,
                willOpen: () => {
                    Swal.showLoading();
                    $.get(
                        '{{route('admin.client.randomize-recipe-template')}}',
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

        const addRandomizeRecipes = async function () {
            let userIds = [{{ $client->id }}];

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

            $.ajax({
                type: 'POST',
                url: "{{ route('admin.recipes.add-to-user-random') }}",
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
                        title: '{{trans('admin.messages.wait')}}',
                        text: '{{trans('admin.messages.in_progress')}}',
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
                            title: 'Your work has been saved!',
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

        function renderCounterToolbarData() {
            $.ajax({
                type: 'GET',
                url: '{{route('admin.client.recipes.count-data')}}',
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    userId: '{{ $client->id }}',
                },
                success: function (data) {
                    $('#counterToolbar').html(data.success === true ? data.message : '');
                },
                error: function (jqXHR) {
                    $('#counterToolbar').html(`Error: <b>${jqXHR.responseJSON.message}</b>`);
                },
            });
        }

        function openInfoModal(element, recipeId) {
            let route = "{{ route('admin.search-recipes.preview', ['recipeId' => '%', 'userId' => $client->id]) }}";
            route = route.replace('%', recipeId);
            $.ajax({
                type: 'GET',
                url: route,
                dataType: 'json',
                beforeSend: function () {
                    $(element).append('<span class="fa fa-spinner fa-spin" aria-hidden="true"></span>');
                },
                success: function (data) {
                    $(element).find('span.fa.fa-spinner.fa-spin').remove();
                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: data.message,
                        });
                        return;
                    }
                    const modal = $('#recipeDetailsModal');
                    modal.modal('show');
                    modal.find('.modal-title').html(data.title);
                    modal.find('.modal-body').html(data.data);
                },
                error: function (jqXHR) {
                    $(element).find('span.fa.fa-spinner.fa-spin').remove();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: jqXHR.responseJSON.message,
                    });
                    console.error(jqXHR);
                },
            });
        }
    </script>
@endpush
