@fragment('title')
    <span>{{$recipe->title}}</span>
    @can(\App\Enums\Admin\Permission\PermissionEnum::CREATE_RECIPE->value, '\App\Models\Admin')
        <a href="{{$editRoute}}" title="{{trans('common.edit')}}"><span class="fa fa-pen" aria-hidden="true"></span></a>
    @endcan
@endfragment

@fragment('card')
    <div class="card">
        <div class="card-body">
            <img class="w-50 float-right" src="{{asset($recipe->image->url('large'))}}" alt="{{$recipe->title}}">
            <p class="mb-0 font-weight-bold">@lang('common.calculations'):</p>
            <ul class="list-unstyled">
                @foreach($calculations as $ingestion => $calculation)
                    <li>
                        <span class="badge badge-primary text-uppercase"><b>{{$ingestion}}</b></span>
                        @if($calculation['errors'] && is_array($calculation['notices']))
                            <span class="badge badge-danger">@lang('common.invalid')</span>
                            {{ custom_implode($calculation['notices'], ' ')}}
                        @else
                            <span class="badge badge-success">@lang('common.valid')</span>
                        @endif
                        <ul class="list-unstyled">
                            <li>@lang('common.carb'): <b>{{ $calculation['calculated_KH'] ?? 0 }}</b> (g)</li>
                            <li>@lang('common.protein'): <b>{{ $calculation['calculated_EW'] ?? 0 }}</b> (g)</li>
                            <li>@lang('common.fat'): <b>{{ $calculation['calculated_F'] ?? 0 }}</b> (g)</li>
                            <li>@lang('common.calories_word'): <b>{{ $calculation['calculated_KCal'] ?? 0 }}</b> (Kcal)
                            </li>
                        </ul>
                    </li>
                @endforeach
            </ul>

            <p class="mb-0 font-weight-bold">@lang('ingredient:common.ingredients'):</p>
            <ul class="list-unstyled">
                @forelse($calculatedIngredients as $ingestion => $ingredients)
                    <li><span class="badge badge-primary text-uppercase"><b>{{$ingestion}}</b></span>
                        <ul class="list-unstyled">
                            @forelse($ingredients as $ingredient)
                                @php $isNotSpice = $ingredient['main_category'] != \Modules\Ingredient\Enums\IngredientCategoryEnum::SEASON->value; @endphp
                                <li>@if($isNotSpice)
                                        <span>{{ $ingredient['ingredient_amount'] }}</span>
                                    @endif
                                    {{ $ingredient['ingredient_text'] }}
                                </li>
                            @empty
                                <li>@lang('common.no_ingredients')</li>
                            @endforelse
                        </ul>
                    </li>
                @empty
                    <li>@lang('common.no_ingredients')</li>
                @endforelse
            </ul>

            <p class="mb-0 font-weight-bold">@lang('common.steps_to_prepare'):</p>
            <ol>
                @foreach($steps as $step)
                    <li>{{ $step->description }}</li>
                @endforeach
            </ol>

            <p class="mb-0 font-weight-bold">@lang('common.diets'):</p>
            <div class="mb-2">
                <span class="badge badge-primary">{!! custom_implode($recipe->diets->pluck('name')->toArray(), '</span> <span class="badge badge-primary">')!!}</span>
            </div>

            <p class="mb-0 font-weight-bold">@lang('common.season'):</p>
            <div class="mb-2">
                <span class="badge badge-primary">{!! custom_implode($recipe->seasons->pluck('name')->toArray(), '</span> <span class="badge badge-primary">') !!}</span>
            </div>

            <p class="mb-0 font-weight-bold">@lang('common.tags'):</p>
            <div class="mb-2">
                <span class="badge badge-primary">{!! custom_implode($recipe->tags->pluck('title')->toArray(), '</span> <span class="badge badge-primary">') !!}</span>
            </div>
        </div>
    </div>
@endfragment
