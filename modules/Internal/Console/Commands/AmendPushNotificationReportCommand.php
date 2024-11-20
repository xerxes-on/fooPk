<?php

declare(strict_types=1);

namespace Modules\Internal\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Modules\PushNotification\Action\ReportGroupingAction;
use Modules\PushNotification\Enums\NotificationTypeSlugEnum;
use Modules\PushNotification\Models\Notification;

final class AmendPushNotificationReportCommand extends Command
{
    protected $signature = 'internal:group-notification-report';

    protected $description = 'Optimize push notifications report in DB allowing to save space and remove duplications';

    public function handle(): void
    {
        $groupService = app(ReportGroupingAction::class);
        Notification::cursor()->each(function (Notification $notification) use ($groupService) {
            if (is_null($notification->report)) {
                return;
            }
            $report = $notification->report;

            // clear info of unknown targets
            if (isset($report['info']['unknownTargets'])) {
                unset($report['info']['unknownTargets']);
            }
            // clear errors of invalid tokens if present
            if (!empty($notification->report['errors']) && isset($report['errors']['invalidTokens'])) {
                unset($report['errors']['invalidTokens']);
            }
            // Group duplications
            if (!empty($notification->report['errors'])) {
                $report['errors'] = $groupService->handle($report['errors']);
            }

            $notification->report = $report;
            $notification->save();
        });

        Notification::where('type_id', static function (Builder $query) {
            $query->select('id')
                ->from('notification_types')
                ->where('slug', NotificationTypeSlugEnum::FOODPOINTS_DISTRIBUTION_WEEKLY->value);
        })->delete();
    }
}
