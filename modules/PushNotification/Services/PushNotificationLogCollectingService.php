<?php

declare(strict_types=1);

namespace Modules\PushNotification\Services;

use Kreait\Firebase\Messaging\MulticastSendReport;
use Log;

/**
 * Service for collecting and logging information about push notifications.
 *
 * @package Modules\PushNotification\Services
*/
final class PushNotificationLogCollectingService
{
    /**
     * Errors occurred during job execution.
     */
    private array $errors = [];

    /**
     * Info messages occurred during job execution.
     */
    private array $info = [];

    /**
     * Token that successfully received notification.
     */
    private array $successfulTargets = [];

    /**
     * Amount of expected targets for pushes.
     */
    private int $expectedTargets = 0;

    /**
     * Amount of successful send pushes.
     */
    private int $successfulAttempts = 0;

    /**
     * Amount of failed to send pushes.
     */
    private int $failedAttempts = 0;

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function addInfo(string $info): void
    {
        $this->info[] = $info;
    }

    public function hasErrors(): bool
    {
        if ($this->errors === []) {
            return false;
        }

        Log::channel('notifications')
            ->error(
                'Sending notifications failed.' . PHP_EOL,
                $this->errors
            );

        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getInfo(): array
    {
        return $this->info;
    }

    public function collectReportData(MulticastSendReport $report): void
    {
        if ($report->hasFailures()) {
            foreach ($report->failures()->getItems() as $failure) {
                $this->addError((string)$failure->error()?->getMessage());
            }
        }

        $this->successfulAttempts += $report->successes()->count();
        $this->failedAttempts += $report->failures()->count();
        $this->successfulTargets = array_merge($this->successfulTargets, $report->validTokens());

        /**
         *  Unknown tokens are tokens that are valid but not know to the currently
         *  used Firebase project. This can, for example, happen when you are
         *  sending from a project on a staging environment to tokens in a
         *  production environment
         */
        $unknownTargets = $report->unknownTokens();
        if ($unknownTargets !== []) {
            if (empty($this->info['unknownTargets'])) {
                $this->info['unknownTargets'] = [];
            }
            $this->info['unknownTargets'] = array_merge($this->info['unknownTargets'], $report->unknownTokens());
        }

        $invalidTokens = $report->invalidTokens();
        if ($invalidTokens !== []) {
            if (empty($this->errors['invalidTokens'])) {
                $this->errors['invalidTokens'] = [];
            }
            $this->errors['invalidTokens'] = array_merge($this->errors['invalidTokens'], $report->invalidTokens());
        }
    }

    public function saveDebugData(): void
    {
        $this->info[] = sprintf(
            'Expected targets: %s, Targets hit: %s',
            $this->expectedTargets,
            count($this->successfulTargets)
        );
        $this->logDebugInfo();
    }

    /**
     * Log all extra information collected during job dispatching
     */
    public function logDebugInfo(): void
    {
        if ($this->info) {
            Log::channel('notifications')
                ->info(
                    'Info gained through job dispatching' . PHP_EOL,
                    $this->info
                );
        }

        if ($this->errors) {
            Log::channel('notifications')
                ->error(
                    'Errors received through job dispatching' . PHP_EOL,
                    $this->errors
                );
        }
    }

    public function setExpectedTargets(int $value): void
    {
        $this->expectedTargets = $value;
    }

    public function getSuccessfulTargets(): array
    {
        return $this->successfulTargets;
    }

    public function getSuccessfulAttempts(): int
    {
        return $this->successfulAttempts;
    }

    public function logError(string $string): void
    {
        Log::channel('notifications')
            ->error($string . PHP_EOL);
    }
}
