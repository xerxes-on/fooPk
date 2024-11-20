<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Facade;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Foodpunk'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool)env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'https://meinplan.foodpunk.de'),
    // main users portal url, use for all interactions with users
    'url_meinplan'        => env('MEINPLAN_URL', 'https://meinplan.foodpunk.de'),
    'url_static'          => env('STATIC_URL', 'https://static.foodpunk.de'),
    'app_google_play_url' => env('APP_GOOGLE_PLAY_URL', 'https://play.google.com/store/apps/details?id=de.foodpunk.app'),
    'app_apple_store_url' => env('APP_APPLE_STORE_URL', 'https://apps.apple.com/uz/app/foodpunk/id1447976808'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Europe/Berlin',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale'  => 'de',
    'locales' => [
        'en',
        'de',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        'store'  => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */
        Barryvdh\Elfinder\ElfinderServiceProvider::class,
        Barryvdh\Snappy\ServiceProvider::class,
        Biscolab\ReCaptcha\ReCaptchaServiceProvider::class,
        Jenssegers\Date\DateServiceProvider::class,
        Spatie\GoogleTagManager\GoogleTagManagerServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        Watson\Active\ActiveServiceProvider::class,
        SleepingOwl\Admin\Providers\SleepingOwlServiceProvider::class,
        App\Admin\Providers\AdminSectionsServiceProvider::class,
        Bavix\Wallet\WalletServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,
        Yajra\DataTables\DataTablesServiceProvider::class,
        Neko\LaravelStapler\Providers\L5ServiceProvider::class,

        /*
        * Package Service Providers For Development
        */
        Barryvdh\Debugbar\ServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\TelescopeServiceProvider::class,
        App\Providers\CalculationServiceProvider::class, //TODO: require refactor

        /*
         * Application Modules Service Providers...
         */
        Modules\FlexMeal\Providers\FlexMealServiceProvider::class,
        Modules\Foodpoints\Providers\FoodpointsServiceProvider::class,
        Modules\Ingredient\Providers\IngredientServiceProvider::class,
        Modules\Internal\Providers\InternalServiceProvider::class,
        Modules\PushNotification\Providers\NotificationServiceProvider::class,
        Modules\ShoppingList\Providers\ShoppingListServiceProvider::class,
        Modules\Chargebee\Providers\ChargebeeServiceProvider::class,
        Modules\Course\Providers\CourseServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge(
        [
            // 'ExampleClass' => App\Example\ExampleClass::class,
            'Date'             => Jenssegers\Date\Date::class,
            'Excel'            => Maatwebsite\Excel\Facades\Excel::class,
            'Debugbar'         => Barryvdh\Debugbar\Facades\Debugbar::class,
            'Calculation'      => \App\Helpers\Calculation::class,
            'GoogleTagManager' => Spatie\GoogleTagManager\GoogleTagManagerFacade::class,
            'PDF'              => Barryvdh\Snappy\Facades\SnappyPdf::class,
            'SnappyImage'      => Barryvdh\Snappy\Facades\SnappyImage::class,
            'ReCaptcha'        => Biscolab\ReCaptcha\Facades\ReCaptcha::class,

            // Sleeping Owl admin
            'Admin'        => 'SleepingOwl\Admin\Admin',
            'AdminAuth'    => 'SleepingOwl\AdminAuth\Facades\AdminAuth',
            'AdminRouter'  => 'SleepingOwl\Admin\Facades\AdminRouter',
            'AssetManager' => 'SleepingOwl\Admin\AssetManager\AssetManager',
            'Column'       => 'SleepingOwl\Admin\Columns\Column',
            'FormItem'     => 'SleepingOwl\Admin\Models\Form\FormItem',
            'ModelItem'    => 'SleepingOwl\Admin\Models\ModelItem',
        ]
    )->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Chargebee settings
    |--------------------------------------------------------------------------
    |
    */
    'chargebee' => [
        'auth_user' => env('CHARGEBEE_AUTH_USER'),
        'site'      => env('CHARGEBEE_SITE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Various settings
    |--------------------------------------------------------------------------
    |
    */
    'mix_url'            => env('APP_URL'),
    'allow_lazy_loading' => env('ALLOW_LAZY_LOADING', false),
];
