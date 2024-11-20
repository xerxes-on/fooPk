<div class="shopping-list_recipes_item" id="recipe_{{ $recipe->pivot->id }}">
    <div class="shopping-list_recipes_item_img">
        @if(is_null($originalRecipe->image))
            <img src="{{ asset('/images/kit_pic.png') }}" alt="{{ $title }}">
        @else
            <img src="{{ $originalRecipe->image->url('thumb') }}" alt="{{ $title }}">
        @endif
    </div>

    <div class="shopping-list_recipes_item_info">

        <a href="{{ $routeUrl }}" class="shopping-list_recipes_item_info_title">{{ $title }}</a>

        <div class="shopping-list_recipes_item_info_type">{{ $mealtime }}</div>

        <div class="shopping-list_recipes_item_right">
            <select class="shopping-list_recipes_item_right_select recipe-serving-anchor"
                    data-recipe-id="{{ $recipe->id }}"
                    data-meal-time="{{ $recipe->pivot->mealtime }}"
                    data-recipe-type="{{ (string)$recipeType }}"
                    data-meal-day="{{ $recipe->pivot->meal_day }}">
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}" {{ $recipe->pivot->servings === $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
                @if($recipe->pivot->servings > 10)
                    <option value="{{ $recipe->pivot->servings }}" selected>{{ $recipe->pivot->servings }}</option>
                @endif
            </select>
            <button type="button"
                    aria-label="@lang('shopping-list::common.labels.remove_recipe')"
                    title="@lang('shopping-list::common.labels.remove_recipe')"
                    class="shopping-list_recipes_item_right_label_rounded recipe-delete-anchor btn-with-icon btn-with-icon-delete"
                    data-target="#recipe_{{ $recipe->pivot->id }}"
                    data-recipe-id="{{ $recipe->id }}"
                    data-list-id="{{ $recipe->pivot->list_id }}"
                    data-recipe-type="{{ (string)$recipeType }}"
                    data-meal-time="{{ $recipe->pivot->mealtime  }}"
                    data-meal-day="{{ $recipe->pivot->meal_day }}"></button>
        </div>
    </div>
</div>
