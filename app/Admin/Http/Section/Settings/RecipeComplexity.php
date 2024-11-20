<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\Settings;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Enums\Admin\SectionPagesEnum;
use App\Models\RecipeComplexity as RecipeComplexityModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Section RecipeComplexity
 *
 * @property \App\Models\RecipeComplexity $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class RecipeComplexity extends Section implements Initializable
{
    protected $checkAccess = true;

    public function getTitle(): string
    {
        return trans('common.recipe_complexity');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(SectionPagesEnum::SETTINGS->value)
                ?->addPage(new Page(RecipeComplexityModel::class))
                ?->setPriority(103);
        });
    }

    /**
     * view grid RecipeComplexity entity
     */
    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth(30),
                    AdminColumn::text('title', trans('common.title'))->setWidth(30)
                ]
            )
            ->paginate(20);
    }

    /**
     * create RecipeComplexity entity
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * edit RecipeComplexity entity
     */
    public function onEdit(): FormInterface
    {
        return AdminForm::panel()->addBody(
            [
                AdminFormElement::text('title', trans('common.title'))->required(),
            ]
        );
    }
}
