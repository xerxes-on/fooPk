<div class="text-left" style="margin-bottom: 10px">
    <button type="button" id="generate-recipes" class="btn btn-info ladda-button" data-style="expand-right"
            onclick="window.FoodPunk.functions.generateRecipe()">
        <span class="ladda-label">@lang('common.subscription_recipes_generate')</span>
    </button>
</div>

<table id="recipesByChallenge" class="table table-striped table-bordered" style="width:100%">
    <thead>
    <tr>
        <th>#</th>
        <th>@lang('common.image')</th>
        <th>@lang('common.title')</th>
        <th>@lang('common.subscription') </th>
        <th>@lang('common.date')</th>
        <th>@lang('common.meal')</th>
        <th>@lang('common.invalid')</th>
        <th>@lang('common.KCAL')</th>
        <th>@lang('common.KH')</th>
        <th>@lang('common.recipe_calculated')</th>
        <th>@lang('common.diets')</th>
    </tr>
    </thead>
</table>