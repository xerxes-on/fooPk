<?php

declare(strict_types=1);

namespace Modules\Foodpoints\Console\Commands;

use App\Enums\Admin\Permission\RoleEnum;
use App\Exceptions\PublicException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Modules\Foodpoints\Enums\FoodpointsDistributionTypeEnum;
use Modules\Foodpoints\Models\FoodpointsDistribution;
use Modules\PushNotification\DTO\PushNotificationOptions;
use Modules\PushNotification\Enums\NotificationTypeSlugEnum;
use Modules\PushNotification\Enums\UserGroupOptionEnum;
use Modules\PushNotification\Jobs\SendPushNotifications;
use Modules\PushNotification\Models\Notification as NotificationModel;
use Modules\PushNotification\Models\NotificationType;

/**
 * Monthly foodpoints distribution
 *
 * @package Modules\Foodpoints\Console\Commands
 */
class MonthlyDistributionCommand extends Command
{
    use DispatchesJobs;

    protected $signature = 'foodpoints:monthly-distribution {--force : skip release date} {--silent : Distribute without notification} {--dry-run : Simulate the command without creating real jobs}';
    protected $description = 'Distribute monthly foodpoints';

    protected bool $dryRun = false;
    protected bool $force = false;
    protected bool $silent = false;
    protected int $chunkSize = 500;
    protected int $distributionType = 0;
    protected int $period = 0;
    protected $checkpointDate;
    protected int $foodpointsAmount = 100;


    public function handle(): void
    {
        $this->setUpDefaults();
        $now = Carbon::now();

        //TODO: its a command why exception just show error text and return error code...
        if (!$this->force && $now->lt(Carbon::createFromFormat('Y-m-d H:i:s', config('foodpoints.distributions.monthly.start_at')))) {
            throw new PublicException('Too early to start, starts at ' . config('foodpoints.distributions.monthly.start_at'));
        }

        if ($this->dryRun) {
            $this->info('Dry run activated. No real foodpoints will be distributed.');
        }

        $usersId = User::active()
            ->whereNotIn(
                'id',
                function (Builder $query) {
                    $query->select('user_id')
                        ->from(app(FoodpointsDistribution::class)->getTable())
                        ->where('created_at', '>', $this->checkpointDate)
                        ->where('type', $this->distributionType);
                }
            )
            ->whereIn(
                'id',
                function (Builder $query) {
                    $query->select('id')
                        ->from(app(User::class)->getTable())
                        ->whereRaw('MOD(DATEDIFF(CURDATE(), `created_at`),' . $this->period . ') = 0');
                }
            )
            ->where('users.created_at', '<', $this->checkpointDate)
            ->whereHas('questionnaire')
            ->whereHas('activeSubscriptions')
            ->orderBy('users.id', 'DESC')
            ->get()
            ->pluck('id')
            ->toArray();


        // filtration only test users if environment isn't production
        if (!app()->environment('production')) {
            $allowedUsers = [];
            User::whereIn('id', $usersId)->orderBy('id', 'DESC')->chunk(
                $this->chunkSize,
                function ($users) use (&$allowedUsers) {
                    foreach ($users as $user) {
                        if (
                            $user->hasRole(RoleEnum::TEST_USER->value)
                        ) {
                            $allowedUsers[] = $user->id;
                        }
                    }
                }
            );
            $usersId = $allowedUsers;
        }

        $notificationTypeSlug = NotificationTypeSlugEnum::FOODPOINTS_DISTRIBUTION_MONTHLY->value;
        $notificationTypeId = NotificationType::where('slug', $notificationTypeSlug)->value('id');

        if (empty($notificationTypeId)) {
            // throw error, not exists type TODO: its a command why exception just show error text and return error code...
            throw new PublicException('Not exists notification type with slug "' . $notificationTypeSlug . '" ');
        }

        if (!$this->dryRun && !empty($usersId)) {

            $notificationData = config('foodpoints.distributions.monthly.pushnotification.texts');
            $notificationData['type_id'] = $notificationTypeId;
            $notificationData['dispatched'] = true;
            $notification = NotificationModel::create($notificationData);

            $depositText = config('foodpoints.distributions.monthly.deposit_text');

            User::whereIn('id', $usersId)->orderBy('id', 'DESC')->chunk(
                $this->chunkSize,
                function ($users) use (&$allowedUsers, $depositText) {
                    foreach ($users as $user) {
                        try {
                            $user->deposit($this->foodpointsAmount, ['description' => $depositText]);

                            FoodpointsDistribution::create([
                                'user_id' => $user->id,
                                'amount' => $this->foodpointsAmount,
                                'type' => $this->distributionType,
                            ]);

                        } catch (\Throwable $e) {
                            logError($e);
                        }
                    }
                }
            );

            if (!$this->silent) {
                $this->dispatch(new SendPushNotifications($notification->id, new PushNotificationOptions(UserGroupOptionEnum::ALL->value, usersId: $usersId)))->onQueue('high');
            }

        }

        $this->info('Amount of foodpoints = ' . $this->foodpointsAmount);
        $this->info('Selected users id : ' . (!empty($usersId) ? implode(', ', $usersId) : 'none'));
        $this->info('Total amount = ' . count($usersId));
        $this->info('Silent = ' . ($this->silent ? 'true' : 'false'));
    }

    private function setUpDefaults(): void
    {
        $this->dryRun = (bool)$this->option('dry-run');
        $this->force = (bool)$this->option('force');
        $this->silent = (bool)$this->option('silent');
        $this->distributionType = FoodpointsDistributionTypeEnum::REGULAR_MONTHLY->value;
        $this->period = config('foodpoints.distributions.monthly.checkpoint_period');
        $this->checkpointDate = Carbon::now()->endOfDay()->subDays($this->period);
        $this->foodpointsAmount = config('foodpoints.distributions.monthly.amount');
    }
}
