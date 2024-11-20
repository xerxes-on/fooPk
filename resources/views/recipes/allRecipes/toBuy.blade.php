@extends('layouts.app')

@section('title', trans('common.buy_recipes'))

@section('content')
    <div class="container">

        @if(!empty($recipes))
            <div class="row">
                <div class="col-md-offset-1 col-md-10">

                    {!! Form::open(['route' => 'recipes.buy.get', 'method' => 'GET' , 'id' => 'search_form']) !!}

                    <div class="form-group input-group search-recipes_form">
                        <div class="input-group-btn">
                            <button type="submit"
                                    class="search-recipes_form_btn"
                                    aria-label="{{trans('common.search')}}"></button>
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
                                             :include-default-value="true">
                            </x-recipe-filter>

                            {{-- Complexity filter --}}
                            @php $filterTitle = trans('common.complexity'); @endphp
                            <x-recipe-filter class="col-sm-3 col-xs-6"
                                             :filter-data="$complexities"
                                             :filter-title="$filterTitle"
                                             filter-id="complexity"
                                             :include-default-value="true">
                            </x-recipe-filter>

                            {{-- Cost filter --}}
                            @php $filterTitle = trans('common.cost'); @endphp
                            <x-recipe-filter class="col-sm-3 col-xs-6"
                                             :filter-data="$costs"
                                             :filter-title="$filterTitle"
                                             filter-id="cost"
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

                            {{-- Favorite filter --}}
                            @php $filterTitle = trans('common.favorite'); @endphp
                            <x-recipe-filter class="col-sm-3 col-xs-6"
                                             :filter-data="$favorites"
                                             :filter-title="$filterTitle"
                                             filter-id="favorite"
                                             :include-default-value="true">
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
                            <x-recipe-card :$recipe
                                           :$key
                                           data-id="{{ $recipe->id }}"
                                           :lock-item="true"
                                           :show-ingredients="true">
                            </x-recipe-card>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-offset-1 col-md-10">
                    @php
                        $filterTitle = trans('common.per_page');
                        $perPageOptions = [20 => 20, 30 => 30, 40 => 40]
                    @endphp
                    <x-recipe-filter class="pull-right"
                                     :filter-data="$perPageOptions"
                                     :filter-title="$filterTitle"
                                     :include-default-value="false"
                                     selected-value-default="20"
                                     filter-id="per_page">
                    </x-recipe-filter>

                    {{ $recipes->appends(request()->query())->links() }}

                </div>
            </div>
        @else
            @php
                $message = trans('common.recipe_to_buy_empty');
            @endphp
            <x-notification-alert :message="$message"></x-notification-alert>
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

            $('.recipe-locked').on('click', function () {
                let recipeId = $(this).closest('.search-recipes_list_item').attr('data-id');

                if (confirm("{{trans('common.are_you_sure_buy_recipe')}}")) {
                    // /user/recipes/buying
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('recipes.buying') }}",
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content'),
                            recipeId: recipeId,
                        },
                        beforeSend: function () {
                            $('#loading').show();
                        },
                        success: function (data) {

                            if (typeof data.link === 'undefined') {

                                if (data.success) {
                                    window.location = '{{ route('recipes.all.get') }}';
                                } else {
                                    $('#loading').hide();
                                    alert(data.message);
                                }

                            } else {
                                $('#loading').hide();
                                if (confirm(data.message)) {
                                    window.location = data.link;
                                }
                            }
                        },
                        error: function (err) {
                            alert(err.message);
                        },
                    });
                }
            });
        });
    </script>
@append
