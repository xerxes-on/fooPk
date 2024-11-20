<?php

declare(strict_types=1);

namespace Modules\PushNotification\Services;

use Illuminate\Support\Collection as SupportCollection;
use Modules\PushNotification\Action\InvalidTokensRemoveAction;
use Modules\PushNotification\Action\ReportGroupingAction;
use Modules\PushNotification\Models\Notification as NotificationModel;
use Modules\PushNotification\Models\UserDevice;
use Modules\PushNotification\Models\UserNotification;

/**
 * Service for handling storage push notification reports.
 *
 * @package Modules\PushNotification\Services
*/
final class PushNotificationReportStorageService
{
    /**
     * Limit of data to insert into database at once.
     */
    private int $insertLimit = 10_000;

    public function __construct(private PushNotificationLogCollectingService $logger, private NotificationModel $notificationModel, private readonly bool $isSilent = false)
    {
    }

    /**
     * Save job report to database.
     */
    public function saveReport(array $params): void
    {
        // this approach allows us to handle invalid tokens and unknown targets despite the fact that we are not saving the report
        $errors = $this->prepareErrorsForSaving();
        $info   = $this->prepareInfoForSaving();

        if ($this->isSilent) {
            return;
        }
        $this->notificationModel->report = [
            'targets_hit' => $this->logger->getSuccessfulAttempts(),
            'errors'      => $errors,
            'info'        => $info,
            'params'      => $params,
        ];
        $this->notificationModel->save();
    }

    public function prepareAndStoreReport(SupportCollection $userNDevices, array $params): void
    {
        if ($this->isSilent) {
            $this->saveReport($params);
            return;
        }

        $userNotificationData = $userNDevices
            ->flatten(1) // stripping language groups
            ->map(
                fn(UserDevice $device) => (in_array($device->token, $this->logger->getSuccessfulTargets())) ?
                    ['user_id' => $device->user_id, 'notification_id' => $this->notificationModel->id] :
                    null
            )
            ->filter(fn(?array $value) => !is_null($value));

        if ($userNotificationData->count() > $this->insertLimit) {
            $userNotificationData->chunk($this->insertLimit)->each(
                fn(SupportCollection $chunk) => $chunk->each(fn(array $item) => UserNotification::updateOrCreate($item, $item))
            );
            $this->saveReport($params);
            return;
        }

        $userNotificationData->each(fn(array $item) => UserNotification::updateOrCreate($item, $item));
        $this->saveReport($params);
    }

    private function prepareErrorsForSaving(): array
    {
        $errors = $this->logger->getErrors();
        if (isset($errors['invalidTokens'])) {
            app(InvalidTokensRemoveAction::class)->handle($errors['invalidTokens']);
            unset($errors['invalidTokens']);
        }
        return app(ReportGroupingAction::class)->handle($errors);
    }

    private function prepareInfoForSaving(): array
    {
        $info = $this->logger->getInfo();
        if (isset($info['unknownTargets'])) {
            app(InvalidTokensRemoveAction::class)->handle($info['unknownTargets']);
            unset($info['unknownTargets']);
        }
        return $info;
    }
}
