<?php

declare(strict_types=1);

namespace Modules\PushNotification\Jobs;

use Illuminate\Database\Eloquent\Builder;
use Modules\PushNotification\DTO\PushNotificationOptions;
use Modules\PushNotification\Services\PushNotificationLogCollectingService;
use Modules\PushNotification\Services\PushNotificationReportStorageService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Collection as SupportCollection;
use Kreait\Firebase\Messaging\RawMessageFromArray;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Modules\PushNotification\Enums\NotificationSettingsEnum;
use Modules\PushNotification\Enums\UserGroupOptionEnum;
use Modules\PushNotification\Models\Notification as NotificationModel;
use Modules\PushNotification\Models\UserDevice;
use Throwable;

/**
 * Job the send push notifications.
 *
 * @package Modules\PushNotification
 */
final class SendPushNotifications implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Notification model.
     */
    private ?NotificationModel $notification = null;

    private PushNotificationLogCollectingService $logger;
    private PushNotificationReportStorageService $reportStorage;

    /**
     * Create a new job instance.
     */
    public function __construct(int $notificationID, private readonly PushNotificationOptions $options)
    {
        $this->logger = new PushNotificationLogCollectingService();
        $this->prepareNotification($notificationID);
    }

    /**
     * Prepare Notification model and check its status
     */
    private function prepareNotification(int $notificationID): void
    {
        try {
            $this->notification  = NotificationModel::with('type')->findOrFail($notificationID);
            $this->reportStorage = new PushNotificationReportStorageService($this->logger, $this->notification, $this->options->isSilent());
        } catch (Throwable $e) {
            $this->logger->addError($e->getMessage());
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Perform initial validation. Prevent further execution if errors occurred.
        if ($this->logger->hasErrors()) {
            $this->saveReport();
            return;
        }

        // 2. Group users by language as per notification distribution settings and check if everything is ok.
        $userNDevices = $this->getUsersWithDevices();

        // 3. Check whether we have data to work with. Prevent further execution.
        if ($this->logger->hasErrors() || is_null($userNDevices)) {
            $this->saveReport();
            return;
        }

        // 4. Form messages for each language group.
        $messages = $userNDevices->map(fn(Collection $item) => $this->prepareMessage($item));

        // 5. Check whether any critical errors have occurred. Prevent further execution.
        if ($this->logger->hasErrors()) {
            $this->saveReport();
            return;
        }
        $this->logger->setExpectedTargets((int)$messages->reduce(fn(?int $amount, array $item) => $amount + count($item['tokens'])));

        // 6. Sending messages
        $messaging = Firebase::messaging();
        $messages->each(
            function (array $item, string $locale) use ($messaging) {
                if (count($item['tokens']) > $messaging::BATCH_MESSAGE_LIMIT) {
                    $message = $item['message'];
                    collect($item['tokens'])
                        ->chunk($messaging::BATCH_MESSAGE_LIMIT)
                        ->each(function (SupportCollection $batch) use ($messaging, $message) {
                            try {
                                $report = $messaging->sendMulticast($message, $batch->toArray());
                                $this->logger->collectReportData($report);
                            } catch (Throwable $e) {
                                $this->logger->addError($e->getMessage());
                                return;
                            }

                            $this->logger->collectReportData($report);
                        });

                    // We have already sent everything, so we can return.
                    return;
                }

                try {
                    $report = $messaging->sendMulticast($item['message'], $item['tokens']);
                } catch (Throwable $e) {
                    $this->logger->addError($e->getMessage());
                    return;
                }

                $this->logger->collectReportData($report);
            }
        );

        // 7. Log debug data
        $this->logger->saveDebugData();
        $this->logger->logDebugInfo();

        // 8. Check whether notification is sent to any user. Prevent further execution.
        if ($this->logger->getSuccessfulAttempts() === 0) {
            $this->logger->logError('No successful targets hit for notification ' . $this->notification->id);
            $this->logger->addError('No successful targets hit for notification ' . $this->notification->id);
            $this->saveReport();
            return;
        }

        // 8. Update user notifications data
        $this->reportStorage->prepareAndStoreReport($userNDevices, $this->options->toArray());
    }

    /**
     * Get users with devices grouped by language as passed from params.
     */
    private function getUsersWithDevices(): ?SupportCollection
    {
        $userNDevices = UserDevice::with(['user', 'user.pushNotifications'])
            ->when(
                $this->options->getUsersId() !== [],
                fn(Builder $query) => $query->whereIn('user_id', $this->options->getUsersId())
            )
            ->get()
            ->mapToGroups(fn(UserDevice $item) => [$item->user->lang => $item]);

        switch ($this->options->getLanguage()) {
            case UserGroupOptionEnum::ALL->value:
                break; // We preserve the original collection
            case UserGroupOptionEnum::DE->value:
                $userNDevices = $userNDevices->only([UserGroupOptionEnum::DE->value]);
                break;
            case UserGroupOptionEnum::EN->value:
                $userNDevices = $userNDevices->only([UserGroupOptionEnum::EN->value]);
                break;
            default:
                $this->logger->addError('Unable to get user devices. Unknown dispatch group option.');
                return null;
        }

        // Occasionally the collection can be empty. No need to run further.
        if ($userNDevices->isEmpty()) {
            $this->logger->addError('Unable to get user devices. Empty collection.');
            return null;
        }

        return $userNDevices;
    }

    /**
     * Prepare targets (tokens) with message data for sending.
     */
    private function prepareMessage(Collection $collection): array
    {
        $data = $collection->map(
            function (UserDevice $item) {
                $messageContent = $this->getRawMessageContent($item->user);
                return [
                    'token'   => $item->token,
                    'user'    => $item->user->id,
                    'message' => is_null($messageContent) ? null : new RawMessageFromArray($messageContent)
                ];
            }
        )->filter(fn(array $item) => !is_null($item['message']));

        $message = $data->unique('message')->first();

        if (is_null($message)) {
            $message = ['message' => null];
            $this->logger->addError('No message content. Nothing to send.');
        }

        return [
            'tokens'  => $data->pluck('token')->unique()->toArray(),
            'message' => $message['message']
        ];
    }

    /**
     * @note return null if message should not be sent to specific user
     */
    private function getRawMessageContent(User $user): ?array
    {
        // User do not want to receive any notifications
        if ($user->push_notifications === NotificationSettingsEnum::DISABLE->value) {
            return null;
        }

        // User already received this message
        if ($user->pushNotifications->contains('notification_id', $this->notification->id)) {
            $this->logger->addInfo("User [$user->id] already received this notification");
            return null;
        }

        // User wants to receive only important notifications
        if ($user->push_notifications === NotificationSettingsEnum::IMPORTANT->value && !$this->notification->type->is_important) {
            return null;
        }

        /**
         * Here we form base message content. We try to get user`s language translation, if not found, then we prevent message from being sent.
         * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#notification
         */
        $messageContent = [
            'title' => $this->notification->translations->where('locale', $user->lang)->first()?->title,
            'body'  => $this->notification->translations->where('locale', $user->lang)->first()?->content
        ];

        // Accidentally translations can be missing.
        if (is_null($messageContent['title']) || is_null($messageContent['body'])) {
            $this->logger->addError("Notification [{$this->notification->id}] missing translation for [$user->lang] locale");
            return null;
        }

        // Notification link content
        $link = null;
        if ($this->notification->link) {
            $link = [
                'url'   => $this->notification->link,
                'title' => $this->notification->translations->where('locale', $user->lang)->first()?->link_title
            ];
        }

        // Accidentally some data can be missing. Ensure it won't happen
        if (!is_null($link) && (is_null($link['url']) || is_null($link['title']))) {
            $this->logger->addError("Missing attributes 'url|title' for [{$this->notification->id}] link");
            return null;
        }

        /**
         * @note Target (token) is not required here as we will be sending multicast messages.
         * @note Data should not contain complex structure. Only simple values are allowed.
         */
        return [
            'data' => [
                'notification_id'         => $this->notification->id,
                'notification_type_id'    => $this->notification->type->id,
                'notification_type_slug'  => $this->notification->type->slug,
                'notification_type_title' => $this->notification->type->name,
                'notification_link_title' => $link['title'] ?? null,
                'notification_link_url'   => $link['url'] ?? null,
                'icon'                    => asset($this->notification->type->icon->url('icon')),
                'date'                    => (string)now()->getTimestamp(),
            ],

            /*
             * Base notification content for all devices
             */
            'notification' => $messageContent,

            /**
             * Android config
             * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig
             */
            'android' => [
                'ttl' => '259200s',
                // 3 days to keep the message in FCM in case the device is offline (3600 * 24 * 3)
                'priority'     => $this->notification->type->is_important ? 'HIGH' : 'NORMAL',
                'notification' => $messageContent,
                // Additional parameters for android can be merged here
            ],
            /**
             * Apple config
             * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#apnsconfig
             */
            'apns' => [
                'headers' => [
                    /**
                     * @notes apns-priority values
                     * 10 - send immediately,
                     * 5 - send at a time that takes into account power considerations for the device,
                     * 1 - prioritize the device’s power considerations over all other factors for delivery, and prevent awakening the device.
                     */
                    'apns-priority' => $this->notification->type->is_important ? '10' : '5',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => $messageContent, // Additional parameters for android can be merged here
                    ],
                ],
                /**
                 * Extra FCM fields
                 * @see https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages?hl=ru#fcmoptions
                 */
                //'fcm_options' => [
                //    'image' => 'https://via.placeholder.com/150/00a65a/ffffff/?text=Test'
                //]
            ],
        ];
    }

    private function saveReport(): void
    {
        $this->reportStorage->saveReport($this->options->toArray());
    }
}
