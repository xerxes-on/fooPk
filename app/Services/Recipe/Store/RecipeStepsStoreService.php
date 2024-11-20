<?php

declare(strict_types=1);

namespace App\Services\Recipe\Store;

use App\Contracts\Services\Recipe\RecipeRelationStoreInterface;
use App\Models\Recipe;
use App\Models\RecipeStep;

/**
 * Service for storing recipe steps.
 *
 * @package App\Services\Recipe\Store
 */
final class RecipeStepsStoreService implements RecipeRelationStoreInterface
{
    public function store(Recipe $model, ?array $data = []): void
    {
        // delete all exist recipe steps
        $model->steps()->delete();

        if (empty($data)) {
            return;
        }
        foreach ($data as $step) {
            RecipeStep::create(
                [
                    'recipe_id'   => $model->id,
                    'description' => $step['description']
                ]
            );
        }

    }

    /**
     * Sync Recipe Steps with existing steps.
     * TODO: maybe refactor this method or extract.
     */
    public function syncRecipeSteps(Recipe $model, array $recipeSteps): void
    {
        $children    = $model->steps()->get();
        $recipeSteps = collect($recipeSteps);

        # update
        foreach ($recipeSteps as $step) {
            if (array_key_exists('id', $step) && !empty($step['id'])) {
                $stepModel              = $model->steps()->where('id', $step['id'])->first();
                $stepModel->description = array_key_exists('description', $step) ?
                    (empty($step['description']) ? 'null' : $step['description'])
                    : 'null';

                $stepModel->save();
            }
        }

        $deleted_ids = $children->filter(fn($child) => empty($recipeSteps->where('id', $child->id)->first()))
            ->map(
                function ($child) {
                    $id = $child->id;
                    $child->delete();
                    return $id;
                }
            );

        $attachments = $recipeSteps->filter(fn($invoice_item) => empty($invoice_item['id']))
            ->map(
                function ($invoice_item) use ($deleted_ids) {
                    $invoice_item['id'] = $deleted_ids->pop();
                    return new RecipeStep($invoice_item);
                }
            );

        $model->steps()->saveMany($attachments);
    }
}
