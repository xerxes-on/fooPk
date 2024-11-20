<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Title
    |--------------------------------------------------------------------------
    |
    | Displayed in title and header.
    |
    */

    'title' => 'FoodPunk administrator',

    /*
    |--------------------------------------------------------------------------
    | Admin Mini logo
    |--------------------------------------------------------------------------
    */
    'logo_mini' => 'FoodPunk',

    /*
    |--------------------------------------------------------------------------
    | Admin Text on sidebar top menu
    | Made this empty(one space only. If delete this one space it will affect to style)
    | because "Main Menu" text was found unnecessary by QAs
    |--------------------------------------------------------------------------
    */
    'menu_top' => ' ',

    /*
    |--------------------------------------------------------------------------
    | Sidebar default condition class "sidebar-collapse", "sidebar-open"
    |--------------------------------------------------------------------------
    | Sidebar mini show  'sidebar-mini' - 1024px , "sidebar-mini-md" - 768px,
    | "sidebar-mini-xs" - always
    |--------------------------------------------------------------------------
    | Font sizes "text-sm"
    |--------------------------------------------------------------------------
    | See https://adminlte.io/themes/v3/# - right top corner "Customize AdminLTE"
    */
    'body_default_class' => 'hold-transition sidebar-mini sidebar-open',

    /*
    |--------------------------------------------------------------------------
    | Admin Logo
    |--------------------------------------------------------------------------
    |
    | Displayed in navigation panel.
    |
    */

    'logo' => '<span class="pull-left">FoodPunk</span>',

    /*
    |--------------------------------------------------------------------------
    | Admin URL prefix
    |--------------------------------------------------------------------------
    */

    'url_prefix' => 'admin',

    /*
     * Subdomain & Domain support routes
     */
    'domain' => false,

    /*
    |--------------------------------------------------------------------------
    | Middleware to use in admin routes
    |--------------------------------------------------------------------------
    |
    | In order to create authentication views and routes
    | don't forget to execute `php artisan make:auth`.
    | See https://laravel.com/docs/authentication
    |
    */

    'middleware' => ['web', 'auth:admin', 'checkRole.admin'],

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'favicon' => '/images/icons/foodpunk-logo57.svg',

    /*
    |--------------------------------------------------------------------------
    | Load dev Vue js
    |--------------------------------------------------------------------------
    */

    'dev_assets' => env('ADMIN_DEV_ASSETS', false),

    /*
    |--------------------------------------------------------------------------
    | Env Editor
    |--------------------------------------------------------------------------
    | Url for env editor
    |
    */
    'env_editor_url' => 'env/editor',

    /*
     * Excluded keys
     */
    'env_editor_excluded_keys' => [
        'APP_KEY',
        'DB_*',
    ],

    /*
     * Env editor middlewares
     */
    'env_editor_middlewares' => [],

    /*
     * Enable and show link in navigation
     * 'show_editor' is @deprecated
     */
    'enable_editor'     => false,
    'env_keys_readonly' => false,
    'env_can_delete'    => true,
    'env_can_add'       => true,

    /*
     * --------------------------------------------------------------------------
     * Add your policy class here.
     * --------------------------------------------------------------------------
     */
    'env_editor_policy' => '',

    /*
     * --------------------------------------------------------------------------
     * DataTables state saving.
     * --------------------------------------------------------------------------
     */
    'state_datatables' => true,

    /*
     * --------------------------------------------------------------------------
     * Tabs state remember.
     * --------------------------------------------------------------------------
     */
    'state_tabs' => false,

    /*
     * --------------------------------------------------------------------------
     * Filters state remember in DataTables.
     * --------------------------------------------------------------------------
     */
    'state_filters' => false,

    /*
    |--------------------------------------------------------------------------
    | Authentication default provider
    |--------------------------------------------------------------------------
    |
    | @see config/auth.php : providers
    |
    */

    'auth_provider' => 'admins',

    /*
    |--------------------------------------------------------------------------
    |  Path to admin bootstrap files directory
    |--------------------------------------------------------------------------
    |
    | Default: app_path('Admin')
    |
    */

    'bootstrapDirectory' => app_path('Admin'),

    /*
    |--------------------------------------------------------------------------
    |  Directory for uploaded images (relative to `public` directory)
    |--------------------------------------------------------------------------
    */

    'imagesUploadDirectory' => 'images/uploads',

    /*
    |--------------------------------------------------------------------------
    |  Use LazyLoad for AdminColumn::image in tables
    |  in `imageLazyLoadFile` insert path to file or `data:image/gif;base64,...`
    |--------------------------------------------------------------------------
    */

    'imageLazyLoad'     => true,
    'imageLazyLoadFile' => 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==',

    /*
    |--------------------------------------------------------------------------
    |  Allowed Extensions for uploaded images - array
    |--------------------------------------------------------------------------
    */

    'imagesAllowedExtensions' => [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'bmp',
        'svg',
        'webp',
        'ico',
        'jpe',
    ],

    /*
    |--------------------------------------------------------------------------
    |  Allow to upload svg-files without required xml-header as image - boolean
    |--------------------------------------------------------------------------
    */

    'imagesAllowSvg' => false,

    /*
    |--------------------------------------------------------------------------
    |  Behavoir if file exists (default 'UPLOAD_HASH'). See in UploadController
    |--------------------------------------------------------------------------
    */

    'imagesUploadFilenameBehavior' => 'UPLOAD_HASH',

    /*
    |--------------------------------------------------------------------------
    |  Directory for uploaded files (relative to `public` directory)
    |--------------------------------------------------------------------------
    */

    'filesUploadDirectory' => 'files/uploads',

    /*
    |--------------------------------------------------------------------------
    |  Allowed Extensions for uploaded files - array
    |--------------------------------------------------------------------------
    */

    'filesAllowedExtensions' => [],

    /*
    |--------------------------------------------------------------------------
    |  Behavoir if file exists (default 'UPLOAD_HASH'). See in UploadController
    |--------------------------------------------------------------------------
    */

    'filesUploadFilenameBehavior' => 'UPLOAD_HASH',

    /*
    |--------------------------------------------------------------------------
    |  Admin panel template
    |--------------------------------------------------------------------------
    */

    'template' => SleepingOwl\Admin\Templates\TemplateDefault::class,

    /*
    |--------------------------------------------------------------------------
    |  Default date and time formats
    |--------------------------------------------------------------------------
    */

    'datetimeFormat' => 'd.m.Y H:i',
    'dateFormat'     => 'd.m.Y',
    'timeFormat'     => 'H:i',
    'timezone'       => 'Europe/Berlin',

    /*
    |--------------------------------------------------------------------------
    | Use Card
    |--------------------------------------------------------------------------
    |
    | Using default cards views.
    |
    */

    'useWysiwygCard'      => false,
    'useRelationCard'     => false,
    'useHasManyLocalCard' => false,

    /*
    |--------------------------------------------------------------------------
    | Editors
    |--------------------------------------------------------------------------
    |
    | Select default editor and tweak options if needed.
    |
    */

    'wysiwyg' => [
        'default' => 'ckeditor',

        /*
         * See http://docs.ckeditor.com/#!/api/CKEDITOR.config
         */
        'ckeditor' => [
            'defaultLanguage' => 'en', // Hardcoded into EN as DE locale is not correctly supported by vendor
            'height'          => 500,
            'allowedContent'  => true,
            'fillEmptyBlocks' => false,
            'extraPlugins'    => 'uploadimage,image2,justify,youtube,uploadfile',
            /*
             * WARNING!!!! CKEDITOR on D & D and UploadImageDialog
             * BY DEFAULT IMAGES WILL STORE TO imagesUploadDirectory = /images/uploads
             * 'uploadUrl'            => '/path/to/your/action',
             * 'filebrowserUploadUrl' => '/path/to/your/action',
             */

        ],

        /*
         * See https://www.tinymce.com/docs/
         */
        'tinymce' => [
            'height' => 200,
        ],

        /*
         * See https://github.com/NextStepWebs/simplemde-markdown-editor
         */
        'simplemde' => [
            'hideIcons' => ['side-by-side', 'fullscreen'],
        ],

        /*
        * ver. 0.8.12
        * See https://summernote.org/
        * Need jQuery
        */
        'summernote' => [
            'height'     => 200,
            'lang'       => config('app.locale'),
            'codemirror' => [
                'theme' => 'monokai',
            ],
        ],

        /*
         * See https://ckeditor.com/docs/ckeditor5/latest/builds/guides/integration/configuration.html
         *
         * For using CKFinder with CKEditor 5 you must load additional js-file, see /app/Admin/bootstrap.php
         * See https://ckeditor.com/docs/ckeditor5/latest/features/image-upload/ckfinder.html#configuring-the-full-integration
         *
         * Be careful: CKEditor 5 haven't html source code button feature!
         * See https://github.com/ckeditor/ckeditor5/issues/592
         */
        'ckeditor5' => [
            'files' => [
                /*
                 * Use Classic build from CDN - provides a limited number of components and capabilities
                 * See https://ckeditor.com/ckeditor-5/download/
                 */
                //'editor' => '//cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js',
                //'translation' => '//cdn.ckeditor.com/ckeditor5/27.1.0/classic/translations/'.config('app.locale').'.js',
                /*
                 * Use Custom build with most-used additional plugins
                 * See https://ckeditor.com/ckeditor-5/online-builder/
                 */
                'editor'      => '/packages/sleepingowl/ckeditor5/build/ckeditor.js',
                'translation' => '/packages/sleepingowl/ckeditor5/build/translations/' . config('app.locale') . '.js',
            ],

            'language' => config('app.locale'),

            // Text alignment options
            'alignment' => [
                'options' => [
                    'left',
                    'center',
                    'right', /*'justify',*/
                ],
            ],

            // Uncomment some plugins if you need to enable them
            'removePlugins' => [
                // See https://ckeditor.com/docs/ckeditor5/latest/api/module_heading_title-Title.html
                'Title',
                // See https://ckeditor.com/docs/ckeditor5/latest/features/lists/lists.html#list-styles
                'ListStyle',
                // See https://ckeditor.com/docs/ckeditor5/latest/features/markdown.html
                'Markdown',
            ],

            // Toolbar components
            'toolbar' => [
                // Active toolbar components
                'undo',
                'redo',
                '|',
                'heading',
                '|',
                'bold',
                'italic',
                'alignment',
                'fontColor',
                'blockQuote',
                'link',
                'bulletedList',
                'numberedList',
                'removeFormat',
                '|',
                'insertImage',
                'mediaEmbed',
                'insertTable',
                '|',

                // All available toolbar components:
                /*
                'heading',
                'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
                'alignment', 'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', 'highlight',
                'link', 'bulletedList', 'numberedList', 'outdent', 'indent',
                'imageUpload', 'imageInsert', 'mediaEmbed', 'insertTable', 'blockQuote', 'htmlEmbed',
                'textPartLanguage', 'codeBlock', 'code', 'pageBreak', 'horizontalLine', 'specialCharacters',
                'undo', 'redo', 'removeFormat', '|',
                */
            ],

            // Images options
            'image' => [
                'styles' => [
                    'alignLeft',
                    'alignCenter',
                    'alignRight',
                    'full',
                    'side',
                ],
                'toolbar' => [
                    'imageStyle:alignLeft',
                    'imageStyle:alignCenter',
                    'imageStyle:alignRight',
                    '|',
                    'imageTextAlternative',
                    '|',
                    'link',
                ],
            ],

            // Tables options
            'table' => [
                'contentToolbar' => [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells',
                    'tableProperties',
                    'tableCellProperties',
                ],
            ],

            // Media embed options
            'mediaEmbed' => [
                'toolbar' => ['mediaEmbed'],
                /**
                 * @see https://ckeditor.com/docs/ckeditor5/latest/features/media-embed.html#including-previews-in-data
                 * @see https://ckeditor.com/docs/ckeditor5/latest/features/media-embed.html#displaying-embedded-media-on-your-website
                 */
                'previewsInData' => true,
                /**
                 * The names of providers with rendering functions (previews): dailymotion, spotify, youtube, vimeo.
                 *
                 * @see https://ckeditor.com/docs/ckeditor5/latest/api/module_media-embed_mediaembed-MediaEmbedConfig.html#member-providers
                 *
                 * So, we need to remove providers without rendering function
                 * @see https://ckeditor.com/docs/ckeditor5/latest/features/media-embed.html#removing-media-providers
                 */
                'removeProviders' => ['instagram', 'twitter', 'googleMaps', 'flickr', 'facebook'],
            ],

            'uploadUrl'            => '/storage/images_admin',
            'filebrowserUploadUrl' => '/storage/images_admin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DataTables
    |--------------------------------------------------------------------------
    |
    | Select default settings for datatable
    |
    */
    'datatables' => [],

    /*
    |--------------------------------------------------------------------------
    | DataTables column highlight
    |--------------------------------------------------------------------------
    |
    | Highlight DataTables column on mouseover
    |
    */
    'datatables_highlight' => false,

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    |
    */
    'breadcrumbs' => true,

    /*
    |--------------------------------------------------------------------------
    | Autoupdate datatables
    |--------------------------------------------------------------------------
    |
    | Interval in minutes. Do not set too low.
    | dt_autoupdate_interval >= 1 and (int)
    | dt_autoupdate_class - custom class if need (can be null)
    | dt_autoupdate_color - color ProgressBar (can be null)
    |
    */
    'dt_autoupdate'          => false,
    'dt_autoupdate_interval' => 5, //minutes
    'dt_autoupdate_class'    => '',
    'dt_autoupdate_color'    => '#dc3545',

    /*
    |--------------------------------------------------------------------------
    | Add scrolls button
    |--------------------------------------------------------------------------
    |
    */

    'scroll_to_top'    => true,
    'scroll_to_bottom' => true,

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started.
    |
    */

    'aliases' => [
        // Components
        'Assets'         => KodiCMS\Assets\Facades\Assets::class,
        'PackageManager' => KodiCMS\Assets\Facades\PackageManager::class,
        'Meta'           => KodiCMS\Assets\Facades\Meta::class, // will destroy
        'Form'           => Collective\Html\FormFacade::class,
        'HTML'           => Collective\Html\HtmlFacade::class,
        'WysiwygManager' => SleepingOwl\Admin\Facades\WysiwygManager::class,
        'MessagesStack'  => SleepingOwl\Admin\Facades\MessageStack::class,

        // Presenters
        'AdminSection'        => SleepingOwl\Admin\Facades\Admin::class,
        'AdminTemplate'       => SleepingOwl\Admin\Facades\Template::class,
        'AdminNavigation'     => SleepingOwl\Admin\Facades\Navigation::class,
        'AdminColumn'         => SleepingOwl\Admin\Facades\TableColumn::class,
        'AdminColumnEditable' => SleepingOwl\Admin\Facades\TableColumnEditable::class,
        'AdminColumnFilter'   => SleepingOwl\Admin\Facades\TableColumnFilter::class,
        'AdminDisplayFilter'  => SleepingOwl\Admin\Facades\DisplayFilter::class,
        'AdminForm'           => SleepingOwl\Admin\Facades\Form::class,
        'AdminFormElement'    => SleepingOwl\Admin\Facades\FormElement::class,
        'AdminDisplay'        => SleepingOwl\Admin\Facades\Display::class,
        'AdminWidgets'        => SleepingOwl\Admin\Facades\Widgets::class,
    ],
];
