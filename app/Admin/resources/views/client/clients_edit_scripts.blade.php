@php
    use App\Enums\Admin\Permission\PermissionEnum;
    use Modules\Chargebee\Enums\Admin\Client\Filters\ClientChargebeeSubscriptionFilterEnum;
    use App\Enums\Admin\Client\Filters\ClientFormularFilterEnum;
    use App\Enums\Admin\Client\Filters\ClientSubscriptionFilterEnum;
    use App\Enums\Admin\Client\Filters\ClientConsultantFilterEnum;
    use App\Models\Admin;

    $hideRecipesRandomizer = !$isConsultant && $user->hasPermissionTo(PermissionEnum::ADD_RECIPES_TO_CLIENT->value);
    $subscription = $client->subscription;

    $canDeleteAllUserRecipes = $user->can(PermissionEnum::DELETE_ALL_USER_RECIPES->value);
@endphp
@push('scripts')
    <script>
        // tab recipes global vars
        let $tableRecipes;
        const selectedRecipesStorage = 'selected_recipes';

        // tabEntity global vars
        let $tablePopup, $tableUsers;
        const selectedPopupRecipesStorage = 'selected_popup_recipes';
        const selectedUsersStorage = 'selected_users';
        @if($hideRecipesRandomizer)
            let element = document.querySelector('#submit-add-recipes');
            if (element) {
                let $SubmitAddRecipes = Ladda.create(element);
            }
        @endif
        localStorage.removeItem(selectedPopupRecipesStorage);
    </script>
@endpush

