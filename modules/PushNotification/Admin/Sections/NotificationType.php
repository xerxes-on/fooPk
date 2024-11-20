<?php

declare(strict_types=1);

namespace Modules\PushNotification\Admin\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use Modules\PushNotification\Http\Controllers\Admin\NotificationTypeAdminController;
use Modules\PushNotification\Models\NotificationType as NotificationTypeModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Section Notification Type
 *
 * @property NotificationTypeModel $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 *
 * @package Modules\PushNotification\Admin
 */
final class NotificationType extends Section implements Initializable
{
    protected $checkAccess = true;

    protected $controllerClass = NotificationTypeAdminController::class;

    public function getIcon(): string
    {
        return 'far fa-comment';
    }

    public function getTitle(): string
    {
        return trans('PushNotification::admin.notification_type');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->getNavigationPage(Notification::ID)
                ?->addPage((new Page(NotificationTypeModel::class))->setPriority(92));
        });
    }

    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::table()
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#'),
                    AdminColumn::text('slug', trans('common.slug')),
                    AdminColumn::text('name', trans('common.title')),
                    AdminColumn::custom(
                        trans('PushNotification::admin.important'),
                        static fn(NotificationTypeModel $model) => $model->is_important ?
                            '<span class="fas fa-check" aria-hidden="true"></span>' :
                            '<span class="fas fa-times" aria-hidden="true"></span>'
                    ),
                    AdminColumn::text('created_at', trans('common.created_at')),
                    AdminColumn::text('updated_at', trans('common.updated_at'))
                ]
            )
            ->paginate(20);
    }

    /**
     * @throws \Exception
     */
    public function onCreate(): FormInterface
    {
        return $this->onEdit(null);
    }

    /**
     * @throws \Exception
     */
    public function onEdit(?int $id): FormInterface
    {
        return AdminForm::panel()
            ->addBody(
                [
                    AdminFormElement::hidden('id')->setValue($id),
                    AdminFormElement::image('icon', trans('common.image'))
                        ->setView(
                            view(
                                'admin::custom.image',
                                [
                                    'hint' => trans(
                                        'admin_help_text.notification_icon',
                                        [
                                            'type' => custom_implode(['jpg', 'png', 'jpeg'], '</b>, <b>', '<b>', '</b>'),
                                            'size' => '<b>100KB</b>',
                                            'link' => 'https://tinypng.com/'
                                        ]
                                    )
                                ]
                            )
                        ),
                    AdminFormElement::checkbox('is_important', trans('PushNotification::admin.important'))
                        ->setHelpText(trans('admin_help_text.notification_important')),
                    AdminFormElement::text('slug', trans('common.slug'))
                        ->required()
                        ->setHtmlAttribute('maxlength', '20')
                        ->setHelpText(trans('admin_help_text.notification_slug', ['amount' => 20])),
                    AdminFormElement::text('name', trans('common.title'))
                        ->required()
                        ->setHtmlAttribute('maxlength', '60')
                        ->setHelpText(trans('admin_help_text.notification_name', ['amount' => 60])),
                ]
            )
            ->setAction(route('admin.notifications.type.store'))
            ->addStyle('customImage.css', mix('css/admin/customImage.css'))
            ->addScript('elfinderPopupImage.js', mix('js/admin/elfinderPopupImage.js'));
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
