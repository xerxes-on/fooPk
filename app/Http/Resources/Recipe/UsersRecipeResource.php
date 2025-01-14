<?php

declare(strict_types=1);

namespace App\Http\Resources\Recipe;

use App\Enums\MealtimeEnum;
use App\Http\Resources\{Complexity, CustomRecipeCategory, Diet, IngestionResource, Inventory, Price, Step,};
use App\Http\Resources\Meal\PlannedMealResource;
use App\Models\{CustomRecipe, Ingestion, Recipe};
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\FlexMeal\Models\FlexmealLists;

/**
 * Detailed representation of a users recipe for API.
 *
 * @property-read \App\Models\Recipe|\App\Models\CustomRecipe|\Modules\FlexMeal\Models\FlexmealLists $resource
 *
 * @note if ($resource instanceof \App\Models\Recipe)
 * @property int $id
 * @property int $title
 * @property int|bool $calc_invalid
 * @property bool|null $excluded
 * @property int $cooking_time
 * @property string $unit_of_time
 * @property-read \App\Models\Diet[] $diets
 * @property-read \Illuminate\Support\Collection $custom_categories
 * @property-read \Neko\Stapler\Attachment $image
 * @property-read \App\Models\Ingestion $ingestions
 * @property-read \App\Models\Inventory $inventories
 * @property-read \App\Models\RecipeComplexity|null $complexity
 * @property-read \App\Models\RecipePrice|null $price
 * @method bool favorited()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany steps()
 *
 * @note if ($resource instanceof \App\Models\CustomRecipe)
 * @property-read \App\Models\Recipe $originalRecipe
 *
 * @note if ($resource instanceof \Modules\FlexMeal\Models\FlexmealLists)
 * @property string $name
 * @property string $notes
 * @property \App\Models\Ingestion $ingestion
 *
 * @used-by \App\Http\Controllers\Api\RecipesApiController::get()
 * @used-by PlannedMealResource::toArray()
 * TODO: Refactor it by separating into different resources
 * @package App\Http\Resources
 */
final class UsersRecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return match ($this->resource::class) {
            Recipe::class        => $this->getRecipeResource(),
            CustomRecipe::class  => $this->getCustomRecipeResource(),
            FlexmealLists::class => $this->getFlexmealResource(),
            default              => $this->getFullyCustomRecipeResource(),
        };
    }

    private function getRecipeResource(): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'calc_invalid'      => (bool)$this->calc_invalid,
            'excluded'          => !is_null($this->excluded),
            'cooking_time'      => $this->cooking_time,
            'unit_of_time'      => trans("common.$this->unit_of_time"),
            'diets'             => Diet::collection($this->diets),
            'custom_categories' => CustomRecipeCategory::collection($this->custom_categories),
            'favourited'        => $this->favorited(),
            'image'             => asset($this->image->url('medium_square')),
            'ingestions'        => IngestionResource::collection($this->ingestions),
            'inventories'       => Inventory::collection($this->inventories),
            'complexity'        => is_null($this?->complexity) ? null : new Complexity($this->complexity),
            'price'             => is_null($this?->price) ? null : new Price($this->price),
            'purchased'         => false,
            'steps'             => Step::collection($this->steps()->get()),
            'custom'            => false,
            'type'              => 'common',
            'tags'              => RecipeTagResource::collection($this->publicTags),
        ];
    }

    private function getCustomRecipeResource(): array
    {
        return $this->originalRecipe === null ?
            $this->getFullyCustomRecipeResource() :
            [
                'id'           => $this->id,
                'title'        => $this->originalRecipe->title,
                'calc_invalid' => (bool)$this->calc_invalid,
                'cooking_time' => $this->originalRecipe->cooking_time,
                'unit_of_time' => $this->originalRecipe->unit_of_time,
                'diets'        => Diet::collection($this->originalRecipe->diets),
                'favourited'   => $this->originalRecipe->favorited(),
                'image'        => asset($this->originalRecipe->image->url('medium_square')),
                'ingestions'   => IngestionResource::collection($this->originalRecipe->ingestions),
                'inventories'  => Inventory::collection($this->originalRecipe->inventories),
                'complexity'   => is_null($this->originalRecipe?->complexity) ?
                    null :
                    new Complexity($this->originalRecipe->complexity),
                'price'     => is_null($this->originalRecipe?->price) ? null : new Price($this->originalRecipe->price),
                'purchased' => false,
                'steps'     => Step::collection($this->originalRecipe->steps()->get()),
                'custom'    => true,
                'type'      => 'custom',
            ];
    }

    private function getFlexmealResource(): array
    {
        /**
         * This dirty hack is needed to allow mobile application to substitute dinner flexmeal with lunch and vice versa.
         * This is done intentionally as mobile developers search mealtime with active ingestion,
         * that's why all possible ingestions have to be included (not only the one it was created for)
         */
        $ingestion = [new IngestionResource($this->ingestion)];
        if ($this->ingestion->id !== MealtimeEnum::BREAKFAST->value) {
            $ingestion = IngestionResource::collection(
                Ingestion::whereIn('id', MealtimeEnum::getExchangeableTypes())->get()
            );
        }

        return [
            'id'         => $this->id,
            'title'      => $this->name,
            'notes'      => $this->notes,
            'image'      => asset($this->image->url('large')),
            'ingestions' => $ingestion,
            'type'       => 'flexmeal'
        ];
    }

    private function getFullyCustomRecipeResource(): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'calc_invalid' => (bool)$this->calc_invalid,
            'image'        => asset('/images/kit_pic.png'),
            'ingestions'   => [new IngestionResource($this->ingestion)],
            'custom'       => true,
            'type'         => 'fully_custom',
        ];
    }
}
