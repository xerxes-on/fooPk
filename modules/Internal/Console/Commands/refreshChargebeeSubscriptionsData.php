<?php

namespace Modules\Internal\Console\Commands;

use App\Models\User;
use Modules\Chargebee\Services\ChargebeeService;
use Illuminate\Console\Command;

/** @internal */
class refreshChargebeeSubscriptionsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargebeeSubscriptions:refresh {user? : The Email of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync chargebee subscriptions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $userEmail = $this->argument('user');

        # get user data
        $userData = is_null($userEmail) ? User::active()->orderBy('id', 'DESC') : User::ofEmail($userEmail);
        //        $userData = is_null($userEmail) ? User::where('id','>',43400) : User::ofEmail($userEmail);
        $userData = $userData->get();

        app(ChargebeeService::class)->configureEnvironment();

        $processed = [];
        $this->output->progressStart(count($userData));
        foreach ($userData as $_user) {
            $processed[] = $_user->email;
            $this->output->progressAdvance();
            app(ChargebeeService::class)->refreshUserSubscriptionData($_user);
        }
        $this->output->progressFinish();
        $this->info('Processed ' . count($processed));
        natcasesort($processed);
        foreach ($processed as $email) {
            $this->info($email);
        }

        return self::SUCCESS;
    }
}
