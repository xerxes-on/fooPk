<?php

namespace Modules\Internal\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * If release flag-file exists command postpone all jobs and stop supervisor
 *
 * @internal
 *
 * @package App\Console\Commands
 */
final class ReleaseQueueTool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internal_release_queue_tool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'If release flag-file exists command postpone all jobs and stop supervisor';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $enable_releases_file_flag = config('releases.enable_releases_file_flag');


        if (!$enable_releases_file_flag) {
            return Command::INVALID;
        }

        $configQueueDefault                 = config('queue.default');
        $deployment_start_file_trigger_path = config('releases.deployment_start_file_trigger_path');
        $deployment_ready_file_trigger_path = config('releases.deployment_ready_file_trigger_path');

        if (file_exists($deployment_ready_file_trigger_path)) {
            File::delete($deployment_ready_file_trigger_path);
        }


        if ($configQueueDefault != 'database') {
            return Command::INVALID;
        }

        if (!file_exists($deployment_start_file_trigger_path)) {
            return Command::INVALID;
        }

        $sleepInterval = config('releases.sleep_interval');
        $jobTimeBuffer = config('releases.job_time_buffer');
        $jobTimeShift  = config('releases.job_time_shift');

        $currentTime = time();
        $timeWindow  = $currentTime + $jobTimeBuffer;
        $timeShifted = $currentTime + $jobTimeShift;

        // if config enabled and file exists

        Job::where('attempts', 0)
            ->where('available_at', '<', $timeWindow)
            ->update(['available_at' => $timeShifted]);

        while ($activeJobs = Job::where('attempts', '>', 0)->get()->count()) {
            sleep($sleepInterval);
            Job::where('attempts', 0)
                ->where('available_at', '<', $timeWindow)
                ->update(['available_at' => $timeShifted]);
        }

        if (config('releases.enable_releases_stop_supervisor')) {
            $basePath              = base_path();
            $command               = ' cd ' . $basePath . ' && sudo supervisorctl stop all';
            $stopSupervisorCommand = shell_exec($command);
        }

        File::delete($deployment_start_file_trigger_path);
        File::put($deployment_ready_file_trigger_path, time());

        return Command::SUCCESS;
    }
}
