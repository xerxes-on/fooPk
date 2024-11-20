<?php

declare(strict_types=1);

namespace Modules\Course\Admin\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Admin\Http\Section\SectionWithImage;
use Modules\Course\Enums\CourseStatus;
use Modules\Course\Models\Course as CourseModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Element\View;

/**
 * Section Challenge
 *
 * @property CourseModel $model
 * @property CourseModel|null $model_value
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class Course extends SectionWithImage implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    protected $icon = 'fas fa-newspaper';

    public function getTitle(): string
    {
        return trans('course::admin.main_title');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->addToNavigation(80);
        });
    }

    /**
     * view grid Challenge entity
     *
     * @throws \Exception
     */
    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth(30),
                    AdminColumn::custom(
                        trans('common.image'),
                        fn(CourseModel $model) => $this->getImageWithUrl(
                            $model->image->url(),
                            $model->image->url('thumb')
                        )
                    )
                        ->setWidth(10),
                    AdminColumn::custom(
                        trans('common.title'),
                        static fn(CourseModel $model) => is_null($model->title) ? '—' : $model->title
                    ),
                    AdminColumn::text('foodpoints', trans('common.foodpoints')),
                    AdminColumn::text('duration', trans('common.duration_days')),
                    AdminColumn::custom(
                        trans('common.draft'),
                        static fn(CourseModel $model) => $model->status === CourseStatus::DRAFT->value ? trans('common.draft') : ''
                    )
                ]
            )
            ->addScript('elfinderPopupImage.js', mix('js/admin/elfinderPopupImage.js'))
            ->addScript('customSearch.js', mix('js/admin/customSearch.js'))
            ->paginate(20);
    }

    /**
     * edit Challenge entity
     *
     * @throws \Exception
     */
    public function onEdit(): FormInterface
    {
        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($this->addChallengeInfoTab(), trans('admin.challenges.tab_title'));
        $tabs->appendTab($this->addArticlesTab(), trans('admin.articles.tab_title'));

        return $tabs;
    }

    /**
     * @throws \Exception
     */
    private function addChallengeInfoTab(): FormInterface
    {
        return AdminForm::panel()
            ->addScript('admin.js', mix('js/admin/admin.js'))
            ->addBody(
                [
                    AdminFormElement::image('image', trans('common.image'))->setView(view('admin::custom.image')),
                    AdminFormElement::columns()
                        ->addColumn(static fn() => [
                            AdminFormElement::select('status', trans('common.status'))
                                ->setOptions(CourseStatus::forSelect())
                                ->setDefaultValue(CourseStatus::DRAFT->value)
                        ], 3),
                    AdminFormElement::text('title', trans('common.title'))->required(),
                    AdminFormElement::text('description', trans('common.description'))->required(),
                    AdminFormElement::columns()
                        ->addColumn(static fn() => [AdminFormElement::text('foodpoints', trans('common.foodpoints'))->required()])
                        ->addColumn(static fn() => [AdminFormElement::number('duration', trans('common.duration_days'))->required()])
                        ->addColumn(static fn() => [
                            AdminFormElement::text('minimum_start_at', trans('common.minimum_start_at') . ' ' . trans('common.format_Y-m-d'))
                        ])
                ]
            )
            ->addStyle('customImage.css', mix('css/admin/customImage.css'))
            ->addScript('elfinderPopupImage.js', mix('js/admin/elfinderPopupImage.js'));
    }

    private function addArticlesTab(): View
    {
        return AdminFormElement::view(
            'course::admin.tab_articles',
            [
                'course'      => $this->model_value,
                'daysPresent' => $this->model_value?->articles()?->pluck('days')?->toArray()
            ]
        );
    }

    /**
     * create Challenge entity
     *
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::image('image', trans('common.image'))
                        ->setView(view('admin::custom.image')),
                    AdminFormElement::checkbox('status', trans('common.draft')),
                    AdminFormElement::text('title', trans('common.title'))->required(),
                    AdminFormElement::number('foodpoints', trans('common.foodpoints'))->required(),
                    AdminFormElement::text('description', trans('common.description'))->required(),
                    AdminFormElement::number('duration', trans('common.duration_days'))->required(),
                    AdminFormElement::text(
                        'minimum_start_at',
                        trans('common.minimum_start_at') . ' ' . trans('common.format_Y-m-d')
                    ),
                ]
            )
            ->addStyle('customImage.css', mix('css/admin/customImage.css'))
            ->addScript('elfinderPopupImage.js', mix('js/admin/elfinderPopupImage.js'));
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
