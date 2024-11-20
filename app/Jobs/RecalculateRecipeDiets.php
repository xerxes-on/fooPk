<?php

namespace App\Jobs;

use App\Models\Recipe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasOne};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Collection;
use Modules\Ingredient\Models\Ingredient;

/**
 * Job that recalculate Recipes ingredients with its respected diets.
 *
 * @package App\Jobs
 */
final class RecalculateRecipeDiets implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private ?int $ingredientId = null)
    {
        $this->onQueue('high');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Recipe::query()
            ->when(
                $this->ingredientId,
                function (Builder $builder) {
                    $builder->whereHas(
                        'ingredients',
                        function (Builder $builder) {
                            $builder->where('ingredient_id', $this->ingredientId);
                        }
                    )->orWhereHas(
                        'variableIngredients',
                        function (Builder $builder) {
                            $builder->where('ingredient_id', $this->ingredientId);
                        }
                    );
                }
            )
            ->select('id')
            ->withOnly(
                [
                    'ingredients' => fn(
                        BelongsToMany $builder
                    ) => $builder->select('ingredients.id', 'ingredients.category_id')->withOnly(
                        [
                            'category' => fn(
                                HasOne $builder
                            ) => $builder->select('ingredient_categories.id')->withOnly(
                                [
                                    'diets' => fn(
                                        BelongsToMany $builder
                                    ) => $builder->select('diets.id')->without('translations')
                                ]
                            )
                        ]
                    ),
                    'variableIngredients' => fn(
                        BelongsToMany $builder
                    ) => $builder->select('ingredients.id', 'ingredients.category_id')->withOnly(
                        [
                            'category' => fn(
                                HasOne $builder
                            ) => $builder->select('ingredient_categories.id')->withOnly(
                                [
                                    'diets' => fn(
                                        BelongsToMany $builder
                                    ) => $builder->select('diets.id')->without('translations')
                                ]
                            )
                        ]
                    ),
                ]
            )
            ->chunkById(
                500,
                function (Collection $recipes) {
                    foreach ($recipes as $recipe) {
                        /** @var Recipe $recipe */
                        $ingredientsCount = count($recipe->ingredients) + count($recipe->variableIngredients);
                        $diets            = [];

                        // Ordinary ingredients
                        foreach ($recipe->ingredients as $ingredient) {
                            /** @var Ingredient $ingredient */
                            if (!count($ingredient->category->diets)) {
                                continue;
                            }
                            foreach ($ingredient->category->diets as $diet) {
                                if (!isset($diets['diet_' . $diet->id])) {
                                    $diets['diet_' . $diet->id] = [
                                        'diet_id' => $diet->id,
                                        'count'   => 0
                                    ];
                                }

                                $diets['diet_' . $diet->id]['count'] += 1;
                            }
                        }

                        // Variable ingredients
                        foreach ($recipe->variableIngredients as $ingredient) {
                            /** @var Ingredient $ingredient */
                            if (!count($ingredient->category->diets)) {
                                continue;
                            }
                            foreach ($ingredient->category->diets as $diet) {
                                if (!isset($diets['diet_' . $diet->id])) {
                                    $diets['diet_' . $diet->id] = [
                                        'diet_id' => $diet->id,
                                        'count'   => 0
                                    ];
                                }

                                $diets['diet_' . $diet->id]['count'] += 1;
                            }
                        }

                        $trueDiets = [];
                        if (count($diets) > 0) {
                            foreach ($diets as $diet) {
                                if ($diet['count'] == $ingredientsCount) {
                                    array_push($trueDiets, $diet['diet_id']);
                                }
                            }
                        }
                        $recipe->diets()->sync($trueDiets);
                    }
                }
            );
    }
}
