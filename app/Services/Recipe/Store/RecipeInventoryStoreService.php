<?php

declare(strict_types=1);

namespace App\Services\Recipe\Store;

use App\Contracts\Services\Recipe\RecipeRelationStoreInterface;
use App\Models\Recipe;

/**
 * Service for storing recipe inventory
 *
 * @package App\Services\Recipe\Store
 */
final class RecipeInventoryStoreService implements RecipeRelationStoreInterface
{
    public function store(Recipe $model, ?array $data = []): void
    {
        // delete all exist connections
        $model->inventories()->detach();

        if (empty($data)) {
            return;
        }
        $model->inventories()->attach(generate_connection_array('inventory_id', $data));
    }
}
