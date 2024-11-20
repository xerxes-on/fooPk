<?php

declare(strict_types=1);

namespace App\Admin\Http\Section\User;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Enums\Admin\Permission\RoleEnum;
use App\Enums\Admin\SectionPagesEnum;
use App\Enums\User\UserStatusEnum;
use App\Models\Admin as AdminModel;
use App\Models\Role;
use App\Models\User;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Section Admins
 *
 * @property \App\Models\Admin $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 */
final class Admins extends Section implements Initializable
{
    protected $checkAccess = true;

    protected $icon = 'fa fa-user-cog';

    public function getTitle(): string
    {
        return trans('common.admins');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(SectionPagesEnum::USERS->value)
                ?->addPage((new Page(AdminModel::class)))
                ?->setPriority(31);
        });
    }

    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->with('roles')
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#')->setWidth(30),
                    AdminColumn::custom(trans('common.name'), static fn(AdminModel $model) => $model->name)->setWidth(60),
                    AdminColumn::text('email', trans('common.email'))->setWidth(100),
                    AdminColumn::lists('roles.name', trans('common.role'))->setWidth(60),
                    AdminColumn::text('created_at', trans('common.registration_date'))->setWidth(60)
                ]
            )
            ->paginate(20);
    }

    /**
     * @throws \SleepingOwl\Admin\Exceptions\Form\Element\SelectException
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit();
    }

    /**
     * @note Autocomplete is set to new-password to prevent browser from autofilling the Email field.
     * @throws \SleepingOwl\Admin\Exceptions\Form\Element\SelectException
     * @throws \Exception
     */
    public function onEdit(?int $id = null): FormInterface
    {
        $roles         = Role::where('name', '!=', 'user')->get(['name', 'id']);
        $passwordField = AdminFormElement::password('new_password', trans('common.password'))->setHtmlAttribute('autocomplete', 'new-password');
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::hidden('id')->setValue($id),
                    AdminFormElement::checkbox('status', trans('Enable User'))
                        ->setExactValue($id ? $this->model_value->status : UserStatusEnum::ACTIVE->value)
                        ->setView(view('admin::custom.switch', ['extraWrapClass' => 'w-25'])),
                    AdminFormElement::text('name', trans('common.name'))->required(),
                    AdminFormElement::text('email', trans('common.email'))->required()->setHtmlAttribute('autocomplete', 'new-password'),
                    $id ? $passwordField : $passwordField->required(),
                    AdminFormElement::select(
                        'role',
                        trans('common.role'),
                        $roles->pluck('name', 'id')->toArray()
                    )
                        ->required(),
                    AdminFormElement::multiselectajax(
                        'liableClients',
                        trans('admin.admins.fields.liable_clients')
                    )
                        ->setModelForOptions(User::class)
                        ->setSearchUrl(route('admin.search-client.select2'))
                        ->setMinSymbols(1)
                        ->setDisplay(fn(User $model) => "[$model->id] $model->full_name")
                        ->setSelect2Options(
                            ['placeholder' => trans('common.select_new_recipe'), 'ajax--delay' => 400]
                        )
                        ->setHelpText(trans('admin_help_text.admins.liable_clients')),
                    AdminFormElement::view(
                        'admin::partials.dependant-list',
                        [
                            'consultantId' => $roles->where('name', RoleEnum::CONSULTANT->value)->first()?->id
                        ]
                    )
                ]
            )
            ->addStyle('switch.css', mix('css/admin/switch.css'))
            ->setAction(route('admin.admin.store'));
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
