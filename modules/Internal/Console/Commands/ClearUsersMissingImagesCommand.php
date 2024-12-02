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
use Modules\Course\Models\Course;

/**
 * Remove missing diary, avatars, recipes images from the database.
 *
 * @internal
 * @package Modules\Internal\Console\Commands
 */
class ClearUsersMissingImagesCommand extends Command
{
    protected $signature   = 'internal:clear-missing-images {--dry-run : Simulate the command without deleting any data}';
    protected $description = 'Remove missing diary, avatars, recipes images from the database.';
    protected bool $dryRun = false;
    protected int $total   = 0;
    protected array $ids;
    protected array $missing;
    protected array $missingImage;
    protected array $exist;

    public function handle(): void
    {
        $this->setDefaults();
        if (app()->environment('production')) {
            $this->info('Restricted to run on production');
            return;
        }

        $total        = $this->getTotal();
        $this->total  = $total['post'] + $total['user'] + $total['recipe'];
        $this->dryRun = (bool)$this->option('dry-run');

        if ($this->dryRun) {
            $this->info('Dry run activated. No data will be deleted.');
        }

        if ($this->total) {
            $this->gatherMissingImagesInfo();
            $this->displayLogInfo($total);
        }

        if (!$this->dryRun) {
            $this->deleteImages();
        }
    }

    private function getTotal(): array
    {
        return [
            'post'   => Post::whereNotNull('image_file_name')->count('id'),
            'user'   => User::whereNotNull('profile_picture_path')->count('id'),
            'recipe' => Recipe::whereNotNull('image_file_name')->count('id'),
            'course' => Course::whereNotNull('image_file_name')->count('id'),
        ];
    }

    private function gatherMissingImagesInfo(): void
    {
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
                }
                $this->missingImage['user'][] = $user->getKey() . '=>' . $user->profile_picture_path;
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
                }
                $this->missingImage['recipe'][] = $recipe->getKey() . '=>' . $recipe->image->path();
                $this->missing['recipe']++;
                $this->ids['recipe'][] = $recipe->id;
                $bar->advance();
            });
        });
        Course::whereNotNull('image_file_name')->chunkById(200, function (Collection $courses) use ($bar): void {
            $courses->each(function (Course $course) use ($bar): void {
                if (file_exists($course->image->path())) {
                    $this->exist['course']++;
                    return;
                }
                $this->missingImage['course'][] = $course->getKey() . '=>' . $course->image->path();
                $this->missing['course']++;
                $this->ids['course'][] = $course->id;
                $bar->advance();
            });
        });

        $bar->finish();
    }

    private function displayLogInfo(array $total): void
    {
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

        if ($this->missing['course']) {
            $this->info('Missed course images:');
            $this->info(implode(PHP_EOL, $this->missingImage['course']));
        }
    }

    private function deleteImages(): void
    {
        foreach (array_chunk($this->ids['post'], 1000) as $chunk) {
            try {
                DB::transaction(static function () use ($chunk) {
                    Post::whereIntegerInRaw('id', $chunk)
                        ->update(
                            [
                                'image_file_name'    => null,
                                'image_file_size'    => null,
                                'image_content_type' => null,
                                'image_updated_at'   => null,
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
                                'image_file_name'    => null,
                                'image_file_size'    => null,
                                'image_content_type' => null,
                                'image_updated_at'   => null,
                            ]
                        );
                });
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        foreach (array_chunk($this->ids['course'], 1000) as $chunk) {
            try {
                DB::transaction(static function () use ($chunk) {
                    Course::whereIntegerInRaw('id', $chunk)
                        ->update(
                            [
                                'image_file_name'    => null,
                                'image_file_size'    => null,
                                'image_content_type' => null,
                                'image_updated_at'   => null,
                            ]
                        );
                });
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
        }
    }

    private function setDefaults(): void
    {
        $this->ids = $this->missingImage = [
            'post'   => [],
            'user'   => [],
            'recipe' => [],
            'course' => []
        ];
        $this->missing = $this->exist = [
            'post'   => 0,
            'user'   => 0,
            'recipe' => 0,
            'course' => 0
        ];

    }
}
