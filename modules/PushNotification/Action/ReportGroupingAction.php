<?php

declare(strict_types=1);

namespace Modules\PushNotification\Action;

/**
 * Action for grouping report messages.
 *
 * @package Modules\PushNotification\Action
*/
final class ReportGroupingAction
{
    public function handle(array $messages): array
    {
        $result       = [];
        $messageCount = [];

        // Count occurrences of each message
        foreach ($messages as $message) {
            if (is_array($message)) {
                continue;
            }
            if (isset($messageCount[$message])) {
                $messageCount[$message]++;
                continue;
            }
            $messageCount[$message] = 1;
        }

        if (empty($messageCount)) {
            return $messages;
        }

        // Prepare the result format
        foreach ($messageCount as $message => $count) {
            $result[] = [
                'title' => trim($message),
                'count' => $count
            ];
        }

        return $result;
    }
}
