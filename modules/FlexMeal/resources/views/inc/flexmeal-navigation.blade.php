<div class="row">
    <div class="col-xs-12" role="navigation" aria-label="Navigation for flexmeals">
        <ul class="content-links">
            <li class="content-links_item font-12 {{ active('recipes.flexmeal') }}">
                <a href="{{ route('recipes.flexmeal') }}">@lang('common.flexmeal')</a>
            </li>
            <li class="content-links_item font-12 {{ active('recipes.flexmeal.show') }}">
                <a href="{{ route('recipes.flexmeal.show') }}">@lang('common.saved')</a>
            </li>
        </ul>
    </div>
</div>
