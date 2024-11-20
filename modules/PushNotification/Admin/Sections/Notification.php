<?php

declare(strict_types=1);

namespace Modules\PushNotification\Admin\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Enums\Admin\Permission\PermissionEnum;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\PushNotification\Http\Controllers\Admin\NotificationAdminController;
use Modules\PushNotification\Models\{NotificationType};
use Modules\PushNotification\Models\Notification as NotificationModel;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Navigation\Page;
use SleepingOwl\Admin\Section;

/**
 * Section Notification
 *
 * @property NotificationModel $model
 *
 * @see http://sleepingowladmin.ru/docs/model_configuration_section
 *
 * @package Modules\PushNotification\Admin
 */
final class Notification extends Section implements Initializable
{
    public const ID = 'notification_section';

    protected $checkAccess = true;

    protected $controllerClass = NotificationAdminController::class;

    protected $icon = 'fas fa-bell';

    public function getTitle(): string
    {
        return trans('PushNotification::admin.notifications');
    }

    public function initialize(): void
    {
        app()->booted(function () {
            $this->addToNavigation()
                ->setPriority(90)
                ->setId(self::ID)
                ->setPages(function (Page $page) {
                    $page->addPage(
                        (new Page(NotificationModel::class))
                            ->setPriority(91)
                            ->setIcon('fas fa-comment-medical')
                    );
                });
        });
    }

    /**
     * @throws \Exception
     */
    public function onDisplay(): DisplayInterface
    {
        return AdminDisplay::datatables()
            ->with('type')
            ->setHtmlAttribute('class', 'table-primary')
            ->setColumns(
                [
                    AdminColumn::text('id', '#'),
                    AdminColumn::text('type.name', trans('PushNotification::admin.notification_type'))->setOrderable(false),
                    AdminColumn::text('title', trans('common.title'))->setOrderable(false),
                    AdminColumn::text('created_at', trans('common.created_at')),
                    AdminColumn::text('updated_at', trans('common.updated_at')),
                    AdminColumn::custom(
                        trans('common.action'),
                        static function (NotificationModel $model) {
                            if (!Auth::user()?->hasPermissionTo(PermissionEnum::DISPATCH_NOTIFICATIONS->value)) {
                                return '<span class="fas fa-times" aria-hidden="true"></span>';
                            }
                            return $model->dispatched ?
                                view('PushNotification::admin.details_modal', ['model' => $model])->render() :
                                sprintf(
                                    '<button type="button" class="btn btn-warning js-notification-dispatch" data-route="%s" data-action="%s">%s</button>',
                                    route('admin.notifications.config', ['notificationId' => $model->id]),
                                    route('admin.notifications.dispatch'),
                                    trans('PushNotification::admin.dispatch')
                                );
                        }
                    )->setOrderable(
                        static fn(Builder $query, string $direction) => $query->orderBy('dispatched', $direction)
                    ),
                    AdminColumn::custom(
                        trans('PushNotification::admin.notification_report.label'),
                        static fn(NotificationModel $model) => (
                            Auth::user()?->hasPermissionTo(PermissionEnum::DISPATCH_NOTIFICATIONS->value) &&
                            !is_null($model->report)
                        ) ?
                            view('PushNotification::admin.report', ['model' => $model])->render() :
                            ''
                    )->setOrderable(false)
                ]
            )
            ->setOrder([[0, 'DESC']])
            ->addScript('notifications', mix('js/admin/notifications.js'))
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
                    AdminFormElement::select('type_id', trans('PushNotification::admin.notification_type'), NotificationType::class)
                        ->setDisplay(
                            fn(NotificationType $model) => "($model->slug) $model->name"
                        )
                        ->required()
                        ->setHelpText(trans('admin_help_text.notification_type')),
                    AdminFormElement::view(
                        'admin::partials.tabbed_translations',
                        [
                            'attribute' => 'title',
                            'label'     => trans('common.title'),
                            'helptext'  => trans('admin_help_text.notification_attribute', ['attribute' => 'title'])
                        ]
                    ),
                    AdminFormElement::view(
                        'admin::partials.tabbed_translations',
                        [
                            'attribute' => 'content',
                            'label'     => trans('common.content'),
                            'type'      => 'textarea',
                            'helptext'  => trans('admin_help_text.notification_attribute', ['attribute' => 'content'])
                        ]
                    ),
                    AdminFormElement::view('PushNotification::admin.link', []),
                ]
            )
            ->setAction(route('admin.notifications.store'));
    }

    public function isEditable(Model $model): bool
    {
        return !$model->dispatched;
    }

    public function onDelete(): void
    {
        $this->model->delete();
    }
}
