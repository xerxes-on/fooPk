<div class="mb-3">
    <div class="loading" id="loading">Loading&#8230;</div>

    <h4>@lang('common.calculated_variable_ingredients')</h4>

    <div class="alert alert-danger" id="calculation_error_alert" role="alert" style="display:none;"></div>

    <div class="row align-items-end mb-3">
        <div class="col-sm-2">
            <label for="calculate_kh_id">@lang('common.KH')</label>
            <input id="calculate_kh_id" type="number" class="form-control" name="calculate_kh">
        </div>
        <div class="col-sm-2">
            <label for="calculate_kcal_id">@lang('common.KCAL')</label>
            <input id="calculate_kcal_id" type="number" class="form-control" name="calculate_kcal">
        </div>
        <div class="col-sm-2">
            <button class="btn btn-primary" type="button"
                    id="calculate_variable_ingredients">@lang('common.calculate')</button>
        </div>
    </div>

    <table class="table-info table table-striped">
        <thead>
        <tr>
            <th class="row-header">@lang('common.type')</th>
            <th class="row-header">@lang('common.name')</th>
            <th class="row-header">@lang('common.proteins')</th>
            <th class="row-header">@lang('common.fats')</th>
            <th class="row-header">@lang('common.carbohydrates')</th>
            <th class="row-header">@lang('common.calories')</th>
            <th class="row-header">@lang('common.amount')</th>
        </tr>
        </thead>
        <tbody id="calculated_variable_ingredients_tbody">
        @foreach($ingredients_categories as $ingredient_category)
            <tr id="calculated_ingredient_category_{{ $ingredient_category->id }}_row">
                <td>{{ ucfirst($ingredient_category->name) }}</td>
                <td class="row-text name">—</td>
                <td class="row-text proteins">—</td>
                <td class="row-text fats">—</td>
                <td class="row-text carbohydrates">—</td>
                <td class="row-text calories">—</td>
                <td class="row-text units">—</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td>@lang('common.total')</td>
            <td>—</td>
            <td>@lang('common.proteins')</td>
            <td>@lang('common.fats')</td>
            <td>@lang('common.carbohydrates')</td>
            <td>@lang('common.calories')</td>
            <td>@lang('common.amount')</td>
        </tr>
        </tfoot>
    </table>
</div>
