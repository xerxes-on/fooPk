@if (Auth::user())
    <div class="col-sm-3 col-md-2 sidebar" style="display: none;">
        <ul class="nav nav-sidebar">
            <li>
                <a href="#tagebuch" data-toggle="collapse" aria-expanded="false"
                   class="dropdown-toggle">{{trans('common.diary')}}</a>
                <ul class="collapse list-unstyled" id="tagebuch">
                    <li><a href="{{ route('diary.create') }}">{{trans('common.diary')}}</a></li>
                    <li><a href="{{ route('diary.statistics') }}">{{trans('common.diary_statistics')}}</a></li>
                    {{--<li><a href="{{ route('post.create') }}">{{trans('common.create_post')}}</a></li>--}}
                    <li><a href="{{ route('posts.list') }}">{{trans('common.posts_list')}}</a></li>
                </ul>
            </li>
        </ul>

        <ul class="nav nav-sidebar">
            <li>
                <a href="#rezeptFeed" data-toggle="collapse" aria-expanded="false"
                   class="dropdown-toggle">{{trans('common.recipe_feed')}}</a>
                <ul class="collapse list-unstyled" id="rezeptFeed">
                    <li><a href="{{ route('recipes.list') }}">{{trans('common.list_of_recipes')}}</a></li>
                    <li><a href="{{ route('recipes.grid') }}">{{trans('common.grid_of_recipes')}}</a></li>
                    <li><a href="{{ route('recipes.all.get') }}">{{trans('common.all_recipes')}}</a></li>
                </ul>
            </li>
        </ul>

        <ul class="nav nav-sidebar">
            <li><a href="{{ route('purchases.list') }}">{{trans('common.purchase_list.page_title')}}</a></li>
        </ul>

        @if(Auth::user()->answers->count() === 0)
            <ul class="nav nav-sidebar">
                <li><a href="{{ route('formular.create') }}">{{trans('common.formular.title')}}</a></li>
            </ul>
        @endif

        <ul class="nav nav-sidebar">
            <li><a href="{{ route('user.settings') }}">{{trans('common.settings')}}</a></li>
        </ul>
    </div>
@endif