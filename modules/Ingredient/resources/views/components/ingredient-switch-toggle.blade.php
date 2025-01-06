<div class="ingredient-switch">
    <label class="switch ingredient-switch-outer-label">
        <input type="checkbox" id="js-ingredient-switcher" class="ingredient-switch-input" readonly>
        <label for="js-ingredient-switcher"
               data-on="@lang('ingredient::common.switcher_tip.alt_unit')"
               data-off="@lang('ingredient::common.switcher_tip.main_unit')"
               class="ingredient-switch-inner-label"></label>
    </label>

    <x-ingredient-tip :data="$helpText"></x-ingredient-tip>
</div>