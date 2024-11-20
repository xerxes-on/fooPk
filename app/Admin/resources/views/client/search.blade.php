<div class="alert bg-info">
    <div class="form-group input-group">
        <input type="hidden" name="search_url" value="{{route('admin.recipe.search')}}">
        <input type="text" class="form-control mr-sm-2" name="search_name">
        <span class="input-group-btn">
            <button class="btn btn-primary" id="client_search" type="button">{{trans('common.search')}}</button>
            <button class="btn btn-danger" id="clear_search" type="button" style="display: none;">X</button>
        </span>
    </div>
</div>

<div id="search_result_wrapper">

</div>
