<?php

namespace App\Jobs;

use App\Mail\FillFormularMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Mail;
use Password;

/**
 * Notify new users to fill their questionnaire.
 *
 * Only users who don't have questionnaire and active chargeBee subscriptions should be notified.
 *
 * @package App\Console\Commands
 */
final class NotifyUserToFillQuestionnaireJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    private int $maximumBatchSize = 5;

    private int $timeoutExtension = 4;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // TODO: @NickMost review, probably optimization for active subscription in query
        $users = User::whereStatus(1)
            ->doesntHave('questionnaire')
            ->whereHas('chargebeeSubscriptions', function (Builder $query) {
                $query->whereJsonContains('data->status', 'active');
                $query->orWhereJsonContains('data->status', 'future');
                $query->orWhereJsonContains('data->status', 'in_trial');
                $query->orWhereJsonContains('data->status', 'non_renewing');
            })
            ->whereRaw(
                'DATEDIFF(CURDATE(), users.created_at) IN (1, 3, 7, 14, 28)'
            ) // 1 day, 3 days, 7 days, 14 days, 28 days according to specifications
            ->select(['email', 'first_name', 'lang'])
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users->chunk($this->maximumBatchSize) as $batchNum => $chunk) {
            foreach ($chunk as $user) {
                if (empty($user->subscription)) {
                    continue;
                }
                $data = new FillFormularMail(
                    $user->lang,
                    $user->email,
                    $user->full_name,
                    Password::getRepository()->create($user)
                );
                if ($batchNum === 0) {
                    Mail::queue($data);
                    continue;
                }
                Mail::later(Carbon::now()->addMinutes($batchNum * $this->timeoutExtension), $data);
            }
        }
    }
}
