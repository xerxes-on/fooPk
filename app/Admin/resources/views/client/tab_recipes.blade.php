@php
    use App\Enums\Admin\Permission\PermissionEnum;use App\Models\Admin;
    $canDeleteAllUserRecipes = auth()->user()->can(PermissionEnum::DELETE_ALL_USER_RECIPES->value);
@endphp
<div class="text-left" style="margin-bottom: 10px">
    @can(PermissionEnum::ADD_RECIPES_TO_CLIENT->value, Admin::class)
        <button type="button" id="add-recipes" class="btn btn-info ladda-button" data-style="expand-right"
                onclick="window.FoodPunk.functions.addRecipes()">
            <span class="ladda-label">+ @lang('common.add_recipe')</span>
        </button>

        <button type="button" id="add-randomize-recipes-to-select-users" class="btn btn-info"
                onclick="window.FoodPunk.functions.addRandomizeRecipes()">
            <i class="fas fa-plus" aria-hidden="true"></i> @lang('admin.buttons.add_random_recipes')
        </button>
    @endif

    <button type="button" id="recalculate-user-recipes" class="btn btn-info ladda-button" data-style="expand-right"
            onclick="window.FoodPunk.functions.recalculateUserRecipes()">
        <span class="ladda-label">@lang('common.recalculate')</span>
    </button>

    @if($canDeleteAllUserRecipes)
        <button type="button" id="delete-all-selected-recipes" class="btn btn-danger" style="display: none"
                onclick="window.FoodPunk.functions.deleteSelectedRecipes()">
            <span class="fa fa-info-circle" aria-hidden="true"></span>
            <span class="ladda-label">@lang('common.delete_selected')</span>
        </button>
        <button type="button" id="delete-all-user-recipes" class="btn btn-danger" onclick="window.FoodPunk.functions.deleteAllRecipes()">
            <span class="ladda-label">@lang('common.delete_all_recipe')</span>
        </button>
    @endif
</div>

<table id="recipesByUser" class="table table-striped table-bordered" style="width:100%">
    <thead>
    <tr>
        <th><label><input type="checkbox" readonly onclick="window.FoodPunk.functions.toggleSelect(this)"></label></th>
        <th>#</th>
        <th>@lang('common.image')</th>
        <th>@lang('common.title')</th>
        <th>@lang('common.cooking_time')</th>
        <th>@lang('common.complexity')</th>
        <th>@lang('common.meal')</th>
        <th>@lang('common.invalid')</th>
        <th>@lang('common.recipe_calculated')</th>
        <th>@lang('common.diets')</th>
        <th>@lang('common.status')</th>
        <th>@lang('common.favorite')</th>
        <th></th>
        <th></th>
    </tr>
    </thead>
</table>

<div class="modal fade" id="recipeDetailsModal" aria-describedby="recipeDetailsTitle" tabindex="-1" role="dialog"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recipeDetailsTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

{{--Search modal for recipes--}}
<div style="display:none">
    <div id="allRecipes-popup-wrapper" style="padding:10px; background:#fff;">
        <table id="allRecipes-popup" class="table table-striped table-bordered" style="width:100%">
            <thead>
            <tr>
                <th colspan="2">@lang('common.filters')</th>
                <td>
                    <select name="ingestionFilter" id="recipeIngestionFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($ingestions as $ingestion)
                            <option value="{{$ingestion->id}}">{{$ingestion->title}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="dietFilter" id="recipeDietFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($diets as $diet)
                            <option value="{{$diet->id}}">{{$diet->name}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="complexityFilter" id="recipeComplexityFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($complexities as $complexity)
                            <option value="{{$complexity->id}}">{{$complexity->title}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="costFilter" id="recipeCostFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($costs as $cost)
                            <option value="{{$cost->id}}">{{$cost->title}}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="tagFilter" id="recipeTagFilter" class="form-control">
                        <option value="" selected>-</option>
                        @foreach($tags as $tag)
                            <option value="{{$tag->id}}">{{$tag->title}}</option>
                        @endforeach
                    </select>
                </td>
                <th>
                    <button class="btn btn-info" id="js-dt-filter">@lang('common.apply')</button>
                </th>
            </tr>
            <tr>
                <th>#</th>
                <th>@lang('common.title')</th>
                <th><label for="recipeIngestionFilter">@lang('common.day_category')</label></th>
                <th><label for="recipeDietFilter">@lang('common.diets')</label></th>
                <th><label for="recipeComplexityFilter">@lang('common.complexity')</label></th>
                <th><label for="recipeCostFilter">@lang('common.cost')</label></th>
                <th><label for="recipeTagFilter">@lang('common.recipe_tags')</label></th>
                <th>@lang('common.status')</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th>#</th>
                <th>@lang('common.title')</th>
                <th><label for="recipeIngestionFilter">@lang('common.day_category')</label></th>
                <th><label for="recipeDietFilter">@lang('common.diets')</label></th>
                <th><label for="recipeComplexityFilter">@lang('common.complexity')</label></th>
                <th><label for="recipeCostFilter">@lang('common.cost')</label></th>
                <th><label for="recipeTagFilter">@lang('common.recipe_tags')</label></th>
                <th>@lang('common.status')</th>
            </tr>

        </table>

        <div class="text-right mt-3">
            <button type="button"
                    id="submit-add-recipes"
                    class="btn btn-info ladda-button"
                    data-style="expand-right"
                    onclick="window.FoodPunk.functions.submitAdding()">
                <span class="ladda-label">@lang('common.submit')</span>
            </button>
        </div>
    </div>
</div>

