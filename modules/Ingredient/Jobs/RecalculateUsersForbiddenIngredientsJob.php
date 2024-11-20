<?php

declare(strict_types=1);

namespace Modules\Ingredient\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job that recalculates all active users' forbidden ingredients.
 *
 * @package Modules\Ingredient\Jobs
 */
final class RecalculateUsersForbiddenIngredientsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $chunkSize = 200;

    public function __construct()
    {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        User::active()->whereHas('questionnaire')->chunk($this->chunkSize, static function (Collection $users) {
            $users->each(static function (User $user) {
                SyncUserExcludedIngredientsJob::dispatch($user)->onQueue('low');
            });
        });
    }
}
