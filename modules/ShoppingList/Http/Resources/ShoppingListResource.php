<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a Purchase list.
 *
 * @property int $id
 * @property string|null $name
 * @property bool $archived
 * @property \Carbon\Carbon updated_at
 *
 * @package App\Http\Resources
 */
final class ShoppingListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id
        ];
    }
}
