<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\Settings;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Admin\Http\Section\SectionWithImage;
use App\Enums\Admin\SectionPagesEnum;
use App\Models\Inventory as InventoryModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;

/**
 * Section Inventory
 *
 * @property \App\Models\Inventory $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class Inventory extends SectionWithImage implements Initializable
{
    protected $checkAccess = true;

    protected $icon = 'fas fa-boxes';

    public function getTitle(): string
    {
        return trans('common.inventory');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(SectionPagesEnum::SETTINGS->value)
                ?->addPage((new Page(InventoryModel::class)))
                ->setPriority(101);
        });
    }

    /**
     * view grid Inventory entity
     */
    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth('5%'),
                    AdminColumn::text('title', trans('common.title'))->setWidth('40%'),
                    AdminColumn::text('tags', trans('common.tags'))->setWidth('40%')
                ]
            )
            ->paginate(20);
    }

    /**
     * create Inventory entity
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * edit Inventory entity
     * @throws \Exception
     */
    public function onEdit(?int $id = null): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::text('title', trans('common.title'))->required(),
                    AdminFormElement::text('tags', trans('common.tags'))->required(),
                ]
            );
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
