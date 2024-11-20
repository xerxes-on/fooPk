<?php

declare(strict_types=1);

namespace App\Admin\Services;

use App\Exceptions\NoData;
use App\Jobs\ActionsAfterChangingFormular;
use App\Jobs\AddingNewRecipes;
use App\Jobs\AutomationUserCreation;
use App\Jobs\PreliminaryCalculation;
use App\Jobs\RecalculateRecipes;
use App\Models\Job;
use Carbon\Carbon;
use Throwable;

/**
 * Service for checking job
 *
 * @package App\Admin\Services
 */
class JobCheckService
{
    /**
     * Check job run
     *
     * @param class-string $payloadDisplayName
     * @param string $paramName
     * @param string|int $paramValue
     * @return bool
     */
    public function checkJobRun(string $payloadDisplayName, string $paramName, string|int $paramValue): bool
    {
        $result = false;
        Job::whereJsonContains('payload->data->commandName', $payloadDisplayName)
            ->get(['payload'])->each(function (Job $job) use ($paramName, $paramValue, &$result) {
                try {
                    $payload = json_decode($job->payload, true, 512, JSON_THROW_ON_ERROR);
                    $jobData = unserialize($payload['data']['command'], ['allowed_classes' => true]);
                    $prop    = $jobData->getProperty($paramName);
                } catch (Throwable) {
                    return;
                }

                if ($prop == $paramValue) {
                    $result = true;
                }
            });

        return $result;
    }

    /**
     * Get recalculation jobs related to user
     * @throws NoData
     */
    public function getUserRecalculationJobs(int $userId): array
    {
        $jobs = Job::whereJsonContains('payload->displayName', PreliminaryCalculation::class)
            ->orWhereJsonContains('payload->displayName', AddingNewRecipes::class)
            ->orWhereJsonContains('payload->displayName', RecalculateRecipes::class)
            ->orWhereJsonContains('payload->displayName', ActionsAfterChangingFormular::class)
            ->orWhereJsonContains('payload->displayName', AutomationUserCreation::class)
            ->get();
        $availableJobs = [];

        if ($jobs->isEmpty()) {
            throw new NoData('Recalculation done');
        }

        foreach ($jobs as $job) {
            try {
                $payload = json_decode($job->payload, true, 512, JSON_THROW_ON_ERROR);
                $jobData = unserialize($payload['data']['command'], ['allowed_classes' => true]);
                $user    = $jobData->getProperty('user');
            } catch (Throwable) {
                continue;
            }

            if ($user->id !== $userId) {
                continue;
            }

            $message = '';
            switch ($payload['displayName']) {
                case PreliminaryCalculation::class:
                    $message = 'Preliminary calculations';
                    break;
                case AddingNewRecipes::class:
                    $message = 'Adding new recipes';
                    break;
                case RecalculateRecipes::class:
                    $message = 'Recalculate recipes';
                    break;
                case ActionsAfterChangingFormular::class:
                    $message = 'Recalculations after changing questionnaire';
                    break;
            }
            $availableJobs[] = [
                'id'     => $job->id,
                'status' => $job->attempts > 0 ? "$message job is in progress. Started at " : "$message job is in queue. Starting at ",
                'time'   => $job->attempts > 0 ? Carbon::createFromTimestamp($job->reserved_at)->toDateTimeString() : Carbon::createFromTimestamp($job->available_at)->toDateTimeString()
            ];
        }

        if (empty($availableJobs)) {
            throw new NoData('Recalculation done');
        }

        return $availableJobs;
    }
}
