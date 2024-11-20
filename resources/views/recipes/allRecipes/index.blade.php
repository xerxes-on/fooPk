@extends('layouts.app')

@section('title', trans('common.all_recipes'))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
                {!! Form::open(['route' => 'recipes.all.get', 'method' => 'GET' , 'id' => 'search_form']) !!}

                <div class="form-group input-group search-recipes_form">
                    <div class="input-group-btn">
                        <button type="submit" class="search-recipes_form_btn" aria-label="Search"></button>
                        @if(count(request()->all()) > 0)
                            <a href="{{ route('recipes.all.get') }}"
                               class="search-recipes_form_close_link"
                               aria-label="Refresh page and clear search results">
                            </a>
                        @endif
                    </div>
                    <input type="text"
                           class="form-control mr-sm-2"
                           name="search_name"
                           id="search_name"
                           value="{{ request()->get('search_name', '') }}"
                           placeholder="{{ trans('common.search_placeholder') }}">
                </div>

                <div class="adv-search-filter-box">
                    <div class="row adv-search-filter-fields">

                        {{-- Months filter --}}
                        @php $filterTitle = trans('common.months'); @endphp
                        <x-recipe-filter class="col-sm-3 col-xs-6"
                                         :filter-data="$seasons"
                                         :filter-title="$filterTitle"
                                         filter-id="seasons"
                                         :include-default-value="false">
                        </x-recipe-filter>

                        {{-- Ingestions filter --}}
                        @php $filterTitle = trans('common.ingestion'); @endphp
                        <x-recipe-filter class="col-sm-3 col-xs-6"
                                         :filter-data="$ingestions"
                                         :filter-title="$filterTitle"
                                         filter-id="ingestion"
                                         :include-default-value="true">>
                        </x-recipe-filter>

                        {{-- Complexity filter --}}
                        @php $filterTitle = trans('common.complexity'); @endphp
                        <x-recipe-filter class="col-sm-3 col-xs-6"
                                         :filter-data="$complexities"
                                         :filter-title="$filterTitle"
                                         filter-id="complexity"
                                         :include-default-value="true">
                        </x-recipe-filter>

                        {{-- Diets filter --}}
                        @php $filterTitle = trans('common.diets'); @endphp
                        <x-recipe-filter class="col-sm-3 col-xs-6"
                                         :filter-data="$diets"
                                         :filter-title="$filterTitle"
                                         filter-id="diet"
                                         :include-default-value="true">
                        </x-recipe-filter>
                    </div>
                    <div class="row adv-search-filter-fields">
                        {{-- Cost filter --}}
                        @php $filterTitle = trans('common.cost'); @endphp
                        <x-recipe-filter class="col-sm-3 col-xs-6"
                                         :filter-data="$costs"
                                         :filter-title="$filterTitle"
                                         filter-id="cost"
                                         :include-default-value="true">
                        </x-recipe-filter>

                        {{-- Favorite filter --}}
                        @php $filterTitle = trans('common.favorite'); @endphp
                        <x-recipe-filter class="col-sm-3 col-xs-6"
                                         :filter-data="$favorites"
                                         :filter-title="$filterTitle"
                                         filter-id="favorite"
                                         :include-default-value="true">
                        </x-recipe-filter>
                        {{-- Recipe status filter --}}
                        @php $filterTitle = trans('common.invalid'); @endphp
                        <x-recipe-filter class="col-sm-3 col-xs-6"
                                         :filter-data="$invalids"
                                         :filter-title="$filterTitle"
                                         filter-id="invalid"
                                         selected-value-default="-1"
                                         :include-default-value="false">
                        </x-recipe-filter>
                        {{-- Recipe Tag filter --}}
                        @php $filterTitle = trans('common.recipe_tags'); @endphp
                        <x-recipe-filter class="col-sm-3 col-xs-6"
                                         :filter-data="$tags"
                                         :filter-title="$filterTitle"
                                         filter-id="recipe_tag"
                                         selected-value-default="-1"
                                         :include-default-value="true">
                        </x-recipe-filter>
                    </div>
                    <div class="adv-search-filter-toogle">
                        <button type="button" class="is-text-show btn-base btn-pink-light">
                            {{ trans('common.show_filters') }}
                        </button>
                        <button type="button" class="is-text-hide btn-base btn-pink-light">
                            {{ trans('common.hide_filters') }}
                        </button>
                    </div>
                </div>

                <input type="hidden" name="per_page" value="{{ request()->get('per_page', 20) }}">
                {!! Form::close() !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md-offset-1 col-md-10">
                <div class="search-recipes_list">
                    @foreach($recipes as $key => $recipe)
                        <x-recipe-card :$recipe :$key :lock-item="false" :show-ingredients="false"></x-recipe-card>
                    @endforeach
                </div>
            </div>
        </div>

        @if(count($recipes))
            <div class="row">
                <div class="col-md-offset-1 col-md-10">
                    <div class="pull-right">
                        <label for="per_page" class="search-recipes_label">{{ trans('common.per_page') }}</label>
                        <select id="per_page" class="form-control search-recipes_select changeable-element">
                            @foreach([20, 30, 40] as $option)
                                <option value="{{ $option }}"
                                        {{ request()->get('per_page', 20) == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{ $recipes->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#search_name').keydown(function (event) {
                if (event.keyCode === 13) {
                    $('#search_form').submit();
                    return false;
                }
            });

            $('.changeable-element').on('change', function () {
                if ($(this).attr('id') === 'per_page') $('[name="per_page"]').val($(this).val());
                $('#loading').show();
                $('#search_form').submit();
            });

            $('.adv-search-filter-toogle').click(function () {
                $('.adv-search-filter-box').toggleClass('isOpen');
            });
        });
    </script>
@append