@push('footer-scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script>
        // tab recipes From Subscription
        let $recipesByChallenge;
        // formular scripts
        $(document).ready(function () {
            let $form = $('#formularEdit');

            $form.validate({
                lang: 'de',
                ignore: [],
                focusInvalid: false,
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('help-block alert alert-danger');
                    error.insertAfter($(element).closest('.form-group'));
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass('has-error').removeClass('has-success');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).closest('.form-group').addClass('has-success').removeClass('has-error');
                },
                invalidHandler: function (form, validator) {
                    if (!validator.numberOfInvalids()) return;

                    $('html, body').animate({
                        scrollTop: $(validator.errorList[0].element).offset().top - 150,
                    }, 1000);
                },
                groups: {
                    particularly_important: '14[answer][ketogenic] 14[answer][low_carb] 14[answer][moderate_carb] 14[answer][paleo] 14[answer][vegetarian] 14[answer][vegan] 14[answer][pescetarisch] 14[answer][aip] 14[answer][no_matter]',
                },
            });
        });

        //     job Status scripts
        function checkCalculationStatus() {
            $.ajax({
                type: 'GET',
                url: "{{ route('admin.recipes.check-calculation-status', ['userId' => $client->id]) }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                }, beforeSend: function () {
                    $('#calculation-status').html('<span class="fa fa-spinner fa-spin" aria-hidden="true"></span>');
                },
                success: function (data) {
                    if (data.success === true) {
                        $('#calculation-status').removeAttr('class').addClass(`alert alert-${data.status}`).html(data.message).show();
                    } else {
                        $('#calculation-status').hide();
                    }
                },
                error: function (data) {
                    console.error(data);
                },
            });
        }

        jQuery(document).ready(function ($) {
            var time = 30;
            setInterval(function () {
                $('#js-check-refresh').html(--time);
            }, 1000);
            setInterval(function () {
                checkCalculationStatus();
                time = 30;
            }, 30000);
            //     tab-calculations script
            $('#allow_custom_nutrients').on('change', function () {
                let allowed = $(this).prop('checked');
                $('.allowed_custom_nutrients').attr('readonly', !allowed);
                $('.allowed_custom_nutrients_btn').css('display', (allowed) ? 'inline-block' : 'none');
            });
            $('#allow_custom_nutrients').trigger('click');

            $('#calc_auto').on('change', function () {
                let approve = $(this).prop('checked');

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
                            url: "{{ route('admin.client.calc-auto') }}",
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                userId: '{{ $client->id }}',
                                approve: approve,
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
                            },
                        });
                    } else {
                        $(this).prop('checked', !approve);
                    }
                });

            });
            // tab-challenges scripts
            $('.start_at').datepicker({
                dateFormat: 'dd.mm.yy',
            });
            $('.user-challenge-edit').on('click', function (e) {
                let userCourseId = $(this).attr('data-userCourse'),
                    courseId = $(this).attr('data-course'),
                    courseStartDate = $(this).attr('data-courseStartDate'),
                    pattern = /(\d{2})\.(\d{2})\.(\d{4})/,
                    dt = new Date(courseStartDate.replace(pattern, '$3-$2-$1'));

                Swal.fire({
                    title: "@lang('course::common.change_date.title')",
                    html: $('#hidden-template').html(),
                    icon: 'question',
                    showCancelButton: true,
                    didOpen: function () {
                        $('#datetimepicker').datepicker({
                            dateFormat: 'dd.mm.yy',
                            defaultDate: dt,
                        }).datepicker('setDate', dt);
                    },
                }).then(function (result) {
                    if (!result.value) {
                        return;
                    }
                    const date = $('#datetimepicker').val();
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
                        url: "{{ route('admin.client.course.edit') }}",
                        dataType: 'json',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            user_course_id: userCourseId,
                            course_id: courseId,
                            start_at: date,
                        },
                        success: function (result) {
                            location.reload();
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
            });

            $('.user-challenge-delete').on('click', function (e) {
                let userCourseId = $(this).attr('data-userCourse');

                Swal.fire({
                    title: '{{trans('admin.messages.confirmation')}}',
                    text: '{{trans('admin.messages.revert_info')}}',
                    type: 'warning',
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
                        type: 'DELETE',
                        url: "{{ route('admin.client.course.destroy') }}",
                        dataType: 'json',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            user_course_id: userCourseId,
                        },
                        success: function (result) {
                            location.reload();
                        }, error: function (result) {
                            Swal.hideLoading();
                            Swal.fire({
                                title: 'Error!',
                                html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                                icon: 'error',
                            });
                        },
                    });
                });
            });
            // tab chargebee subscription
            $('#chargebee-subscription-add').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                let url = '{{ route("admin.client.assign-chargebee-subscription" ) }}';

                Swal.fire({
                    title: 'Enter Chargebee subscription id',
                    html: '<input id="chargebee-subscription-id" class="form-control" /><span id="chargebee-subscription-id-info-text"></span>',
                    icon: 'question',
                    didOpen: function () {
                        //
                    },
                }).then(function (result) {
                    if (result.value) {

                        let subscriptionId = $('#chargebee-subscription-id').val();

                        Swal.fire({
                            title: 'Please Wait..!',
                            text: 'Is working..',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: 'POST',
                            url: url,
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                chargebee_subscription_id: subscriptionId,
                                client_id: {{ $client->id }}
                            },
                            success: function (result) {
                                location.reload();
                            },
                            error: function (data) {
                                let response = JSON.parse(data.responseText),
                                    errorString = '<ul style="text-align: left;">';
                                $.each(response.errors, function (key, value) {
                                    errorString += '<li>' + value + '</li>';
                                });
                                errorString += '</ul>';

                                Swal.fire({
                                    icon: 'error',
                                    title: response.message,
                                    html: errorString,
                                });
                            },
                        });
                    }
                });
            });
            // tab questionnaire scripts

            $('#approve_formular').change(function () {
                let approve = $(this).prop('checked');

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
                            url: "{{ route('admin.client.questionnaire.approve') }}",
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                userId: '{{ $client->id }}',
                                approve: approve,
                            },
                            success: function (data) {
                                if (data.success === true) {
                                    Swal.hideLoading();
                                    Swal.fire({
                                        icon: 'success',
                                        title: '{{trans('admin.messages.saved')}}',
                                        html: data.message,
                                    });
                                    if (approve) location.reload();
                                } else {
                                    Swal.hideLoading();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        html: data.message,
                                    });
                                }
                            },
                            error: function (data) {
                                let error = JSON.parse(data.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: error.message,
                                });
                            },
                        });
                    } else {
                        $(this).prop('checked', !approve);
                    }
                });
            });

            $('#toggle_formular').change(function () {
                let currentState = $(this).prop('checked');
                Swal.fire({
                    title: '{{trans('admin.messages.confirmation')}}',
                    text: '{{trans('admin.messages.revert_info')}}',
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
                            url: "{{ route('admin.clients.questionnaire.toggle') }}",
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                clientId: {{$client->id }},
                                is_editable: currentState,
                            },
                            success: function (data) {
                                if (data.success === true) {
                                    Swal.hideLoading();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Changes has been applied!',
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
                            error: function (data) {
                                console.log(data);
                                let error = JSON.parse(data.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: error.message,
                                });
                            },
                        });
                    } else {
                        $(this).prop('checked', currentState);
                    }
                });

            });

            $('.compare-formular').on('click', function (e) {
                let questionnaireId = $(this).attr('data-formular');

                $.ajax({
                    type: 'GET',
                    url: "{{ route('admin.client.questionnaire.compare') }}",
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        clientId: '{{ $client->id }}',
                        questionnaireId: questionnaireId,
                    },
                    success: function (data) {
                        if (data.success === true) {
                            $.each(data.data, function (index, value) {
                                $('tr[data-answer=\'' + index + '\'] .compare-answer').html(value);
                            });
                            $('.compare-answer-id').html('(#' + questionnaireId + ')');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: data.message,
                            });
                        }
                    },
                    error: function (data) {
                        let error = JSON.parse(data.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: error.message,
                        });
                    },
                });

            });
            // tab subscriptions scripts
            $('.user-subscription-edit').on('click', function (e) {
                let subscriptionId = $(this).attr('data-subscription'),
                    url = '{{ route("admin.client.subscription-edit", ":id") }}',
                    subscriptionEndDate = $(this).attr('data-subscriptionStartDate'),
                    pattern = /(\d{2})\.(\d{2})\.(\d{4})/,
                    dt = new Date(subscriptionEndDate.replace(pattern, '$3-$2-$1'));

                url = url.replace(':id', subscriptionId);

                Swal.fire({
                    title: 'Confirm details?',
                    html: '<input id="datetimepicker" class="form-control">',
                    icon: 'question',
                    showCancelButton: true,
                    didOpen: function () {
                        $('#datetimepicker').datepicker({
                            dateFormat: 'dd.mm.yy',
                            defaultDate: new Date(),
                        }).datepicker('setDate', dt);
                    },
                }).then(function (result) {
                    if (result.value) {

                        let ends_at = $('#datetimepicker').val();

                        Swal.fire({
                            title: "@lang('admin.messages.wait')",
                            text: "@lang('admin.messages.in_progress')",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: 'PUT',
                            url: url,
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                ends_at: ends_at,
                            },
                            success: function (result) {
                                location.reload();
                            },
                            error: function (data) {
                                let response = JSON.parse(data.responseText),
                                    errorString = '<ul style="text-align: left;">';
                                $.each(response.errors, function (key, value) {
                                    errorString += '<li>' + value + '</li>';
                                });
                                errorString += '</ul>';

                                Swal.fire({
                                    icon: 'error',
                                    title: response.message,
                                    html: errorString,
                                });
                            },
                        });
                    }
                });

            });

            $('.user-subscription-stop').on('click', function (e) {
                let subscriptionId = $(this).attr('data-subscription'),
                    url = '{{ route("admin.client.subscription-stop", ":id") }}';

                url = url.replace(':id', subscriptionId);

                Swal.fire({
                    title: "@lang('admin.messages.confirmation')",
                    text: "@lang('admin.messages.revert_warning')",
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
                            title: "@lang('admin.messages.wait')",
                            text: "@lang('admin.messages.in_progress')",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: 'PUT',
                            url: url,
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                            },
                            success: function (result) {
                                location.reload();
                            },
                            error: function (data) {
                                let response = JSON.parse(data.responseText),
                                    errorString = '<ul style="text-align: left;">';
                                $.each(response.errors, function (key, value) {
                                    errorString += '<li>' + value + '</li>';
                                });
                                errorString += '</ul>';

                                Swal.fire({
                                    icon: 'error',
                                    title: response.message,
                                    html: errorString,
                                });
                            },
                        });
                    }
                });

            });

            $('.user-subscription-delete').on('click', function (e) {
                let subscriptionId = $(this).attr('data-subscription'),
                    url = '{{ route("admin.client.subscription-delete", ":id") }}';

                url = url.replace(':id', subscriptionId);

                Swal.fire({
                    title: "@lang('admin.messages.confirmation')",
                    text: "@lang('admin.messages.revert_warning')",
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
                            title: "@lang('admin.messages.wait')",
                            text: "@lang('admin.messages.in_progress')",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            },
                        });

                        $.ajax({
                            type: 'DELETE',
                            url: url,
                            dataType: 'json',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content'),
                            },
                            success: function (result) {
                                location.reload();
                            },
                            error: function (data) {
                                let response = JSON.parse(data.responseText),
                                    errorString = '<ul style="text-align: left;">';
                                $.each(response.errors, function (key, value) {
                                    errorString += '<li>' + value + '</li>';
                                });
                                errorString += '</ul>';

                                Swal.fire({
                                    icon: 'error',
                                    title: response.message,
                                    html: errorString,
                                });
                            },
                        });
                    }
                });
            });

            $('#subscription-create').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                let activeChallenge = {{ $subscription ? $subscription->id : 0 }};

                if (activeChallenge > 0) {
                    Swal.fire({
                        title: "@lang('admin.messages.confirmation')",
                        text: 'Active subscription will be stopped!',
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
                                title: "@lang('admin.messages.wait')",
                                text: "@lang('admin.messages.in_progress')",
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                allowEnterKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            $(this).closest('form').submit();
                        }
                    });
                } else {
                    $(this).closest('form').submit();
                }

            });
            //     table Entity scripts
            $(document).on('click change', 'input[type="radio"].random_recipe_distribution_type', function (el) {
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
                        url: '/admin/datatable/async',
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
                                return '<a href="{{ url('/') }}/admin/users/' + row.id +
                                    '/edit" class="btn btn-xs btn-primary" title="Edit" data-toggle="tooltip">' +
                                    '<span class="fas fa-pencil-alt"></span>' +
                                    '</a>'
                                        @can(PermissionEnum::DELETE_CLIENT->value, '\App\Models\Admin')
                                    + ' ' +
                                    '<form action="{{ url('/') }}/admin/users/' + row.id +
                                    '/delete" method="POST" style="display:inline-block;">' +
                                    '{{ csrf_field() }}' +
                                    '<input type="hidden" name="_method" value="delete">' +
                                    '<button class="btn btn-xs btn-danger btn-delete" title="Delete" data-toggle="tooltip">' +
                                    '<i class="fas fa-trash-alt"></i>' +
                                    '</button>' +
                                    '</form>';
                                @endcan
                            },
                        },
                    ],
                })
                .on('draw', function (e, settings) {
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
            // tab recipes scripts
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
            //     tab recipes scripts
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
                    title: '@lang('admin.messages.confirmation')',
                    text: '@lang('admin.messages.revert_warning')',
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

        });
        //     tab-balance scripts
        function deposit() {
            Swal.fire({
                title: 'Deposit',
                text: 'How many CS do you want to add?',
                input: 'number',
                icon: 'question',
            }).then(function (result) {
                if (result.value) {

                    let amount = result.value;

                    Swal.fire({
                        title: 'Please Wait..!',
                        text: 'Is working..',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('admin.client.deposit') }}",
                        dataType: 'json',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            userId: '{{ $client->id }}',
                            amount: amount,
                        },
                        success: function (result) {
                            location.reload();
                        },
                    });
                }
            });
        }

        function withdraw() {
            Swal.fire({
                title: '@lang('questionnaire.info.withdraw')',
                text: '@lang('questionnaire.info.withdraw_number')',
                input: 'number',
                icon: 'question',
            }).then(function (result) {
                if (result.value) {

                    let amount = result.value;

                    Swal.fire({
                        title: "@lang('admin.messages.wait')",
                        text: "@lang('admin.messages.in_progress')",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('admin.client.withdraw') }}",
                        dataType: 'json',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            userId: '{{ $client->id }}',
                            amount: amount,
                        },
                        success: function (result) {
                            location.reload();
                        },
                    });
                }
            });
        }
        // tab recipes From Subscription
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            let target = $($(e.target).attr("href")).find('#recipesByChallenge')
            if (target.length && !$.fn.DataTable.isDataTable(target)) {
                // target.DataTable().draw(false);
                $recipesByChallenge = $('#recipesByChallenge').DataTable({
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
                        url: '/admin/datatable/async',
                        data: function (d) {
                            d.method = 'recipesByUserFromActiveChallenge';
                            d.userId = '{{ $client->id }}';
                        },
                    },
                    order: [[4, 'asc']],
                    columns: [
                        {data: 'id', width: '5%'},
                        {data: '_image', orderable: false},
                        {
                            data: 'title',
                            width: '30%',
                            orderable: false,
                        },
                        {data: 'challenge_title', width: '10%'},
                        {data: 'meal_date', width: '8%'},
                        {data: 'meal_time', width: '8%'},
                        {
                            data: 'invalid',
                            orderable: false,
                            width: '5%',
                        },
                        {
                            data: '_kcal',
                            orderable: false,
                            width: '8%',
                        },
                        {
                            data: '_kh',
                            orderable: false,
                            width: '8%',
                        },
                        {
                            data: 'calculated',
                            orderable: false,
                            width: '10%',
                        },
                        {
                            data: '_diets',
                            width: '25%',
                            orderable: false,
                        },
                    ],
                });
            }
        });

        function generateRecipe() {
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
            })
                .then((result) => {
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
                        url: "{{ route('admin.recipes.generate-to-subscription') }}",
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
                            $recipesByChallenge.ajax.reload();
                        },
                    });
                });
        }

        //      table Entity scripts
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
                    title: 'No User selected!',
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
                            url: '/admin/datatable/async',
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

        const addRandomizeRecipes2selectUsers = async function () {
            let usersSelected = $tableUsers.rows({selected: true}).data();
            let userIds = [];

            // check selected rows
            if (usersSelected.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No User selected!',
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

    </script>
@endpush
