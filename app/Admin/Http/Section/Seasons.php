<?php

declare(strict_types=1);

namespace App\Admin\Http\Section;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Seasons as SeasonsModel;
use Modules\Ingredient\Admin\Sections\IngredientsAdminSection;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Class Seasons
 *
 * @property \App\Models\Seasons $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class Seasons extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    public function getTitle(): string
    {
        return trans('common.ingredient_season');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(IngredientsAdminSection::ID)
                ?->addPage((new Page(SeasonsModel::class))->setPriority(26));
        });
    }

    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth(30),
                    AdminColumn::text('name', trans('common.name'))->setWidth(30),
                    AdminColumn::text('key', trans('common.slug'))->setWidth(30)
                ]
            )->paginate(20);
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
                    AdminFormElement::text('name', trans('common.name'))->required(),
                    AdminFormElement::text('key', trans('common.slug'))->required()
                ]
            );
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
