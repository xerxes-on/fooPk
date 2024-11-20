<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\Settings;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Enums\Admin\SectionPagesEnum;
use App\Models\{Vitamin};
use Modules\Ingredient\Models\IngredientVitamin;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Section Vitamins
 *
 * @property \App\Models\Vitamin $model
 *
 * @see https://github.com/populov/SleepingOwlAdmin-docs/blob/master/model_configuration_section.md
 */
final class Vitamins extends Section implements Initializable
{
    protected $checkAccess = true;

    protected $icon = 'fas fa-pills';

    public function getTitle(): string
    {
        return trans('common.vitamins');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(SectionPagesEnum::SETTINGS->value)
                ?->addPage(new Page(Vitamin::class))
                ?->setPriority(102);
        });
    }

    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth(30),
                    AdminColumn::text('name', trans('common.vitamin_name'))->setWidth(30)
                ]
            )->paginate(20);
    }

    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    public function onEdit(?int $id = null): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::hidden('id')->setValue($id),
                    AdminFormElement::text('name', trans('common.vitamin_name'))->required(),
                ]
            )
            ->setAction(route('admin.vitamins.store'));
    }

    public function onDelete(int $id): void
    {
        (new IngredientVitamin())->whereVitaminId($id)->delete();
        $this->model->delete();
    }
}
