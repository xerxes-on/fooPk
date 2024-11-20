<?php
/**
 * @see KodiCMS\Assets\Meta for Meta instance methods
 * @see http://sleepingowladmin.ru/docs/assets
 *
 * @example Meta::addJs('ckfinder5', 'https://ckeditor.com/apps/ckfinder/3.5.0/ckfinder.js', ['admin-default']);
 */

// hardcoded https
if (in_array(config('app.env'), ['stage', 'production'], true)) {
    \URL::forceScheme('https'); // TODO: find more appropriate solution to force https for these assets
}

Meta::addJs('elfinder', asset('packages/barryvdh/elfinder/js/elfinder.min.js'), 'admin-default')
    ->addJs('colorbox-min.js', mix('vendor/colorbox/jquery.colorbox-min.js'), 'admin-default')
    ->addCss('colorboxTheme.css', mix('vendor/colorbox/colorboxTheme.css'), 'admin-default')
    ->addCss(
        'fonts.css',
        'https://fonts.googleapis.com/css2?family=Oswald&family=Raleway:ital,wght@0,400;0,700;1,400;1,700&display=swap',
        'admin-default'
    )

    ->addCss('progressBar.css', mix('css/admin/progressBar.css'), 'admin-default')
    ->addJs(
        'datetimepicker.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js',
        'admin-default'
    )
    ->addCss('jquery-ui.css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', 'admin-default')
    ->addJs('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', 'admin-default')
    ->addJs('custom-admin', mix('js/admin/admin-custom.js'), 'admin-default')
    ->addJs('users-section.min.js', mix('js/admin/users.js'), 'admin-default')
    ->addJs('tagify.min.js', 'https://unpkg.com/@yaireo/tagify', 'admin-default')
    ->addJs('tagify.polyfills.min.js', 'https://unpkg.com/@yaireo/tagify/dist/tagify.polyfills.min.js', 'admin-default')
    ->addCss('tagify.min.css', 'https://unpkg.com/@yaireo/tagify@4.14.0/dist/tagify.css', 'admin-default')
    ->addCss('extend', asset('packages/sleepingowl/default/css/admin-app.css'))
    ->addCss('customAdmin.min.css', mix('css/admin/customAdmin.css'));
