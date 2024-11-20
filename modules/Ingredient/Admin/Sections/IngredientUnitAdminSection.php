<?php

declare(strict_types=1);

namespace Modules\Ingredient\Admin\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use Modules\Ingredient\Enums\IngredientUnitType;
use Modules\Ingredient\Models\IngredientUnit as IngredientUnitModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Class IngredientUnit
 *
 * @property \Modules\Ingredient\Models\IngredientUnit $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class IngredientUnitAdminSection extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    public function getTitle(): string
    {
        return trans('common.ingredient_unit');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(IngredientsAdminSection::ID)
                ?->addPage((new Page(IngredientUnitModel::class))->setPriority(25));
        });
    }

    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth(30),
                    AdminColumn::custom(trans('ingredient::admin.type.title'), static fn(IngredientUnitModel $model) => IngredientUnitType::getNameFor($model->type))
                        ->setWidth(30),
                    AdminColumn::text('full_name', trans('common.full_name'))->setWidth(10),
                    AdminColumn::text('short_name', trans('common.short_name'))->setWidth(30),
                    AdminColumn::text('default_amount', trans('common.default_amount'))->setWidth(30)
                ]
            )
            ->paginate(20);
    }

    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    public function onEdit(): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::text('full_name', trans('common.full_name'))->required(),
                    AdminFormElement::text('short_name', trans('common.short_name'))->required(),
                    AdminFormElement::select('type', trans('ingredient::admin.type.title'), IngredientUnitType::forSelect())
                        ->required(),
                    AdminFormElement::number('default_amount', trans('common.default_amount'))
                        ->setMin(0)
                        ->setStep(0.1)
                        ->setHtmlAttribute('required', 'true')
                        ->required(),
                    AdminFormElement::select('next_unit_id', trans('common.conversion_unit'), $this->model)
                        ->setDisplay('full_name'),
                    AdminFormElement::number('max_value', trans('common.conversion_value'))
                        ->setMin(0)
                        ->setStep(0.1),
                ]
            );
    }
}
