<?php

declare(strict_types=1);

namespace App\Services\Recipe\Store;

use App\Contracts\Services\Recipe\RecipeRelationStoreInterface;
use App\Models\Recipe;

/**
 * Service for storing recipe ingestion
 *
 * @package App\Services\Recipe\Store
 */
final class RecipeIngestionStoreService implements RecipeRelationStoreInterface
{
    public function store(Recipe $model, ?array $data = []): void
    {
        // delete all exist connections
        $model->ingestions()->detach();

        if (!empty($data)) {
            $model->ingestions()->attach(generate_connection_array('ingestion_id', $data));
        }
    }
}
