<?php

declare(strict_types=1);

namespace Modules\Course\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Course\Http\View\Components\ArticleTabContentComponent;
use Modules\Course\Http\View\Components\ArticleTabLinkComponent;
use Modules\Course\Http\View\Components\CourseComponent;
use Modules\Course\Http\View\Components\CourseWidgetComponent;
use Modules\Course\Http\View\Components\CustomCourseComponent;
use Modules\Course\Http\View\Components\DailyCourseArticleComponent;

final class CourseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'course');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'course');
        $this->mergeConfigFrom(__DIR__ . '/../config/main.php', 'course');
        $this->bootComponents();
    }

    private function bootComponents(): void
    {
        Blade::component('course', CourseComponent::class);
        Blade::component('article-nav-link', ArticleTabLinkComponent::class);
        Blade::component('article-nav-content', ArticleTabContentComponent::class);
        Blade::component('daily-course-article', DailyCourseArticleComponent::class);
        Blade::component('custom-course-article', CustomCourseComponent::class);
        Blade::component('course-widget', CourseWidgetComponent::class);
    }
}
