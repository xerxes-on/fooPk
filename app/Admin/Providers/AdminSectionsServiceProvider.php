<?php

declare(strict_types=1);

namespace App\Admin\Providers;

use App\Admin\Http\Section\Allergy;
use App\Admin\Http\Section\Page;
use App\Admin\Http\Section\Recipe\Recipe;
use App\Admin\Http\Section\Recipe\RecipeDistribution;
use App\Admin\Http\Section\Recipe\RecipeTag;
use App\Admin\Http\Section\Seasons;
use App\Admin\Http\Section\Settings\Inventory;
use App\Admin\Http\Section\Settings\RecipeComplexity;
use App\Admin\Http\Section\Settings\RecipePrice;
use App\Admin\Http\Section\Settings\Vitamins;
use App\Admin\Http\Section\User\Admins;
use App\Admin\Http\Section\User\Users;
use App\Admin\Http\Section\UserDashboard;
use Illuminate\Routing\Router;
use Modules\Course\Admin\Sections\Course;
use Modules\Ingredient\Admin\Sections\IngredientCategoriesAdminSection;
use Modules\Ingredient\Admin\Sections\IngredientDietsAdminSection;
use Modules\Ingredient\Admin\Sections\IngredientsAdminSection;
use Modules\Ingredient\Admin\Sections\IngredientTagAdminSection;
use Modules\Ingredient\Admin\Sections\IngredientUnitAdminSection;
use Modules\PushNotification\Admin\Sections\Notification;
use Modules\PushNotification\Admin\Sections\NotificationType;
use SleepingOwl\Admin\Contracts\Navigation\NavigationInterface;
use SleepingOwl\Admin\Contracts\Template\MetaInterface;
use SleepingOwl\Admin\Contracts\Widgets\WidgetsRegistryInterface;
use SleepingOwl\Admin\Providers\AdminSectionsServiceProvider as ServiceProvider;

final class AdminSectionsServiceProvider extends ServiceProvider
{
    protected array $widgets = [
        \App\Admin\Widgets\NavigationUserBlock::class,
    ];

    protected $policies = [
        //Ingredient
        \Modules\Ingredient\Admin\Sections\IngredientsAdminSection::class          => \Modules\Ingredient\Policies\IngredientsSectionModelPolicy::class,
        \Modules\Ingredient\Admin\Sections\IngredientCategoriesAdminSection::class => \Modules\Ingredient\Policies\IngredientCategoriesSectionModelPolicy::class,
        \Modules\Ingredient\Admin\Sections\IngredientDietsAdminSection::class      => \Modules\Ingredient\Policies\IngredientDietsSectionModelPolicy::class,
        \Modules\Ingredient\Admin\Sections\IngredientTagAdminSection::class        => \Modules\Ingredient\Policies\IngredientTagSectionModelPolicy::class,
        \Modules\Ingredient\Admin\Sections\IngredientUnitAdminSection::class       => \Modules\Ingredient\Policies\IngredientUnitSectionModelPolicy::class,
        // Notification
        \Modules\PushNotification\Admin\Sections\Notification::class     => \Modules\PushNotification\Policies\NotificationSectionModelPolicy::class,
        \Modules\PushNotification\Admin\Sections\NotificationType::class => \Modules\PushNotification\Policies\NotificationSectionModelPolicy::class,
        // Course
        \Modules\Course\Admin\Sections\Course::class => \Modules\Course\Admin\Policies\CourseSectionModelPolicy::class,
    ];

