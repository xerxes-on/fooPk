<?php

declare(strict_types=1);

namespace App\Admin\Http\Section;

use AdminForm;
use AdminFormElement;
use App\Models\UserDashboard as ModelUserDashboard;
use Modules\Course\Service\WpApi;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Section;

/**
 * Section UserDashboard
 *
 * @property ModelUserDashboard $model
 * @property ModelUserDashboard|null $model_value
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
final class UserDashboard extends Section implements Initializable
{
    protected $checkAccess = true;

    protected $icon = 'fas fa-table';

    public function getTitle(): string
    {
        return trans('common.user_dashboard');
    }

    /**
     * Initialize class.
     */
    public function initialize(): void
    {
        $this->addToNavigation()->setPriority(120);
    }

    public function onDisplay(): DisplayInterface
    {
        return $this->fireEdit(ModelUserDashboard::first()?->id);
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
                    AdminFormElement::ckeditor('message', trans('common.send_report_placeholder'))->required(),
                    AdminFormElement::view(
                        'admin::dashboard.tab_articles',
                        [
                            'article' => is_null($id) ?
                                collect() :
                                WpApi::getPost((int)$this->model_value->wp_article_id)
                        ]
                    ),
                ]
            )
            ->setAction(route('admin.user.dashboard.update', ['user_dashboard' => $id]));
    }
}
