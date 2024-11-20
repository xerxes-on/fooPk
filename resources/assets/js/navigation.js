jQuery(document).ready(function ($) {
    headerNavigation.init();
    navigationTabs.init();
});
jQuery(window).on('resize', function () {
    if (window.outerWidth > 992) {
        setHeaderPaddingBottom($('.header-submenu').height());
    } else {
        setHeaderPaddingBottom(15);
    }
});
/*---------------------------------------------Navigation--------------------------------------------------------------*/
const headerNavigation = {

    DROPDOWN_SELECTOR: '.js-main-menu-item-dropdown',
    DROPDOWN_ALWAYS_OPEN_SELECTOR: '.js-main-menu-item-opened',
    DROPDOWN_OPEN_CLASS: 'open',
    DROPDOWN_ALWAYS_OPEN_IS_OPENED_CLASS: 'js-main-menu-item-opened open',

    init: function () {
        headerNavigation.activateAlwaysOnTab();
        $(headerNavigation.DROPDOWN_SELECTOR).on('show.bs.dropdown', headerNavigation._onOpenDropdown).on('hide.bs.dropdown', headerNavigation._onHideDropdown).on('hidden.bs.dropdown', headerNavigation._onHiddenDropdown);
    },
    activateAlwaysOnTab: function () {
        const dp = $(headerNavigation.DROPDOWN_ALWAYS_OPEN_SELECTOR);
        if (dp.length > 0) {
            dp.addClass(headerNavigation.DROPDOWN_OPEN_CLASS);
            if (window.outerWidth > 992) {
                setHeaderPaddingBottom(dp.find('.header-submenu').height());
            } else {
                setHeaderPaddingBottom(15);
            }
        }
    },
    _onOpenDropdown: function (e) {
        $(headerNavigation.DROPDOWN_SELECTOR).not($(e.target)).removeClass(headerNavigation.DROPDOWN_OPEN_CLASS);

        //check if nothing is opened 'always on' should be opened. Use tick to get correct values
        setTimeout(function () {
            if (!$(headerNavigation.DROPDOWN_SELECTOR).hasClass(headerNavigation.DROPDOWN_OPEN_CLASS)) {
                $(headerNavigation.DROPDOWN_ALWAYS_OPEN_SELECTOR).addClass(headerNavigation.DROPDOWN_OPEN_CLASS);
            }
        }, 1);

        if (window.outerWidth > 992) {
            setHeaderPaddingBottom($(this).find('.header-submenu').height());
        } else {
            setHeaderPaddingBottom(15);
        }
    },
    _onHideDropdown: function (e) {
        let headerPadding = 15;
        // prevent closing 'Always open element'
        if ($(e.target).hasClass(headerNavigation.DROPDOWN_ALWAYS_OPEN_IS_OPENED_CLASS) &&
            window.outerWidth > 992) {
            e.preventDefault();
            headerPadding = $(this).find('.header-submenu').height();
        }

        setHeaderPaddingBottom(headerPadding);
    },
    _onHiddenDropdown: function (e) {
        let item = $(headerNavigation.DROPDOWN_ALWAYS_OPEN_SELECTOR);
        if (!item.hasClass(headerNavigation.DROPDOWN_OPEN_CLASS)) {
            e.preventDefault();
            item.addClass(headerNavigation.DROPDOWN_OPEN_CLASS);

            if (window.outerWidth > 992) {
                setHeaderPaddingBottom(item.find('.header-submenu').height());
            } else {
                setHeaderPaddingBottom(15);
            }
        }
    },
};

/*-------------------------------------------------TABS----------------------------------------------------------------*/
const navigationTabs = {

    init: function () {
        $('a[data-toggle="tab"]').on('shown.bs.tab', navigationTabs._onTabShow);
        navigationTabs.showActiveTab();
    },
    showActiveTab: function () {
        // Here, save the index to which the tab corresponds. You can see it in the chrome dev tool.
        let activeTab = localStorage.getItem('activeTab');

        if (activeTab) {
            $('a[href="' + activeTab + '"]').tab('show');
        }
    },
    _onTabShow: function (e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    },
};

/*-------------------------------------------------HELPERS----------------------------------------------------------------*/
function setHeaderPaddingBottom(value) {
    document.documentElement.style.setProperty('--header-offset-top',
        (value instanceof String ? value : value + 'px'));
}