    protected $sections = [
        \App\Models\Recipe::class                                => Recipe::class,
        \App\Models\RecipeTag::class                             => RecipeTag::class,
        \App\Models\RecipeDistribution::class                    => RecipeDistribution::class,
        \Modules\Ingredient\Models\Ingredient::class             => IngredientsAdminSection::class,
        \Modules\Ingredient\Models\IngredientCategory::class     => IngredientCategoriesAdminSection::class,
        \Modules\Ingredient\Models\IngredientTag::class          => IngredientTagAdminSection::class,
        \App\Models\Diet::class                                  => IngredientDietsAdminSection::class,
        \Modules\Ingredient\Models\IngredientUnit::class         => IngredientUnitAdminSection::class,
        \App\Models\Seasons::class                               => Seasons::class,
        \App\Models\Admin::class                                 => Admins::class,
        \App\Models\User::class                                  => Users::class,
        \App\Models\Allergy::class                               => Allergy::class,
        \Modules\Course\Models\Course::class                     => Course::class,
        \Modules\PushNotification\Models\Notification::class     => Notification::class,
        \Modules\PushNotification\Models\NotificationType::class => NotificationType::class,
        \App\Models\Vitamin::class                               => Vitamins::class,
        \App\Models\Inventory::class                             => Inventory::class,
        \App\Models\RecipeComplexity::class                      => RecipeComplexity::class,
        \App\Models\RecipePrice::class                           => RecipePrice::class,
        \App\Models\Page::class                                  => Page::class,
        \App\Models\UserDashboard::class                         => UserDashboard::class,
    ];

    /**
     * Register sections.
     */
    public function boot(\SleepingOwl\Admin\Admin $admin): void
    {
        $this->loadViewsFrom(base_path('app/Admin/resources/views'), 'admin');
        $this->registerPolicies('App\\Admin\\Policies\\');

        $this->app->call([$this, 'registerRoutes']);
        $this->app->call([$this, 'registerNavigation']);

        parent::boot($admin);

        $this->app->call([$this, 'registerViews']);
        $this->app->call([$this, 'registerMediaPackages']);
    }

    public function registerRoutes(Router $router): void
    {
        $router->group([
            'prefix'     => config('sleeping_owl.url_prefix'),
            'middleware' => config('sleeping_owl.middleware')
        ], function ($router) {
            require_once app_path('Admin/Http/routes.php');
            require_once base_path('modules/Ingredient/routes/admin.php');
            require_once base_path('modules/PushNotification/routes/admin.php');
            require_once base_path('modules/Course/routes/admin.php');
        });
    }

    /**
     * We only register here menu items that do not have a model
     */
    public function registerNavigation(NavigationInterface $navigation): void
    {
        require_once app_path('Admin/navigation.php');
    }

    public function registerViews(WidgetsRegistryInterface $widgetsRegistry): void
    {
        foreach ($this->widgets as $widget) {
            $widgetsRegistry->registerWidget($widget);
        }
    }

    public function registerMediaPackages(MetaInterface $meta): void
    {
        $packages = $meta->assets()->packageManager();

        /**
         * DataTables
         * @see https://datatables.net/
         * These are the main files for DataTables that were grouped and consist of the following:
         * - Bootstrap 4 css
         * - DataTables js
         * - Extension:
         *     - Buttons
         *     - RowGroup
         *     - FixedHeader
         *     - Responsive
         *     - SearchBuilder
         *     - SearchPanes
         *     - Select
         */
        $packages->add('dataTables')
            ->css('dt_css', '//cdn.datatables.net/v/bs4/dt-2.1.6/b-3.1.2/fh-4.0.1/r-3.0.3/rg-1.5.0/sb-1.8.0/sp-2.3.2/sl-2.0.5/datatables.min.css')
            ->css('dt_customPaginationCss', mix('css/admin/dataTablesPagination.css'))
            ->js('dt_js', '//cdn.datatables.net/v/bs4/dt-2.1.6/b-3.1.2/fh-4.0.1/r-3.0.3/rg-1.5.0/sb-1.8.0/sp-2.3.2/sl-2.0.5/datatables.min.js', [], true)
            ->js('dt_pagination', mix('/js/admin/dTInputPaginationPlugin.js'), ['dt_js'], true);

        // Ladda
        $packages->add('ladda')
            ->css('ld_styles', '//cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/ladda-themeless.min.css')
            ->js('ld_spinJs', '//cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/spin.min.js')
            ->js('ld_mainJs', '//cdnjs.cloudflare.com/ajax/libs/Ladda/1.0.6/ladda.min.js');


        require_once app_path('Admin/assets.php');
    }

}
