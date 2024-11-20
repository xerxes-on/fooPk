<div {{ $attributes->merge(['class' => implode(' ', $recipeItemClass)]) }}>

    @if( $recipe->is_new && !$lockItem )
        <div class="ribbon"><span>{{trans('common.label_new')}}</span></div>
    @endif

    <div class="search-recipes_list_item_img">
        @if($lockItem)
            <div class="recipe-locked">
                <div class="recipe-unlocked">
                    <span>{{ trans('common.Unlock') }}</span> <strong>{{config('foodpunk.new_recipe_price')}}
                        FP</strong>
                </div>
            </div>
        @endif
        <img src="{{ asset($recipe->image->url('thumb')) }}" alt="{{ $recipe->title }}" width="150" height="150"/>
    </div>

    <div class="search-recipes_list_item_info">
        <div class="search-recipes_list_item_info_wrap">
            @if($lockItem)
                <span class="search-recipes_list_item_info_title">{{ $recipe->title }}</span>
            @else
                <a href="{{ route('recipe.allRecipes.show', ['id'=>$recipe->id]) }}"
                   class="search-recipes_list_item_info_title"
                   title="{{$recipe->title}}">
                    @if ($recipe?->calc_invalid)
                        {{trans('common.front_invalid_recipe')}}
                    @endif {{$recipe->title}}
                </a>
            @endif
            <div class="d-flex">
                <div class="search-recipes_list_item_right_label">
                    <div class="complexity">
                        <vue-stars
                                name="{{ $key }}_complexity"
                                :max="3"
                                @if(!is_null($recipe->complexity))
                                    :value="{{ $recipe->complexity->id }}"
                                :title="'{{$recipe->complexity->title}}'"
                                @endif
                                :readonly="true">
                            <img slot-scope="props"
                                 slot="activeLabel"
                                 src="/images/icons/ic_hat_black.svg"
                                 width="24"
                                 height="24"
                                 alt="Icon"
                            />
                            <img slot-scope="props"
                                 slot="inactiveLabel"
                                 src="/images/icons/ic_hat_black_empty.svg"
                                 width="24"
                                 height="24"
                                 alt="Icon"
                            />
                        </vue-stars>
                    </div>
                </div>
                <div class="search-recipes_list_item_right_label">
                    <div class="cost">
                        <vue-stars
                                name="{{ $key }}_price"
                                :max="3"
                                @if(!is_null($recipe->price))
                                    :value="{{ $recipe->price->id }}"
                                :title="'{{ $recipe->price->title }}'"
                                @endif
                                :readonly="true">
                            <img slot-scope="props"
                                 slot="activeLabel"
                                 src="/images/icons/ic_money.svg"
                                 width="24"
                                 height="24"
                                 alt="Icon"/>
                            <img slot-scope="props"
                                 slot="inactiveLabel"
                                 src="/images/icons/ic_money_noactive.svg"
                                 height="24"
                                 width="24"
                                 alt="Icon"/>
                        </vue-stars>
                    </div>
                </div>
            </div>
        </div>

        <div class="search-recipes_list_item_info_wrap my-6px">
            <div class="search-recipes_list_item_info_type">
                @php
                    echo '<span class="select-recipe-list_item_info_type_item">' .
                         implode(
                                    '</span><span class="select-recipe-list_item_info_type_item">',
                                    $recipe->ingestions->pluck('title')->toArray()
                                 );
                @endphp
            </div>
            @if(isset($recipe->cooking_time) && isset($recipe->unit_of_time))
                <div class="search-recipes_list_item_info_cooking-time">
                    <span>{{ $recipe->cooking_time . ' ' . trans('common.'.$recipe->unit_of_time) }}</span>
                </div>
            @else
                <div class="search-recipes_list_item_info_cooking-time-invalid"></div>
            @endif
        </div>

        @if($diets !== '')
            <div class="recipe-diets search-recipes_list_item_right_label mobile-hidden">
                {!! $diets !!}
            </div>
        @endif
    </div>

    <div class="search-recipes_list_item_footer">
        @if($diets !== '')
            <div class="recipe-diets search-recipes_list_item_right_label laptop-hidden search-recipes_list_item_right_label_mobile">
                {!! $diets !!}
            </div>
        @endif

        @if($showIngredients)
            @php
                $ingredients = collect();
                $ingredients->push($recipe->ingredients);
                $ingredients->push($recipe->variableIngredients);
            @endphp
            <div class="search-recipes_list_item_ingredients ">
                <b>{{trans('common.ingredients_preview')}}:</b>
                @php
                    echo '<span class="select-recipe-list_item_info_type_item">' .
                         implode(
                                    '</span><span class="select-recipe-list_item_info_type_item">',
                                    $ingredients->collapse()->pluck('name')->toArray()
                                 );
                @endphp
            </div>
        @endif
    </div>
</div>