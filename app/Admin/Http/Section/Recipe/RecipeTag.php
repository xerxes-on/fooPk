<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\Recipe;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Recipe as RecipeModel;
use App\Models\RecipeTag as RecipeTagModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Class Recipe tag
 *
 * @property RecipeTagModel $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class RecipeTag extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    protected $icon = 'fas fa-tag';

    public function getTitle(): string
    {
        return trans('admin.recipe_tag.title');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(Recipe::ID)
                ?->addPage((new Page(RecipeTagModel::class))->setPriority(13));
        });
    }

    public function onDisplay(): DisplayInterface
    {
        $display = AdminDisplay::datatables()
            ->with(['recipes', 'translations'])
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth('5%'),
                    AdminColumn::text(
                        'slug',
                        trans('common.slug')
                    )->setWidth('20%')->setOrderable(false),
                    AdminColumn::text(
                        'title',
                        trans('common.title')
                    )->setWidth('20%')->setOrderable(false),
                    AdminColumn::custom(
                        trans('common.recipes'),
                        static function (RecipeTagModel $model) {
                            $model->loadCount('recipes');
                            return view('admin::collapsedList', [
                                'id'     => "recipe_tag_$model->id",
                                'titles' => $model->recipes->pluck('id'),
                                'count'  => $model->recipes_count,
                            ])->render();
                        }
                    ),
                    AdminColumn::custom(
                        trans('admin.recipe_tag.publicFlag'),
                        static fn(RecipeTagModel $model) => $model->filter ?
                            '<span class="fas fa-check text-success" aria-hidden="true"></span>' :
                            '<span class="fas fa-times text-warning" aria-hidden="true"></span>'
                    )->setWidth('5%'),
                    AdminColumn::custom(
                        trans('admin.recipe_tag.internalFlag'),
                        static fn(RecipeTagModel $model) => $model->is_internal ?
                            '<span class="fas fa-check text-success" aria-hidden="true"></span>' :
                            '<span class="fas fa-times text-warning" aria-hidden="true"></span>'
                    )->setWidth('5%'),
                ]
            )
            ->addScript('recipe-tags.js', mix('js/admin/recipes/recipe-tags.js'))
            ->paginate(20);

        $display
            ->getExtension('links')
            ->setView(
                view(
                    'admin::recipe.tags.search',
                )
            );
        return $display;
    }

    /**
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * @throws \Exception
     */
    public function onEdit(?int $id = null): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::hidden('id')->setValue($id),
                    AdminFormElement::text('slug', 'Slug')
                        ->required()
                        ->setHtmlAttribute('maxlength', '20')
                        ->setHelpText(
                            trans(
                                'admin_help_text.notification_slug',
                                ['amount' => 20]
                            )
                        ),
                    AdminFormElement::columns()
                        ->addColumn(
                            [
                                AdminFormElement::checkbox(
                                    'filter',
                                    trans('admin.recipe_tag.publicFlag')
                                )
                                    ->setHelpText(
                                        trans('admin_help_text.recipe_tag.publicFlag')
                                    ),
                            ],
                        )
                        ->addColumn(
                            [
                                AdminFormElement::checkbox(
                                    'is_internal',
                                    trans('admin.recipe_tag.internalFlag')
                                )
                                    ->setHelpText(
                                        trans('admin_help_text.recipe_tag.randomFlag')
                                    ),

                            ],
                        ),
                    AdminFormElement::view(
                        'admin::partials.tabbed_translations',
                        [
                            'attribute' => 'title',
                            'label'     => trans('common.title'),
                            'helptext'  => trans(
                                'admin_help_text.notification_attribute',
                                ['attribute' => 'title']
                            )
                        ]
                    ),
                    AdminFormElement::multiselectajax(
                        'recipes',
                        trans('common.all_recipes'),
                    )
                        ->setModelForOptions(RecipeModel::class)
                        ->setSearchUrl(
                            route(
                                'admin.search-recipes.select2',
                                ['excludedId' => 0]
                            )
                        )
                        ->setMinSymbols(1)
                        ->setDisplay(fn(RecipeModel $model) => "[$model->id] $model->title")
                        ->setSelect2Options(
                            [
                                'placeholder' => trans('common.select_new_recipe'),
                                'ajax--delay' => 400
                            ]
                        )
                        ->setHelpText(trans('admin_help_text.related_recipes_help_text'))
                ]
            )
            ->setAction(route('admin.recipes.tags.store'));
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
