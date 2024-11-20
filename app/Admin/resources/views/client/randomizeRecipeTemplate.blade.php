<form id="multiple-inputs">
    {{-- Translations are required in here --}}
    <fieldset>
        <div class="form-group">
            <input type="radio"
                   id="distribution_type_general" class="form-check-input random_recipe_distribution_type"
                   name="distribution_type"
                   value="general"
                   checked="checked">
            <label for="distribution_type_general" class="form-check-label">
                General distribution
            </label>
        </div>
        <div class="form-group">
            <label for="amount_of_recipes">Add randomized recipes</label>
            <input type="number" id="amount_of_recipes"
                   class="form-control randomization_type_input randomization_type_general"
                   name="amount_of_recipes" min="0" value="0">
        </div>
    </fieldset>

    <fieldset class="row no-gutters">
        <div class="form-group col-12">
            <input id="distribution_type_ingestion" type="radio"
                   class="form-check-input random_recipe_distribution_type"
                   name="distribution_type" value="ingestions">
            <label for="distribution_type_ingestion" class="form-check-label">Split recipes into</label>
        </div>
        <div class="form-group col-6">
            <label for="breakfast_snack">Breakfast/Snack</label>
            <input type="number" id="breakfast_snack"
                   class="form-control randomization_type_input randomization_type_ingestions"
                   name="breakfast_snack" min="0" value="0" disabled="disabled"/>
        </div>
        <div class="form-group col-6">
            <label for="lunch_dinner">Lunch/Dinner</label>
            <input type="number" id="lunch_dinner"
                   class="form-control randomization_type_input randomization_type_ingestions"
                   name="lunch_dinner" min="0" value="0" disabled="disabled"/>
        </div>
    </fieldset>

    <fieldset>
        <legend>Distribution mode</legend>
        <div class="form-group">
            <div class="form-check form-check-inline">
                <input id="distribution_mode_PREFERABLE" type="radio" name="distribution_mode"
                       class="form-check-input distribution_mode"
                       value="PREFERABLE"
                       checked="checked"/>
                <label for="distribution_mode_PREFERABLE" class="form-check-label">Preferable</label>
            </div>
            <div class="form-check form-check-inline">
                <input id="distribution_mode_STRICT" type="radio" name="distribution_mode"
                       class="form-check-input distribution_mode"
                       value="STRICT"/>
                <label for="distribution_mode_STRICT" class="form-check-label">Strict</label>
            </div>
        </div>
    </fieldset>

    @if(!is_null($seasons))
        <fieldset>
            <legend>Seasons?</legend>
            <div class="row no-gutters text-left">
                @foreach($seasons as $id =>$season)
                    <div class="{{ ($loop->first || $loop->last) ? 'col-12' : 'col-sm-4' }}">
                        <div class="form-check">
                            <input id="selected_seasons_{{$id}}" type="checkbox" name="selected_seasons[]"
                                   class="form-check-input selected_seasons"
                                   value="{{ $id }}"/>
                            <label for="selected_seasons_{{$id}}" class="form-check-label">{{ $season }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </fieldset>
    @endif

    <fieldset>
        <legend>Recipe Tags/Category</legend>
        <div class="row no-gutters text-left">
            <div class="col-12">
                <div class="form-check">
                    <input id="recipes_recipes_tag_0" type="radio" name="recipes_tag"
                           class="form-check-input recipes_tag"
                           value="0"
                           checked="checked"/>
                    <label class="form-check-label" for="recipes_tag_0">Any</label>
                </div>
            </div>
            @if($recipesTags->isNotEmpty())
                @foreach($recipesTags as $tag )
                    <div class="{{ ($loop->last) ? 'col-12' : 'col-sm-6' }}">
                        <div class="form-check">
                            <input id="recipes_tag_{{$tag->id}}" type="radio" name="recipes_tag"
                                   class="form-check-input recipes_tag"
                                   value="{{ $tag->id }}"/>
                            <label class="form-check-label" for="recipes_tag_{{$tag->id}}">{{ $tag->title }}</label>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </fieldset>
</form>
