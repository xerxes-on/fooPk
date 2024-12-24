let $tablePopup, $recipesByChallenge;
const selectedPopupRecipesStorage = 'selected_popup_recipes';
const selectedUsersStorage = 'selected_users';
let $SubmitAddRecipes;
if (window.FoodPunk.static.hideRecipesRandomizer) {
    $SubmitAddRecipes = Ladda.create(document.querySelector('#submit-add-recipes'));
}
localStorage.removeItem(selectedPopupRecipesStorage);
jQuery(document).ready(function ($) {
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

    var time = 30;
    setInterval(function () {
        $('#js-check-refresh').html(--time);
    }, 1000);
    setInterval(function () {
        checkCalculationStatus();
        time = 30;
    }, 30000);
    // tab calculations
    $('#allow_custom_nutrients').on('change', function () {
        let allowed = $(this).prop('checked');
        $('.allowed_custom_nutrients').attr('readonly', !allowed);
        $('.allowed_custom_nutrients_btn').css('display', (allowed) ? 'inline-block' : 'none');
    });
    $('#allow_custom_nutrients').trigger('click');

    $('#calc_auto').on('change', function () {
        let approve = $(this).prop('checked');

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
            if (result.value) {
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
                    url: window.FoodPunk.route.clientCalcAuto,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.static.clientId,
                        approve: approve,
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
            title: window.FoodPunk.i18n.changeDateTitle,
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
                url: window.FoodPunk.route.courseEdit,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    user_course_id: userCourseId,
                    course_id: courseId,
                    start_at: date,
                },
                success: function () {
                    location.reload();
                },
                error: function (result) {
                    Swal.hideLoading();
                    Swal.fire({
                        title: window.FoodPunk.i18n.error,
                        html: result.responseJSON.message ? result.responseJSON.message : window.FoodPunk.i18n.somethingWentWrong,
                        icon: 'error',
                    });
                },
            });
        });
    });
    $('.user-challenge-delete').on('click', function () {
        let userCourseId = $(this).attr('data-userCourse');

        Swal.fire({
            title: window.FoodPunk.i18n.confirmation,
            text: window.FoodPunk.i18n.revertInfo,
            type: 'warning',
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: window.FoodPunk.i18n.defaultsExist,
            cancelButtonText: window.FoodPunk.i18n.defaultsMissing,
        }).then((result) => {
            if (!result.value) {
                return;
            }
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
                type: 'DELETE',
                url: window.FoodPunk.route.courseDestroy,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    user_course_id: userCourseId,
                },
                success: function () {
                    location.reload();
                }, error: function (result) {
                    Swal.hideLoading();
                    Swal.fire({
                        title:  window.FoodPunk.i18n.error,
                        html: result.responseJSON.message ? result.responseJSON.message : window.FoodPunk.i18n.somethingWentWrong,
                        icon: 'error',
                    });
                },
            });
        });
    });

    // job Status scripts
    function checkCalculationStatus() {
        $.ajax({
            type: 'GET',
            url: window.FoodPunk.route.checkCalculationStatus,
            dataType: 'json',
            beforeSend: function () {
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

    // tab chargebee subscription
    $('#chargebee-subscription-add').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        Swal.fire({
            title: window.FoodPunk.i18n.subscriptionId,
            html: '<input id="chargebee-subscription-id" class="form-control" /><span id="chargebee-subscription-id-info-text"></span>',
            icon: 'question',
            didOpen: function () {
                //
            },
        }).then(function (result) {
            if (result.value) {

                let subscriptionId = $('#chargebee-subscription-id').val();

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
                    url: window.FoodPunk.route.assignChargebeeSubscription,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        chargebee_subscription_id: subscriptionId,
                        client_id: window.FoodPunk.static.clientId,
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
            if (result.value) {
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
                    url: window.FoodPunk.route.questionnaireApprove,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.static.clientId,
                        approve: approve,
                    },
                    success: function (data) {
                        if (data.success === true) {
                            Swal.hideLoading();
                            Swal.fire({
                                icon: 'success',
                                title: window.FoodPunk.i18n.saved,
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
            if (result.value) {
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
                    url: window.FoodPunk.route.questionnaireToggle,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        clientId: window.FoodPunk.static.clientId,
                    is_editable: currentState,
            },
                success: function (data) {
                    if (data.success === true) {
                        Swal.hideLoading();
                        Swal.fire({
                            icon: 'success',
                            title: window.FoodPunk.i18n.changesApplied,
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
            url: window.FoodPunk.route.questionnaireCompare,
            dataType: 'json',
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                clientId: window.FoodPunk.static.clientId,
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
            url = window.FoodPunk.route.subscriptionEdit,
            subscriptionEndDate = $(this).attr('data-subscriptionStartDate'),
            pattern = /(\d{2})\.(\d{2})\.(\d{4})/,
            dt = new Date(subscriptionEndDate.replace(pattern, '$3-$2-$1'));

        url = url.replace(':id', subscriptionId);

        Swal.fire({
            title: window.FoodPunk.i18n.confirmDetails,
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
            url = window.FoodPunk.route.subscriptionStop;

        url = url.replace(':id', subscriptionId);

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

            if (result.value) {
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
            url = window.FoodPunk.route.subscriptionDelete

        url = url.replace(':id', subscriptionId);

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

            if (result.value) {
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
        let activeChallenge = window.FoodPunk.static.activeChallenge

        if (activeChallenge > 0) {
            Swal.fire({
                title: window.FoodPunk.i18n.confirmation,
                text: window.FoodPunk.i18n.subscriptionStopped,
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

                if (result.value) {
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

                    $(this).closest('form').submit();
                }
            });
        } else {
            $(this).closest('form').submit();
        }

    });

    // tab recipes scripts
    localStorage.removeItem(selectedRecipesStorage);
    localStorage.removeItem(selectedPopupRecipesStorage);
    $(document).bind('cbox_open', function () {
        $('html').css({overflow: 'hidden'});
    }).bind('cbox_cleanup', function () {
        $('html').css({overflow: 'auto'});
    });
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

    window.FoodPunk.functions.addRecipes = function () {
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
                            url: window.FoodPunk.route.datatableAsync,
                            data: function (d) {
                                d.method = 'allRecipes';
                                d.userId = window.FoodPunk.static.clientId;
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
    window.FoodPunk.functions.openInfoModal = function(element, recipeId) {
        let route = window.FoodPunk.route.searchRecipesPreview
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
    window.FoodPunk.functions.deleteRecipe = function (elem) {
        let recipeId = $(elem).attr('data-id');

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
            if (result.value) {
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

                let route = window.FoodPunk.route.recipesDeleteByUser
                route = route.replace('%', recipeId);
                $.ajax({
                    type: 'DELETE',
                    url: route,
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                    },
                    dataType: 'json',
                    success: function (result) {
                        $tableRecipes.ajax.reload();
                        renderCounterToolbarData();
                        Swal.hideLoading();

                        if (result.success) {
                            Swal.fire({
                                title: window.FoodPunk.i18n.success,
                                html: result.message ? result.message : 'Success',
                                icon: 'success',
                            });
                            return;
                        }

                        Swal.fire({
                            title:  window.FoodPunk.i18n.error,
                            html: result.message ? result.message : 'Something went wrong',
                            icon: 'error',
                        });
                        console.error(result);
                    },
                    error: function (result) {
                        Swal.hideLoading();
                        Swal.fire({
                            title:  window.FoodPunk.i18n.error,
                            html: result.responseJSON.message ? result.responseJSON.message : window.FoodPunk.i18n.somethingWentWrong,
                            icon: 'error',
                        });
                    },
                });
            }
        });
    }
    window.FoodPunk.functions.deleteAllRecipes = function () {
        Swal.fire({
            title: window.FoodPunk.i18n.deleteAllRecipesUser,
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
            if (result.value) {
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
                    type: 'DELETE',
                    url: window.FoodPunk.route.deleteAllRecipes,
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                    },
                    dataType: 'json',
                    success: function (result) {
                        // refresh DataTable
                        $tableRecipes.ajax.reload();
                        $('#counterToolbar').html('');
                        Swal.hideLoading();
                        Swal.fire({
                            title: window.FoodPunk.i18n.deleted,
                            html: result.message,
                            icon: result.success ? 'success' : 'error',
                        });
                    },
                    error: function (result) {
                        Swal.hideLoading();
                        Swal.fire({
                            title: "{{ __('admin.messages.error') }}",
                            html: result.responseJSON.message ? result.responseJSON.message : window.FoodPunk.i18n.somethingWentWrong,
                            icon: 'error',
                        });
                    },
                });
            }
        });
    }
    window.FoodPunk.functions.toggleSelect = function (element) {
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
    window.FoodPunk.functions.deleteSelectedRecipes = function () {
        const data = JSON.parse(localStorage.getItem(selectedRecipesStorage));
        if (data.selected.length === 0) {
            Swal.fire({
                icon: 'error',
                title: window.FoodPunk.i18n.noItemSelected
            });
            return;
        }
        // prompt for confirmation and proceed to delete items
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
            if (!result.value) {
                return;
            }
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
                    userId: window.FoodPunk.static.clientId,
                    recipes: data.selected,
                },
                success: function (result) {
                    // refresh DataTable
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
                        title:  window.FoodPunk.i18n.error,
                        html: result.responseJSON.message ? result.responseJSON.message : window.FoodPunk.i18n.somethingWentWrong,
                        icon: 'error',
                    });
                },
            });
        });
    }
    window.FoodPunk.functions.recalculateUserRecipes = function () {
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
            if (result.value) {
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
                    url: window.FoodPunk.route.recalculateToUser,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.static.clientId,
                    },
                    success: function (data) {
                        if (data.success === true) {
                            Swal.hideLoading();
                            Swal.fire({
                                icon: 'success',
                                title: window.FoodPunk.i18n.success,
                                text: window.FoodPunk.i18n.recordRecalculatedSuccessfully,
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
    window.FoodPunk.functions.addRandomizeRecipes = async function () {
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
            url: window.FoodPunk.route.recipesAddToUserRandom,
            dataType: 'json',
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                userIds: [window.FoodPunk.static.clientId],
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
    window.FoodPunk.functions.deposit = function () {
        Swal.fire({
            title: window.FoodPunk.i18n.deposit,
            text: window.FoodPunk.i18n.csCountMessage,
            input: 'number',
            icon: 'question',
        }).then(function (result) {
            if (result.value) {

                let amount = result.value;

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
                    url: window.FoodPunk.route.clientDeposit,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.static.clientId,
                        amount: amount,
                    },
                    success: function (result) {
                        location.reload();
                    },
                });
            }
        });
    }
    window.FoodPunk.functions.withdraw = function () {
        Swal.fire({
            title: window.FoodPunk.i18n.infoWithdraw,
            text: window.FoodPunk.i18n.infoWithdrawNumber,
            input: 'number',
            icon: 'question',
        }).then(function (result) {
            if (result.value) {

                let amount = result.value;

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
                    url: window.FoodPunk.route.clientWithdraw,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.static.clientId,
                        amount: amount,
                    },
                    success: function (result) {
                        location.reload();
                    },
                });
            }
        });
    }
    window.FoodPunk.functions.generateRecipe = function() {
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
        })
            .then((result) => {
                if (!result.value) {
                    return;
                }
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
                    url: window.FoodPunk.route.recipesGenerateToSub,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.static.clientId,
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
                        $recipesByChallenge.ajax.reload();
                    },
                });
            });
    }
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

    function renderCounterToolbarData() {
        $.ajax({
            type: 'GET',
            url: window.FoodPunk.route.recipesCountData,
            dataType: 'json',
            data: {
                _token: $('meta[name=csrf-token]').attr('content'),
                userId: window.FoodPunk.static.clientId,
            },
            success: function (data) {
                $('#counterToolbar').html(data.success === true ? data.message : '');
            },
            error: function (jqXHR) {
                $('#counterToolbar').html(`Error: <b>${jqXHR.responseJSON.message}</b>`);
            },
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
                    url:window.FoodPunk.route.datatableAsync,
                    data: function (d) {
                        d.method = 'recipesByUserFromActiveChallenge';
                        d.userId = window.FoodPunk.static.clientId;
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
                    url: window.FoodPunk.route.datatableAsync,
                    data: function (d) {
                        d.method = 'recipesByUser';
                        d.userId = window.FoodPunk.static.clientId;
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
                            return '<button type="button" class="btn btn-xs btn-secondary" onclick="window.FoodPunk.functions.openInfoModal(this, ' + row.id + ')"> window.FoodPunk.i18n.notificationInfoModalButton</button>';
                        },
                    },
                    {
                        data: null,
                        className: 'text-center',
                        width: '3%',
                        orderable: false,
                        render: function (data, type, row) {
                            return window.FoodPunk.static.canDeleteAllUserRecipes ? '<button type="button" class="btn btn-xs btn-danger" title="Delete" data-id="' + row.id +
                                '" onclick="window.FoodPunk.functions.deleteRecipe(this)">' +
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

