<?php

declare(strict_types=1);

namespace Modules\Ingredient\Admin\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Diet;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Class Diets
 *
 * @property Diet $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class IngredientDietsAdminSection extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = true;

    public function getTitle(): string
    {
        return trans('common.ingredients_diets');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(IngredientsAdminSection::ID)
                ?->addPage((new Page(Diet::class))->setPriority(24));
        });
    }

    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth('5%'),
                    AdminColumn::text('slug', trans('common.slug'))->setWidth('10%'),
                    AdminColumn::text('name', trans('common.name'))->setWidth('80%')
                ]
            )
            ->paginate(20);
    }

    public function onCreate(): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::text('slug', trans('common.slug'))->setHelpText(
                        trans(
                            'admin_help_text.notification_slug',
                            ['amount' => 30]
                        )
                    )->setHtmlAttribute('maxlength', '30')->required(),
                    AdminFormElement::text('name', trans('common.name'))->required()
                ]
            )
            ->setAction(route('admin.diets.store'));
    }

    public function onEdit(?int $id = null): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::text('name', trans('common.name'))->required()
                ]
            );
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
