<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

final class PruneOldPasswordResetTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-password-reset-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old password reset records from the database.';

    private int $batchLimit = 100;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = DB::table('password_resets')
            ->where('created_at', '<', now()->subDays(3))
            ->limit($this->batchLimit)
            ->delete();

        $this->info("Deleted $deleted old password reset records.");

        return self::SUCCESS;
    }
}
