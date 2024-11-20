<?php

declare(strict_types=1);

namespace Modules\Internal\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Remove missing diary, avatars, recipes images from the database.
 *
 * @internal
 * @package Modules\Internal\Console\Commands
 */
class ClearUsersMissingImagesCommand extends Command
{
    protected $signature = 'internal:clear-missing-images {--dry-run : Simulate the command without deleting any data}';
    protected $description = 'Remove missing diary, avatars, recipes images from the database.';
    protected bool $dryRun = false;
    protected int $total = 0;
    protected array $ids = ['post' => [], 'user' => [], 'recipe' => []];
    protected array $missing = ['post' => 0, 'user' => 0, 'recipe' => 0];
    protected array $missingImage = ['post' => [], 'user' => [], 'recipe' => []];
    protected array $exist = ['post' => 0, 'user' => 0, 'recipe' => 0];

    public function handle(): void
    {
        if (app()->environment('production')) {
            $this->info('Restricted to run on production');
            return;
        }

        $total = [
            'post' => Post::whereNotNull('image_file_name')->count('id'),
            'user' => User::whereNotNull('profile_picture_path')->count('id'),
            'recipe' => Recipe::whereNotNull('image_file_name')->count('id'),
        ];
        $this->total = $total['post'] + $total['user'] + $total['recipe'];
        $this->dryRun = (bool)$this->option('dry-run');

        if ($this->dryRun) {
            $this->info('Dry run activated. No data will be deleted.');
        }

        if ($this->total) {
            $bar = $this->output->createProgressBar($this->total);
            $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\n");
            $bar->start();

            Post::whereNotNull('image_file_name')->chunkById(200, function (Collection $posts) use ($bar): void {
                $posts->each(function (Post $post) use ($bar): void {
                    if (file_exists($post->image->path())) {
                        $this->exist['post']++;
                        return;
                    } else {
                        $this->missingImage['post'][] = $post->getKey() . '=>' . $post->image->path();
                    }
                    $this->missing['post']++;
                    $this->ids['post'][] = $post->id;
                    $bar->advance();
                });
            });


            User::whereNotNull('profile_picture_path')->chunkById(200, function (Collection $users) use ($bar): void {
                $users->each(function (User $user) use ($bar): void {
                    if (file_exists(Storage::disk('public')->path($user->profile_picture_path))) {
                        $this->exist['user']++;
                        return;
                    } else {
                        $this->missingImage['user'][] = $user->getKey() . '=>' . $user->profile_picture_path;
                    }
                    $this->missing['user']++;
                    $this->ids['user'][] = $user->id;
                    $bar->advance();
                });
            });


            Recipe::whereNotNull('image_file_name')->chunkById(200, function (Collection $recipes) use ($bar): void {
                $recipes->each(function (Recipe $recipe) use ($bar): void {
                    if (file_exists($recipe->image->path())) {
                        $this->exist['recipe']++;
                        return;
                    } else {
                        $this->missingImage['recipe'][] = $recipe->getKey() . '=>' . $recipe->image->path();
                    }
                    $this->missing['recipe']++;
                    $this->ids['recipe'][] = $recipe->id;
                    $bar->advance();
                });
            });

            $bar->finish();


            $this->info("\nPosts images total: " . $total['post'] . ", Exist: " . $this->exist['post'] . ", Missing: " . $this->missing['post']);

            if ($this->missing['post']) {
                $this->info('Missed posts images:');
                $this->info(implode(PHP_EOL, $this->missingImage['post']));
            }


            $this->info("\nAvatars total: " . $total['user'] . ", Exist: " . $this->exist['user'] . ", Missing: " . $this->missing['user']);

            if ($this->missing['user']) {
                $this->info('Missed avatars images:');
                $this->info(implode(PHP_EOL, $this->missingImage['user']));
            }

            $this->info("\nRecipes images total: " . $total['recipe'] . ", Exist: " . $this->exist['recipe'] . ", Missing: " . $this->missing['recipe']);

            if ($this->missing['recipe']) {
                $this->info('Missed recipes images:');
                $this->info(implode(PHP_EOL, $this->missingImage['recipe']));
            }


        }

        if (!$this->dryRun) {


            foreach (array_chunk($this->ids['post'], 1000) as $chunk) {
                try {
                    DB::transaction(static function () use ($chunk) {
                        Post::whereIntegerInRaw('id', $chunk)
                            ->update(
                                [
                                    'image_file_name' => null,
                                    'image_file_size' => null,
                                    'image_content_type' => null,
                                    'image_updated_at' => null,
                                ]
                            );
                    });
                } catch (\Throwable $e) {
                    $this->error($e->getMessage());
                }
            }


            foreach (array_chunk($this->ids['user'], 1000) as $chunk) {
                try {
                    DB::transaction(static function () use ($chunk) {
                        User::whereIntegerInRaw('id', $chunk)
                            ->update(
                                [
                                    'profile_picture_path' => null
                                ]
                            );
                    });
                } catch (\Throwable $e) {
                    $this->error($e->getMessage());
                }
            }

            foreach (array_chunk($this->ids['recipe'], 1000) as $chunk) {
                try {
                    DB::transaction(static function () use ($chunk) {
                        Recipe::whereIntegerInRaw('id', $chunk)
                            ->update(
                                [
                                    'image_file_name' => null,
                                    'image_file_size' => null,
                                    'image_content_type' => null,
                                    'image_updated_at' => null,
                                ]
                            );
                    });
                } catch (\Throwable $e) {
                    $this->error($e->getMessage());
                }
            }
        }

    }
}
