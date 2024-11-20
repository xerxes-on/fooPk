<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Models\Inventory;
use App\Services\Recipe\Store\RecipeInventoryStoreService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasInventory
{
    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'recipes_to_inventories');
    }

    public function saveRecipeInventory(?array $inventory = null): self
    {
        app(RecipeInventoryStoreService::class)->store($this, $inventory);
        return $this;
    }
}
