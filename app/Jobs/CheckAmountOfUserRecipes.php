<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Mails\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CheckAmountOfUserRecipes implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->onQueue('emails');
    }

    /**user_recipe
     * Execute the job.
     */
    public function handle(): void
    {
        User::has('allRecipes')->active()->chunk(
            200,
            function (Collection $users) {
                foreach ($users as $user) {
                    app(MailService::class)->sendRawAdminEmailOnInvalidRecipes($user);
                }
            }
        );
    }
}
