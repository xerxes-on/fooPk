<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\User\UserStatusEnum;
use App\Events\AdminActionsTaken;
use App\Models\{User, UserSubscription};
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;

/**
 * Deactivate users which subscription is finished
 *
 * @package App\Console\Commands
 */
final class UsersDeactivationJob extends Command
{
    /**
     * @var string
     */
    public const DATE_AFTER_USER_STARTS_DISABLING = '2019-07-01'; // YYYY-MM-DD

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user_deactivation_check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate users which subscription is finished';

    /**
     * Execute the console command.
     *
     * TODO: method has a Cyclomatic Complexity of 18. Simplify this method to reduce its complexity.
     * TODO: method has an NPath complexity of 514. Simplify this method to reduce its complexity.
     *
     * @return int
     */
    public function handle(): int
    {
        $breakPointDate               = Carbon::parse(self::DATE_AFTER_USER_STARTS_DISABLING)->startOfDay();
        $todayDateTime                = Carbon::now();
        $outdatedSubscriptionsUserIds = UserSubscription::whereActive(1)
            ->where('ends_at', '<', $todayDateTime)
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        $processedUsersCount = 0;

        if (empty($outdatedSubscriptionsUserIds)) {
            $this->info('User deactivation by subscriptions finished, total amount of deactivated users = ' . $processedUsersCount);
            Log::info('User deactivation by subscriptions finished, total amount of deactivated users = ' . $processedUsersCount);
            return self::SUCCESS;
        }

        // TODO: may take quite a lot of resources. refactor required.
        foreach ($outdatedSubscriptionsUserIds as $userId) {
            $user = User::find($userId);
            if (empty($user)) {
                $subscriptionsWithoutUser = UserSubscription::where('user_id', $userId)->get();

                if (empty($subscriptionsWithoutUser)) {
                    continue;
                }

                foreach ($subscriptionsWithoutUser as $item) {
                    $item->stopSubscription(false);
                }
                continue;
            }

            $subscriptions = $user->subscriptions()->where('active', 1)->get();

            $issetActiveSubscription = false;

            if (empty($subscriptions)) {
                continue;
            }

            foreach ($subscriptions as $subscription) {
                if (
                    empty($subscription->ends_at) ||
                    (!empty($subscription->ends_at) && $subscription->ends_at > $todayDateTime)
                ) {
                    $issetActiveSubscription = true;
                } elseif (!empty($subscription->ends_at) && $subscription->ends_at < $todayDateTime) {
                    $subscription->stopSubscription(false);
                }
            }

            if ($issetActiveSubscription === false) {
                // check if user had subscription before $breakPointDate and we don't need to make inactive
                $makeUserInactive = true;

                foreach ($subscriptions as $subscription) {
                    if (!empty($subscription->ends_at) && $subscription->ends_at < $breakPointDate) {
                        $makeUserInactive = false;
                    }
                }


                if ($makeUserInactive) {
                    $user->status = UserStatusEnum::INACTIVE->value;
                    $user->save();
                    $processedUsersCount++;

                    Log::info('User deactivation by subscriptions, user ' . $user->id . ' is deactivated ');
                }
            }
        }

        AdminActionsTaken::dispatch();

        $this->info('User deactivation by subscriptions finished, total amount of deactivated users = ' . $processedUsersCount);
        Log::info('User deactivation by subscriptions finished, total amount of deactivated users = ' . $processedUsersCount);

        return self::SUCCESS;
    }
}
