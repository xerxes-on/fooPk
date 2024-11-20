<?php

declare(strict_types=1);

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

final class FixCustomRecipeIdEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-recipe:fix-custom-recipe-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix custom recipe id entries in the database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;
        DB::table('recipes_to_users')
            ->where('custom_recipe_id', 0)
            ->orderBy('id') // Order by primary key for consistency
            ->chunkById(1000, function ($rows) use (&$updated) {
                $updated += DB::table('recipes_to_users')
                    ->whereIn('id', $rows->pluck('id')->toArray())
                    ->update(['custom_recipe_id' => null]);
            });

        $this->info("$updated records were updated.");

        return self::SUCCESS;
    }
}
