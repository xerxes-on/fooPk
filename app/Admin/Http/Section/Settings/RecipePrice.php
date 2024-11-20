<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\Settings;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Enums\Admin\SectionPagesEnum;
use App\Models\RecipePrice as RecipePriceModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Class RecipePriceCategory
 *
 * @property \App\Models\RecipePrice $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class RecipePrice extends Section implements Initializable
{
    protected $checkAccess = true;

    public function getTitle(): string
    {
        return trans('common.recipe_price');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(SectionPagesEnum::SETTINGS->value)
                ?->addPage(new Page(RecipePriceModel::class))
                ?->setPriority(104);
        });
    }

    /**
     * view grid RecipePrice entity
     */
    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth(30),
                    AdminColumn::text('title', trans('common.title'))->setWidth(30),
                    AdminColumn::text('min_price', trans('common.min_price'))->setWidth(30),
                    AdminColumn::text('max_price', trans('common.max_price'))->setWidth(30)
                ]
            )
            ->paginate(20);
    }

    /**
     * create RecipePrice entity
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * edit RecipePrice entity
     */
    public function onEdit(): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::text('title', trans('common.title'))->required(),
                    AdminFormElement::number('min_price', trans('common.min_price'))
                        ->setMin(0)
                        ->setStep(0.1),
                    AdminFormElement::number('max_price', trans('common.max_price'))
                        ->setMin(0)
                        ->setStep(0.1),
                ]
            );
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
