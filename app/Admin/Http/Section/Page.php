<?php

declare(strict_types=1);

namespace App\Admin\Http\Section;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Section;

/**
 * Section Page
 *
 * @property \App\Models\Page $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class Page extends Section implements Initializable
{
    protected $checkAccess = true;

    protected $icon = 'fas fa-table';

    public function getTitle(): string
    {
        return trans('common.pages');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->addToNavigation(110);
        });
    }

    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#'),
                    AdminColumn::text('title', trans('common.title')),
                    AdminColumn::text('slug', 'URL Key'),
                    AdminColumn::text('created_at', trans('common.created_at')),
                    AdminColumn::text('updated_at', trans('common.updated_at'))
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
                    AdminFormElement::text('title', trans('common.title'))->required(),
                    AdminFormElement::text('slug', trans('common.slug'))->required(),
                    AdminFormElement::ckeditor('content', trans('common.content'))
                ]
            );
    }
}
