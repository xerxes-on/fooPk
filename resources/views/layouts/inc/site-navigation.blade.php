@php
    if ($isApp) {
        return;
    }
    // TODO: using service provider probably make more flexible navigation bar
    $routesList = [
        'recipes' 		=> ['recipes.list', 'recipes.grid', 'recipes.all.get', 'recipe.show','recipe.allRecipes.show', 'recipe.show.custom.common', 'recipes.flexmeal', 'recipes.flexmeal.show', 'recipes.buy.get'],
        'challenges' 	=> ['course.index', 'course.buy', 'articles.list', 'articles.show'],
        'diary' 		=> ['diary.create', 'diary.statistics', 'diary.edit', 'posts.list', 'post.create', 'post.edit', 'post.form'],
        'purchaseList' 	=> ['purchases.list']
    ];
@endphp
<div class="navbar-fixed-top">
    <nav class="navbar navbar-static-top header @guest header-guest @endguest">
        <div class="container header-container">
            <div class="header-wrap">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('/images/icons/foodpunk-logo-black.svg') }}"
                         alt="{{ config('app.name', 'Foodpunk') }}"
                         width="105"
                         height="54">
                </a>
                @guest
                    <div class="navbar-right">
                        <div class="btn-header-group">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="btn btn-tiffany mr-5">Log in</a>
                            @endif
                            <a href="https://foodpunk.com" class="btn btn-white">@lang('auth.book_now')</a>
                        </div>
                    </div>
                @else
                    {{-- Collapsed Hamburger --}}
                    <button type="button"
                            class="navbar-toggle collapsed header-navbar-toggle"
                            data-toggle="collapse"
                            data-target="#app-navbar-collapse"
                            aria-expanded="false"
                            aria-label="Toggle Navigation">
                        <span class="icon-bar" aria-hidden="true"></span>
                        <span class="icon-bar" aria-hidden="true"></span>
                        <span class="icon-bar" aria-hidden="true"></span>
                    </button>
                @endguest

                @auth
                    <div class="collapse navbar-collapse" id="app-navbar-collapse">
                        <div class="navbar-wrap">
                            {{-- Left Side Of Navbar --}}
                            <ul class="navbar-nav main-menu">
                                <li class="main-menu-item js-main-menu-item-dropdown {{ active($routesList['recipes'], 'js-main-menu-item-opened') }}">
                                    <button
                                            class="main-menu-item-link dropdown-toggle {{ active($routesList['recipes']) }}"
                                            type="button"
                                            data-toggle="dropdown"
                                            aria-haspopup="true"
                                            aria-expanded="false">
                                        @lang('common.recipes')
                                    </button>
                                    <nav class="dropdown-menu header-submenu" aria-label="Recipes navigation links">
                                        <ul class="header-submenu-wrap">
                                            <li class="header-submenu-item">
                                                <a class="header-submenu-link {{ active('recipes.list') }}"
                                                   href="{{ route('recipes.list') }}">
                                                    @lang('common.list_of_recipes')
                                                </a>
                                            </li>
                                            <li class="header-submenu-item">
                                                <a class="header-submenu-link {{ active('recipes.grid') }}"
                                                   href="{{ route('recipes.grid') }}">
                                                    @lang('common.grid_of_recipes')
                                                </a>
                                            </li>
                                            <li class="header-submenu-item">
                                                <a class="header-submenu-link {{ active('recipes.all.get') }}"
                                                   href="{{ route('recipes.all.get') }}">
                                                    @lang('common.all_recipes')
                                                </a>
                                            </li>
                                            <li class="header-submenu-item">
                                                <a class="header-submenu-link {{ active('recipes.buy.get') }}"
                                                   href="{{ route('recipes.buy.get') }}">
                                                    @lang('common.buy_recipes')
                                                </a>
                                            </li>
                                            <li class="header-submenu-item">
                                                <a class="header-submenu-link {{ active(['recipes.flexmeal','recipes.flexmeal.show']) }}"
                                                   href="{{ route('recipes.flexmeal') }}">
                                                    @lang('common.flexmeal')
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </li>
                                <li class="main-menu-item js-main-menu-item-dropdown {{ active($routesList['challenges'], 'js-main-menu-item-opened') }}">
                                    <button
                                            class="main-menu-item-link dropdown-toggle {{ active($routesList['challenges']) }}"
                                            type="button"
                                            data-toggle="dropdown"
                                            aria-haspopup="true"
                                            aria-expanded="false">
                                        @lang('course::common.courses')
                                    </button>
                                    <nav class="dropdown-menu header-submenu" aria-label="Challenges navigation links">
                                        <ul class="header-submenu-wrap">
                                            <li class="header-submenu-item">
                                                <a class="header-submenu-link {{ active('course.index') }}"
                                                   href="{{ route('course.index') }}">
                                                    @lang('course::common.all')
                                                </a>
                                            </li>
                                            <li class="header-submenu-item">
                                                <a class="header-submenu-link {{ active(['articles.list', 'articles.show']) }}"
                                                   href="{{ route('articles.list') }}">
                                                    @lang('course::common.lessons')
                                                </a>
                                            </li>
                                            <li class="header-submenu-item">
                                                <a class="header-submenu-link {{ active('course.buy') }}"
                                                   href="{{ route('course.buy') }}">
                                                    @lang('course::common.more')
                                                </a>
                                            </li>
                                        </ul>

                                    </nav>
                                </li>
                                <li class="main-menu-item">
                                    <a class="main-menu-item-link {{ active($routesList['diary']) }}"
                                       href="{{ route('diary.statistics') }}">
                                        @lang('common.diary')
                                    </a>
                                </li>
                                <li class="main-menu-item">
                                    <a class="main-menu-item-link {{ active($routesList['purchaseList']) }}"
                                       href="{{ route('purchases.list') }}">
                                        @lang('shopping-list::common.page_title')
                                    </a>
                                </li>
                                {{-- Mobile links --}}
                                <li class="main-menu-item main-menu-item-mobile">
                                    <a class="main-menu-item-link {{ active('user.settings') }}"
                                       href="{{ route('user.settings') }}">
                                        @lang('common.settings')
                                    </a>
                                </li>
                                <x-course-widget :course-data="(array)$aboChallengeData"
                                                 type="mini_mobile"></x-course-widget>
                                <li class="main-menu-item main-menu-item-mobile">
                                    <a class="main-menu-item-link"
                                       href="{{ route('logout.get') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        @lang('auth.logout')
                                    </a>

                                    <form id="logout-form"
                                          action="{{ route('logout.post') }}"
                                          method="POST"
                                          style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>

                            {{-- Right Side Of Navbar --}}
                            <div class="navbar-right mr-0">
                                <div class="btn-header-group">
                                    <x-course-widget :course-data="(array)$aboChallengeData"
                                                     type="mini_desktop"></x-course-widget>
                                    <div class="balance-info btn-header-group-item">
                                        <a class="balance-info-link"
                                           href="{{ route('pages.wallet') }}"
                                           title="{{(int) $user?->balance}}">
											<span class="balance-info-currency">
												{{ shortenNumbers((int) $user?->balance) }}
											</span>
                                        </a>
                                    </div>

                                    <div class="dropdown user-dropdown btn-header-group-item">
                                        <button type="button"
                                                class="dropdown-toggle user-dropdown-toggle"
                                                data-toggle="dropdown"
                                                aria-expanded="false"
                                                aria-haspopup="true"
                                                v-pre>
                                			<span class="user-dropdown-img"
                                                  @style(["background-image: url('$user->avatar_url')" => $user?->avatar_url])
                                                  aria-label="User Avatar">
											</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li class="settings">
                                                <a href="{{ route('user.settings') }}">@lang('common.settings')</a>
                                            </li>
                                            <li class="logout">
                                                <a href="{{ route('logout.get') }}"
                                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                    @lang('auth.logout')
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="user-name">{{$user->fullName}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </nav>
</div>
