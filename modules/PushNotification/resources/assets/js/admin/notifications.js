jQuery(document).ready(function ($) {
    $(document).on('click', '.js-notification-dispatch', function (e) {
        const target = this;
        Swal.fire({
            title: window.foodPunk.admin.i18n.notifications.dispatchTitle,
            icon: 'question',
            showCancelButton: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            allowEnterKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '&#10004;',
            cancelButtonText: '&#10008;',
            html: `<div id="dispatchingNotification"></div>`,
            willOpen: () => {
                Swal.showLoading();
                $.get(
                    target.dataset.route,
                    (payload) => {
                        Swal.hideLoading();
                        Swal.getHtmlContainer().querySelector('#dispatchingNotification').innerHTML = payload;
                    });
            },
            preConfirm: () => {
                const container = Swal.getHtmlContainer();
                let params = {};
                container.querySelectorAll('fieldset [name^="params"]').forEach((input, index) => {
                    const isCheckableInput = (input.type === 'checkbox' || input.type === 'radio'),
                        inputName = parseParamName(input.name);
                    if (isCheckableInput) {
                        // for inputs with `checked` prop we need to check if they are checked, otherwise we skip them
                        if (input.checked) {
                            setNestedValue(params, inputName, input.value);
                        }
                        return;
                    }

                    setNestedValue(params, inputName, input.value);
                });
                removeEmptyValues(params);
                return {
                    id: container.querySelector('input[name="id"]').value,
                    params: params,
                };
            },

        }).then(function (result) {
            if (!result.value) {
                return;
            }

            Swal.fire({
                title: window.foodPunk.admin.i18n.info.workInProgressWait,
                text: window.foodPunk.admin.i18n.info.workInProgress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.post(
                target.dataset.action,
                {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    id: result.value.id,
                    params: result.value.params,
                }
            )
                .then((data) => {
                    if (data.success) {
                        target.style.display = 'none';
                        Swal.fire({
                            title: window.foodPunk.admin.i18n.success,
                            text: data.message,
                            icon: 'success',
                            allowOutsideClick: true,
                            allowEscapeKey: true,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: '&#10004;',
                        });
                    } else {
                        Swal.fire({
                            title: window.foodPunk.admin.i18n.error,
                            text: data.message,
                            icon: 'error',
                            allowOutsideClick: true,
                            allowEscapeKey: true,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: '&#10004;',
                        });
                    }
                })
                .fail((result) => {
                    Swal.hideLoading();
                    Swal.fire({
                        title: 'Error!',
                        html: result.responseJSON.message ? result.responseJSON.message : 'Something went wrong.',
                        icon: 'error',
                        allowOutsideClick: true,
                        allowEscapeKey: true,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: '&#10004;',
                    });
                });
        });
    });

    function parseParamName(string) {
        const matches = string.match(/\[([^\]]+)\]/g);
        if (!matches) return [];
        return matches.map(match => match.slice(1, -1));
    }

    function setNestedValue(result, keys, value) {
        let current = result;
        value = value ? value : null;

        keys.forEach((key, index) => {
            if (index === keys.length - 1) { // in case its last
                current[key] = value;
            } else {
                current[key] = current[key] || {};
                current = current[key];
            }
        });
    }

    function removeEmptyValues(obj) {
        for (const key in obj) {
            if (typeof obj[key] === 'object' && obj[key] !== null) {
                // used for nested objects
                removeEmptyValues(obj[key]);

                // check and clear empty object if it occurred after recursion
                if (Object.keys(obj[key]).length === 0) {
                    delete obj[key];
                }
            } else if (obj[key] === null || obj[key] === '' || obj[key] === undefined) {
                // clear empty (null, пустая строка, undefined)
                delete obj[key];
            }
        }
    }
});
