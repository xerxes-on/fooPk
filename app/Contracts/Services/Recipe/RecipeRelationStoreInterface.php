<?php

namespace App\Contracts\Services\Recipe;

use App\Models\Recipe;

interface RecipeRelationStoreInterface
{
    public function store(Recipe $model, ?array $data = []): void;
}
