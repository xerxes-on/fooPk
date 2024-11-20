<div class="form-inline mb-3">
    {!! $buttons->render() !!}

    @if(!is_null($id))
        <div class="form-buttons ml-1">
            <a href="{{ route('admin.recipes.copy-recipe', ['id' => $id]) }}" class="btn btn-info" id="copy_recipe">
                @lang('common.copy_recipe')
            </a>

            <button type="button" id="recalculate-all-users-recipe" class="btn btn-info"
                    onclick="recalculateAllUsersRecipe({{ $id }})">
                @lang('Recalculate all users')
            </button>
        </div>
    @endif
</div>
