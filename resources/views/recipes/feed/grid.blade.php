@extends('layouts.app')
@section('title', trans('common.grid_of_recipes'))

@section('styles')
    <link href="{{ mix('css/flexmeal.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ mix('vendor/owlcarousel/assets/owl.theme.default.min.css') }}" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js" defer></script>
    <script>
        window.foodPunk.routes = {
            flexMeals: '{{route('recipes.flexmeal.get_for_mealtime')}}',
            replaceRecipe: '{{route('recipes.replacement')}}',
            checkFlexmeal: '{{route('recipes.flexmeal.check')}}',
            replaceWithFlexmeal: '{{route('recipes.replace_with_flexmeal')}}',
            getUserRecipes: '{{route('recipes.ration_food')}}',
            getUserIngredients: '{{route('ingredients.get')}}',
            getIngredients: '{{route('ingredients.search.all')}}',
            updateFlexmeal: '{{route('recipes.flexmeal.update')}}',

            cook: '{{route('toCook')}}',
            unCook: '{{route('unCook')}}',
            eatOut: '{{route('eatOut')}}',
        };

        // Single time controller for dynamic container
        document.addEventListener('DOMContentLoaded', function cb(event) {
            let dynamicContainer = document.getElementById('js-dynamic-container');
            if (!dynamicContainer) {
                return;
            }
            if (window.innerWidth <= 992) {
                dynamicContainer.classList.remove('container');
                dynamicContainer.classList.add('container-fluid');
            } else {
                dynamicContainer.classList.add('container');
                dynamicContainer.classList.remove('container-fluid');
            }

            event.currentTarget.removeEventListener(event.type, cb);
        });

        // Controller for dynamic container
        window.addEventListener('resize', function () {
            let dynamicContainer = document.getElementById('js-dynamic-container');
            if (!dynamicContainer) {
                return;
            }

            if (window.innerWidth <= 992 && dynamicContainer.classList.contains('container')) {
                dynamicContainer.classList.remove('container');
                dynamicContainer.classList.add('container-fluid');
            } else if (window.innerWidth > 992 && dynamicContainer.classList.contains('container-fluid')) {
                dynamicContainer.classList.add('container');
                dynamicContainer.classList.remove('container-fluid');
            }
        });
    </script>
@endsection

@section('content')
    <div class="container" id="js-dynamic-container">

        <div class="row py-15">

            <div class="col-md-2 col-sm-3 col-xs-7">
                <div class="week-calendar" id="week-calendar">
                    <b class="week-range">
                        {{$calendar['curWeek']->getStartDate()->format('d.m')}}
                        -
                        {{$calendar['curWeek']->getEndDate()->format('d.m')}}
                    </b>
                </div>
            </div>

            <div class="col-md-10 col-sm-9 col-xs-5">
                <div class="week-carousel-nav">
                    @if(key_exists('prevWeek', $calendar))
                        <a href="{{ route('recipes.grid', ['year' => $calendar['prevWeek']['year'], 'week' => $calendar['prevWeek']['week']]) }}"
                           class="week-carousel-nav_prev"
                           aria-label="Previous">
                        </a>
                    @else
                        <span class="week-carousel-nav_prev" aria-label="Disabled previous link"></span>
                    @endif

                    @if(key_exists('nextWeek', $calendar))
                        <a href="{{ route('recipes.grid', ['year' => $calendar['nextWeek']['year'], 'week' => $calendar['nextWeek']['week']]) }}"
                           class="week-carousel-nav_next"
                           aria-label="Next">
                        </a>
                    @else
                        <span class="week-carousel-nav_next" aria-label="Disabled next link"></span>
                    @endif
                </div>
            </div>

        </div>

        @if(!empty($recipesGroup))

            <div class="week-grid" id="week-grid">
                @php $cd = 0; @endphp
                @foreach($calendar['curWeek'] as $date)
                    @if($date->dayOfWeek == 1)
                        @php $cd = 0; @endphp
                        <div class="week-grid_wrapper">
                            @endif

                            @php $cd++; @endphp

                            <div style="order:1;" class="week-grid_day grid-day-order-1 grid-day-order-1-{{ $cd }}">
                                {{ Date::parse($date)->format('l') }}
                            </div>

                            @php $k = 2; @endphp
                            @foreach($ingestions as $ingestion)
                                @php $empty = true; @endphp

                                @if(key_exists($date->format('Y-m-d'), $recipesGroup))
                                    @php $ci = 0; @endphp
                                    @foreach($recipesGroup[$date->format('Y-m-d')] as $key => $item)
                                        @php
                                            $ci++;
                                            $recipeType = \App\Enums\Recipe\RecipeTypeEnum::tryFromClass(get_class($item));
                                        @endphp
                                        @if($item->pivot->meal_time == $ingestion->key)
                                            @php $empty = false; @endphp
                                            <div style="order:{{ $k }};"
                                                 class="week-grid_item grid-item-order-{{ $k }} grid-item-order-{{ $k }}-{{ $cd }}">

                                                @if(!empty($item->invalid))
                                                    <div class="invalid_recipe alert alert-danger">
                                                        @lang('common.recipe_is_invalid')
                                                    </div>
                                                @endif

                                                @if($item instanceof \App\Models\Recipe)
                                                    @include("recipes.feed.inc.{$recipeType->lowerName()}-recipe-extend-menu")
                                                    @elseif($item instanceof \Modules\FlexMeal\Models\FlexmealLists)
                                                    @include("recipes.feed.inc.{$recipeType->lowerName()}-recipe-extend-menu")
                                                @else
                                                    {{--Custom Recipe recipe as altered ordinary one --}}
                                                        @include("recipes.feed.inc.{$recipeType->lowerName()}-recipe-extend-menu")
                                                @endif

                                                <div class="week-grid_item_actions-menu">
                                                    <actions
                                                            :recipe="{{ $item->id }}"
                                                            :recipe-type="{{$recipeType}}"
                                                            :seasons="{{$seasons}}"
                                                            :cooked="{{ $item->pivot->cooked }}"
                                                            :eat-out="{{ $item->pivot->eat_out }}"
                                                            :date="{{ json_encode($date->format('Y-m-d')) }}"
                                                            :meal-time="{{ json_encode($item->pivot->meal_time) }}"
                                                    ></actions>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                                @if($empty)
                                    <div style="order:{{ $k }};" class="empty week-grid_item">
                                        <p>@lang('common.' . $ingestion->key)</p>
                                    </div>
                                @endif

                                @php $k++; @endphp
                            @endforeach

                            @if($date->dayOfWeek == 0)
                        </div>
                    @endif
                @endforeach
            </div>

        @else
            @php
                $latestQuestionnaireApproved = $user->questionnaire_approved;
                $formularExists = $user->isQuestionnaireExist();
                $message = '';
                if ($formularExists && ($latestQuestionnaireApproved === true)) {
                    $message = trans('common.thanks_for_information_in_your_questionnaire_recipes_will_be_soon');
                } elseif ($formularExists && !$latestQuestionnaireApproved) {
                    $message = trans('common.thanks_for_information_in_your_questionnaire_recipes_will_be_soon_after_custom_evaluation');
                }
            @endphp

            @if(!empty($message))
                <x-notification-alert :message="$message"></x-notification-alert>
            @endif
        @endif

    </div>
    <flex-meal-edit-modal :ingestion-goals="{{Js::from($user?->dietdata['ingestion']??null)}}"></flex-meal-edit-modal>
@endsection

@section('scripts')
    <script src="{{ mix('vendor/sweetalert/sweetalert.min.js')}} "></script>
@endsection
