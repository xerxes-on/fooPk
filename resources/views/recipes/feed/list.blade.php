@extends('layouts.app')
@section('styles')
    <link href="{{  mix('css/flexmeal.css') }}" rel="stylesheet">
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
    </script>
@endsection

@section('title', trans('common.list_of_recipes'))

@section('content')
    <div class="container">
        @php $now = \Carbon\Carbon::now()->format('Y-m-d') @endphp

        <div class="row">

            @if(!empty($recipesGroup))

                <div class="col-lg-2 col-xs-12 col-sm-3">
                    <div class="calendar-wrapper">
                        @if(key_exists('prevWeek', $calendar))
                            <a href="{{ route('recipes.list', ['year' => $calendar['prevWeek']['year'], 'week' => $calendar['prevWeek']['week']]) }}"
                               class="week-prev"
                               aria-label="Previous Week">
                                <span class="glyphicon glyphicon-menu-up" aria-hidden="true"></span>
                            </a>
                        @else
                            <span class="glyphicon glyphicon-menu-up" aria-hidden="true"></span>
                        @endif

                        @foreach($calendar['curWeek'] as $date)
                            @php $additionalClass = '';
                            if(!key_exists($date->format('Y-m-d'), $recipesGroup) || $date->format('Y-m-d') < $now){
                                $additionalClass = 'disabled';
                            } elseif($date->format('Y-m-d') === $now){
                                $additionalClass = 'current';
                            }
                            @endphp

                            <div class="calendar-item rounded {{ $additionalClass }}">
                                <a href="#{{ $date->format('Y-m-d') }}" class="calendar-item_link scrollTo-element">
                                    <span class="calendar-item_link_day">{{ Date::parse($date)->format('D') }}</span>
                                    {{ $date->format('d.m') }}
                                </a>
                            </div>
                        @endforeach

                        @if(key_exists('nextWeek', $calendar))
                            <a href="{{ route('recipes.list', ['year' => $calendar['nextWeek']['year'], 'week' => $calendar['nextWeek']['week']]) }}"
                               class="week-next"
                               aria-label="Next Week">
                                <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                            </a>
                        @else
                            <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
                        @endif
                    </div>
                </div>

                <div class="col-lg-8 col-xs-12 col-sm-8">
                    <div class="daily-recipes">
                        @foreach($recipesGroup as $date => $group)
                            <div class="recipe-list-wrapper" id="{{ $date }}">
                                <h2>{{ Date::parse($date)->format('l j. F') }}</h2>

                                <div class="recipe-wrapper">
                                    @foreach($group as $key => $item)
                                        <div class="recipe-title-meal-time">{{ trans('common.'.$item->pivot->meal_time) }}</div>

                                        @if(!empty($item->invalid))
                                            <div class="invalid_recipe alert alert-danger">
                                                {{ trans('common.recipe_is_invalid') }}
                                            </div>
                                        @endif

                                        <div class="recipe-card">
                                            @php
                                                $recipeType = \App\Enums\Recipe\RecipeTypeEnum::tryFromClass(get_class($item));
                                            @endphp
                                            <actions
                                                    :seasons="{{$seasons}}"
                                                    :recipe-type="{{$recipeType}}"
                                                    :recipe="{{ $item->id }}"
                                                    :cooked="{{  $item->pivot->cooked }}"
                                                    :eat-out="{{  $item->pivot->eat_out }}"
                                                    :date="{{ json_encode($date) }} "
                                                    :meal-time="{{ json_encode($item->pivot->meal_time) }}"
                                            ></actions>

                                            @php
                                                if ($item instanceof \App\Models\Recipe) {
                                                   $cardImage = $item->image->url('medium');
                                                   $linkText  = $item->title;
                                                   $linkUrl   = route(
                                                       'recipe.show',
                                                       [
                                                           'id'        => $item->id,
                                                           'date'      => $date,
                                                           'ingestion' => $item->pivot->meal_time
                                                       ]
                                                   );
                                                   $flag = 'recipe';
                                               } elseif ($item instanceof \Modules\FlexMeal\Models\FlexmealLists) {
                                                   $cardImage = $item->image->url('mobile');
                                                   $linkText  = $item->name;
                                                   $linkUrl   = route(
                                                       'recipes.flexmeal.show_one',
                                                       [
                                                       'id'        => $item->id,
                                                       'date'      => \Carbon\Carbon::parse($item->pivot->meal_date)->format('Y-m-d'),
                                                       'ingestion' => $item->pivot->meal_time
                                                   ]
                                                   );
                                                   $flag = 'flex';
                                               }  else {
                                                   $cardImage = $item->originalRecipe?->image->url('medium') ?? '/images/kit_pic.png';
                                                   $linkText  = $item->originalRecipe?->title . ' *' . trans('common.edited') . '*';
                                                   $linkUrl   = route(
                                                       'recipe.show.custom.common',
                                                       [
                                                           'id'        => $item->id,
                                                           'date'      => $date,
                                                           'ingestion' => $item->pivot->meal_time
                                                       ]
                                                   );
                                                   $flag = 'edited';
                                               }
                                            @endphp
                                            <div class="recipe-card_image"
                                                 style="background-image: url('{{asset($cardImage)}}');"></div>

                                            <div class="recipe-card_container">
                                                <a href="{{ $linkUrl }}"
                                                   class="recipe-card_image-link"
                                                   aria-label="{{ $linkText }}"></a>

                                                <div class="recipe-card_info">

                                                    @if('recipe' === $flag)
                                                        <div>
                                                            <a href="{{ $linkUrl}}"
                                                               class="recipe-card_info_title">{{ $linkText }}
                                                            </a>

                                                            <div class="complexity">
                                                                <vue-stars
                                                                        name="{{ strtotime($date) . $key }}_complexity"
                                                                        :max="3"
                                                                        @if(!is_null($item->complexity))
                                                                            :value="{{ $item->complexity->id }}"
                                                                        @endif
                                                                        :readonly="true">
                                                                    <img slot-scope="props"
                                                                         slot="activeLabel"
                                                                         src="{{asset('/images/icons/ic_hat_black.svg')}}"
                                                                         width="24"
                                                                         height="24"
                                                                         alt="Icon"
                                                                    />
                                                                    <img slot-scope="props"
                                                                         slot="inactiveLabel"
                                                                         src="{{asset('/images/icons/ic_hat_black_empty.svg')}}"
                                                                         width="24"
                                                                         height="24"
                                                                         alt="Icon"
                                                                    />
                                                                </vue-stars>
                                                            </div>
                                                        </div>

                                                        <div class="recipe-card_info_favourites">
                                                            <favorite
                                                                    color="white"
                                                                    :recipe="{{ $item->id }}"
                                                                    :favorited="{{ is_null($item->favorite) ? 'false' : 'true' }}"
                                                            ></favorite>
                                                        </div>

                                                    @elseif('flex' === $flag)
                                                        <div>
                                                            <a href="{{ $linkUrl }}"
                                                               class="recipe-card_info_title">
                                                                {{ $linkText }}
                                                            </a>
                                                        </div>

                                                    @elseif('custom' === $flag)
                                                        <div>
                                                            <a href="{{ $linkUrl }}"
                                                               class="recipe-card_info_title">
                                                                {{ $linkText}}
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div>
                                                            <a href="{{ $linkUrl }}"
                                                               class="recipe-card_info_title">
                                                                {{ $linkText }}
                                                            </a>

                                                            <div class="complexity">
                                                                <vue-stars
                                                                        name="{{ strtotime($date) . $key }}_complexity"
                                                                        :max="3"
                                                                        @if(!is_null($item->originalRecipe?->complexity))
                                                                            :value="{{ $item->originalRecipe->complexity->id }}"
                                                                        @endif
                                                                        :readonly="true">
                                                                    <img slot-scope="props"
                                                                         slot="activeLabel"
                                                                         src="{{asset('/images/icons/ic_hat_black.svg')}}"
                                                                         width="24"
                                                                         height="24"
                                                                         alt="icon"
                                                                    />
                                                                    <img slot-scope="props"
                                                                         slot="inactiveLabel"
                                                                         src="{{asset('/images/icons/ic_hat_black_empty.svg')}}"
                                                                         width="24"
                                                                         height="24"
                                                                         alt="icon"
                                                                    />
                                                                </vue-stars>
                                                            </div>
                                                        </div>

                                                        @if($item->originalRecipe?->id)
                                                            <div class="recipe-card_info_favourites">
                                                                <favorite
                                                                        color="white"
                                                                        :recipe="{{ $item->originalRecipe->id }}"
                                                                        :favorited="{{ is_null($item->originalRecipe->favorite) ? 'false' : 'true' }}"
                                                                ></favorite>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                <flex-meal-edit-modal
                        :ingestion-goals="{{Js::from($user->dietdata['ingestion'])}}"></flex-meal-edit-modal>
            @else
                @php
                    $existsHeaderMessage = session()->has(['success', 'error', 'warning', 'info']);
                @endphp
                @if(!$existsHeaderMessage)
                    @php
                        $questionnaireExist = $user->isQuestionnaireExist();
                        $questionnaireApproved = $user->questionnaire_approved;
                    @endphp
                    @if($questionnaireExist && ($questionnaireApproved === true))
                        @php
                            $message = trans('common.thanks_for_information_in_your_questionnaire_recipes_will_be_soon');
                            $config = ['container' => true];
                        @endphp
                        <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
                    @elseif($questionnaireExist && (!$questionnaireApproved))
                        @php
                            $message = trans('common.thanks_for_information_in_your_questionnaire_recipes_will_be_soon_after_custom_evaluation');
                            $config = ['container'  => true];
                        @endphp
                        <x-notification-alert :message="$message" :config="$config"></x-notification-alert>
                    @endif
                @endif
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ mix('vendor/sweetalert/sweetalert.min.js')}} "></script>
    <script>
        $(document).ready(function () {
            $('.daily-recipes').on('scroll', onScroll);

            $('.scrollTo-element').on('click', function (e) {
                localStorage.setItem('activeDate', this.hash);
                scrollToTarget(this.hash);
                e.preventDefault();
            });

            let activeDate = localStorage.getItem('activeDate');

            if (activeDate) {
                scrollToTarget(activeDate);
                return;
            }

            scrollToTarget("{{ '#' . $now }}");
        });

        function scrollToTarget(target) {
            let $target = $(target);

            if ($target.length === 0) return;

            $('.recipe-feed-wrapper, .daily-recipes').animate(
                {
                    scrollTop: $target.parent().scrollTop() + $target.offset().top - $target.parent().offset().top,
                },
                {
                    duration: 1000,
                    specialEasing: {
                        width: 'linear',
                        height: 'easeOutBounce',
                    },
                    complete: function (e) {
                    },
                },
            );
        }

        function onScroll() {
            let scrollDistance = $(window).scrollTop() + 10;

            $('.daily-recipes .recipe-list-wrapper').each(function (i) {
                if ($(this).position().top <= scrollDistance) {
                    $('.calendar-wrapper .calendar-item.active').removeClass('active');
                    $('.calendar-wrapper .calendar-item').eq(i).addClass('active');
                }
            });
        }
    </script>
@append
