<div class="row mb-3">
    <div class="col-sm-3">
        <label for="min_kcal_id">@lang('common.min_kcal')</label>
        <input id="min_kcal_id" type="number" class="form-control" name="min_kcal" value="{{$recipe?->min_kcal ?? ''}}">
    </div>
    <div class="col-sm-3">
        <label for="max_kcal_id">@lang('common.max_kcal')</label>
        <input id="max_kcal_id" type="number" class="form-control" name="max_kcal" value="{{$recipe?->max_kcal ?? ''}}">
    </div>
    <div class="col-sm-3">
        <label for="min_kh_id">@lang('common.min_kh')</label>
        <input id="min_kh_id" type="number" class="form-control" name="min_kh" value="{{$recipe?->min_kh ?? ''}}">
    </div>
    <div class="col-sm-3">
        <label for="max_kh_id">@lang('common.max_kh')</label>
        <input id="max_kh_id" type="number" class="form-control" name="max_kh" value="{{$recipe?->max_kh ?? ''}}">
    </div>
</div>
