<div class="shopping-list_recipes">
    @forelse($recipes as $e => $recipeCollection)
        <div class="shopping-list_recipes-group">
            <h4 class="shopping-list_recipes-group-title">{{\Carbon\Carbon::parse($e)->toFormattedDayDateString()}}</h4>
            @foreach($recipeCollection as $recipe)
                <x-shopping-list-recipe :$recipe></x-shopping-list-recipe>
            @endforeach
        </div>
    @empty
        @lang('shopping-list::messages.success.empty_recipes_list')
    @endforelse
</div>
