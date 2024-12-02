<?php

declare(strict_types=1);

namespace Modules\Course\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Course\Enums\CourseId;

/**
 * Send admin notification email with data on who has restarted course 27 in the past 24 hours.
 *
 * @package Modules\Course\Jobs
 */
final class NotifyAdminsOnRestartedCoursesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $messageContent = '';
        \DB::table('course_users')
            ->where('course_id', CourseId::TBR2024_DE->value)
            ->where('counter', '>=', 1)
            ->whereBetween('course_users.updated_at', [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()])
            ->leftJoin('users', 'course_users.user_id', 'users.id')
            ->orderBy('users.email')
            ->get(['course_users.user_id', 'start_at', 'users.email','course_users.updated_at as restarted_at'])
            ->each(function (\stdClass $data) use (&$messageContent) {
                $messageContent .= "$data->email (#$data->user_id) restarted course 27, start date: $data->start_at, restarted at: $data->restarted_at\n";
            });
        if ($messageContent) {
            send_raw_admin_email($messageContent, 'Course ID 27 has been restarted, send preparation emails');
        }
    }
}
