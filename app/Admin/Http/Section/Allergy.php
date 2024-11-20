<?php

declare(strict_types=1);

namespace App\Admin\Http\Section;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\{Allergy as AllergyModel, AllergyTypes, Diet};
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Section;

/**
 * Class Allergy
 *
 * @property \App\Models\Allergy $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class Allergy extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    protected $icon = 'fas fa-disease';

    public function getTitle(): string
    {
        return trans('common.disease_and_allergy');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->addToNavigation(70);
        });
    }

    /**
     * view grid Allergy entity
     */
    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->with(['type', 'ingredientCategories', 'allowedDiets', 'ingredients'])
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth(30),
                    AdminColumn::text('name', trans('common.name'))->setWidth(30),
                    AdminColumn::custom(
                        trans('common.type'),
                        static fn(AllergyModel $model) => trans("common.{$model->type->name}")
                    )->setWidth(30),
                    AdminColumn::lists('ingredientCategories.name', trans('common.excluded_category'))
                        ->setWidth(30),
                    AdminColumn::lists('allowedDiets.name', trans('common.allowed_diets'))
                        ->setWidth(30),
                    AdminColumn::lists('ingredients.name', trans('common.excluded_ingredients'))
                        ->setWidth(30)
                ]
            )
            ->paginate(20);
    }

    /**
     * create Allergy entity
     *
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * edit Allergy entity
     *
     * @throws \Exception
     */
    public function onEdit(?int $id = null)
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::hidden('id')->setValue($id),
                    AdminFormElement::text('name', trans('common.name'))->required(),
                    AdminFormElement::text('slug', trans('common.slug'))->required(),
                    AdminFormElement::select(
                        'type_id',
                        trans('common.type'),
                        AllergyTypes::pluck('name', 'id')->toArray()
                    )
                        ->required(),
                    view(
                        'admin::allergy.categorySelect',
                        [
                            'ingredient_categories' => IngredientCategory::get(),
                            'allergy'               => is_null($id) ? null : $this->model_value,
                        ]
                    ),
                    AdminFormElement::multiselect('allowedDiets', trans('common.allowed_diets'), Diet::class)
                        ->setDisplay('name'),
                    AdminFormElement::multiselect(
                        'ingredients',
                        trans('common.excluded_ingredients'),
                        Ingredient::class
                    )
                        ->setDisplay('name'),
                ]
            )
            ->addScript('ingredientCategorySelect.js', mix('js/admin/ingredientCategorySelect.js'))
            ->setAction(route('admin.allergies.store'));
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
