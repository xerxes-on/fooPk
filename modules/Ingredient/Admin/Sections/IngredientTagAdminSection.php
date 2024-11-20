<?php

declare(strict_types=1);

namespace Modules\Ingredient\Admin\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use Modules\Ingredient\Http\Controllers\Admin\IngredientTagAdminController;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientTag as IngredientTagModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Class Recipe tag
 *
 * @property IngredientTagModel $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class IngredientTagAdminSection extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    protected $icon = 'fas fa-tag';

    protected $controllerClass = IngredientTagAdminController::class;

    public function getTitle(): string
    {
        return trans('admin.ingredient_tag.title');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(IngredientsAdminSection::ID)
                ?->addPage((new Page(IngredientTagModel::class))->setPriority(24));
        });
    }

    public function onDisplay(): DisplayInterface
    {
        $table = AdminDisplay::datatables()
            ->with('ingredients')
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
                        trans('common.ingredients'),
                        static function (IngredientTagModel $model) {
                            $model->loadCount('ingredients');
                            return view('admin::collapsedList', [
                                'id'     => "ingredient_tag_$model->id",
                                'titles' => $model->ingredients->pluck('name'),
                                'count'  => $model->ingredients_count,
                            ])->render();
                        }
                    )->setWidth('35%'),
                ]
            )
            ->addScript('ingredientTag.js', mix('js/admin/ingredients/ingredient-tags.js'))
            ->paginate(20);

        $table->getExtension('links')?->setView(view('admin::ingredient.tags.search'));

        return $table;
    }

    /**
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::hidden('id')->setValue(null),
                    AdminFormElement::text('slug', 'Slug')
                        ->required()
                        ->setHtmlAttribute('maxlength', '20')
                        ->setHelpText(
                            trans(
                                'admin_help_text.notification_slug',
                                ['amount' => 20]
                            )
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
                        'ingredients',
                        trans('admin.ingredients.label.all_ingredients'),
                    )
                        ->setModelForOptions(Ingredient::class)
                        ->setSearchUrl(route('admin.search-ingredients.select2', ['all' => 1]))
                        ->setMinSymbols(1)
                        ->setDisplay(static fn(Ingredient $model) => "[$model->id] $model->name")
                        ->setSelect2Options(
                            [
                                'placeholder'  => trans('admin.ingredients.label.select_ingredient'),
                                'ajax--delay'  => 400,
                                'ajax--method' => 'GET',
                            ]
                        )
                        ->setHelpText(trans('admin_help_text.ingredients_help_text'))
                ]
            )
            ->setAction(route('admin.ingredients.tags.store'));
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
                        'ingredients',
                        trans('admin.ingredients.label.all_ingredients'),
                    )
                        ->setModelForOptions(Ingredient::class)
                        ->setSearchUrl(route('admin.search-ingredients.select2', ['all' => 1]))
                        ->setMinSymbols(1)
                        ->setDisplay(static fn(Ingredient $model) => "[$model->id] $model->name")
                        ->setSelect2Options(
                            [
                                'placeholder'  => trans('admin.ingredients.label.select_ingredient'),
                                'ajax--delay'  => 400,
                                'ajax--method' => 'GET',
                            ]
                        )
                        ->setHelpText(trans('admin_help_text.ingredients_help_text'))
                ]
            )
            ->setAction(route('admin.ingredients.tags.store'));
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
