<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\Recipe;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Admin\Http\Section\SectionWithImage;
use App\Models\Recipe as RecipeModel;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;

/**
 * Monthly Recipe Distribution configuration class.
 *
 * @property \App\Models\RecipeDistribution $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class RecipeDistribution extends SectionWithImage implements Initializable
{
    protected $checkAccess = true;

    public function getTitle(): string
    {
        return trans('common.recipe_distribution');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(Recipe::ID)
                ?->addPage((new Page(\App\Models\RecipeDistribution::class))->setPriority(14));
        });
    }

    /**
     * view grid Ingredients entity
     */
    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::datatables()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setOrderable()->setWidth(5),
                    AdminColumn::lists('recipes', trans('common.recipes'))->setWidth(50),
                    AdminColumn::text('comment', 'Comments')->setWidth(50),
                    AdminColumn::text('updated_at', 'Updated at')->setOrderable()->setWidth(10),
                ]
            )
            ->paginate(20);
    }

    /**
     * create Ingredients entity
     *
     * @throws \SleepingOwl\Admin\Exceptions\Form\Element\SelectException
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * Edit Ingredients entity
     *
     * @throws \SleepingOwl\Admin\Exceptions\Form\Element\SelectException
     */
    public function onEdit(?int $id = null): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::hidden('id', $id),
                    AdminFormElement::multiselectajax(
                        'recipes',
                        trans('common.recipes'),
                    )
                        ->setModelForOptions(RecipeModel::class)
                        ->setSearchUrl(route('admin.search-recipes.select2', ['excludedId' => $id ?? 0]))
                        ->setMinSymbols(1)
                        ->setDisplay(fn(RecipeModel $model) => "[$model->id] $model->title")
                        ->setSelect2Options(
                            ['placeholder' => trans('common.select_new_recipe'), 'ajax--delay' => 400]
                        )
                        ->setHelpText(trans('admin_help_text.related_recipes_help_text'))
                        ->required(),
                    AdminFormElement::textarea('comment', 'Admin comments')
                        ->setHelpText('Add notes to the distribution'),
                ]
            )
            ->setAction(route('admin.recipe-distribution.store'));
    }

    /**
     * Prevent deletion if distribution is done.
     */
    public function isDeletable(Model $model): bool
    {
        return !$model->is_distributed;
    }

    /**
     * Prevent editing if distribution is done.
     */
    public function isEditable(Model $model): bool
    {
        return !$model->is_distributed;
    }
}
