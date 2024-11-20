function createInputElement(settings) {
    const pageInfo = $(settings.nTable).DataTable().page.info();
    const eventHandler = function (target) {
        if (target.value === '' || target.value.match(/[^0-9]/)) {
            /* Nothing entered or non-numeric character */
            target.value = target.value.replace(/\D/g, ''); // don't even allow anything but digits
            return;
        }

        const page = Number(target.value - 1);
        $(settings.nTable).DataTable().page(page).draw(false);
    }
    const $input = $('<input>', {
        class: 'form-control pagination-input',
        type: 'number',
        min: 1,
        max: pageInfo.pages,
    }).val(pageInfo.page + 1);
    $input.on('change', function (e) {
        eventHandler(this);
    }).on('keydown', function (e) {
        // Check if the Enter key (key code 13) is pressed
        if (event.key === 'Enter' || event.keyCode === 13) {
            eventHandler(this);
        }
    });

    return $input;
}

$.fn.DataTable.ext.pager.input = function () {
    return ['first', 'previous', 'numbers', 'next', 'last'];
};

$.fn.DataTable.ext.renderer.pagingButton.bootstrap = function (settings, buttonType, content, active, disabled) {
    let classes = settings.oClasses.paging;
    let btnClasses = [classes.button];
    let btn;
    if (active) {
        btnClasses.push(classes.active);
    }

    if (disabled) {
        btnClasses.push(classes.disabled);
    }

    let li = $('<li>').addClass(btnClasses.join(' '));
    btn = $('<a>', {class: 'page-link'}).html(content);

    if (buttonType === 'last') {
        let btnT = $('<div>', {class: 'input-pagination-wrapper'})
            .append(btn)
            .append(createInputElement(settings));
        li.append(btnT);
    } else {
        li.append(btn);
    }

    return {
        display: li,
        clicker: btn
    }
};

$.extend(DataTable.ext.classes, {
    paging: {
        active: 'active',
        button: 'dt-paging-button page-item',
        container: 'dt-paging',
        disabled: 'disabled',
        nav: ''
    }
});