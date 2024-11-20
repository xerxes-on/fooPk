<?php

namespace App\Listeners;

use App\Events\UserRecipeUpdated;
use App\Services\Mails\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;

final class CheckUserRecipeCount implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public string $queue = 'emails';

    /**
     * Handle the event.
     */
    public function handle(UserRecipeUpdated $event): void
    {
        app(MailService::class)->sendRawAdminEmailOnInvalidRecipes($event->user);
    }
}
