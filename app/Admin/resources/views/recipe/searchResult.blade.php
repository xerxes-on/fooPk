<div id="seach_result">
    @if(count($recipes))
        <table class="table-primary table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>@lang('common.image')</th>
                <th>@lang('common.title')</th>
                <th>@lang('common.cooking_time')</th>
                <th>@lang('common.complexity')</th>
                <th>@lang('common.meal')</th>
                <th>@lang('common.diets')</th>
                <th>@lang('common.status')</th>
                <th>@lang('admin.recipes.translations_done')</th>
                <th>@lang('common.season')</th>
                <th>@lang('admin.recipe_tag.title')</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($recipes as $recipe)
                @php /** @var \App\Models\Recipe $recipe */ @endphp
                <tr>
                    <td style="min-width: 4em;">{{$recipe->id}}</td>
                    <td style="min-width: 4em;">
                        <div>
                            <a href="{{$recipe->image->url()}}" data-toggle="lightbox">
                                <img class="thumbnail" src="{{$recipe->image->url('thumb')}}" alt="" width="80px">
                            </a>
                        </div>
                    </td>
                    <td style="min-width: 4em;">{{$recipe->title}}</td>
                    <td style="min-width: 4em;">
                        @if(!is_null($recipe->cooking_time) && !is_null($recipe->unit_of_time))
                            {{$recipe->cooking_time.' '.trans('common.'.$recipe->unit_of_time)}}
                        @else
                            -
                        @endif
                    </td>
                    <td style="min-width: 4em;">
                        @if(!is_null($recipe->complexity_id))
                            {{$recipe->complexity->title}}
                        @else
                            -
                        @endif
                    </td>
                    <td style="min-width: 4em;">
                        @forelse($recipe->ingestions as $ingestion)
                            <span class="badge table-badge">{{$ingestion->translations->where('locale', $user->lang)->first()->title}}</span>
                        @empty
                            -
                        @endforelse
                    </td>
                    <td style="min-width: 4em;">
                        @if($recipe->diets->count())
                            @foreach( $recipe->diets as $diet)
                                <span class="badge table-badge">{{$diet->name}}</span>
                            @endforeach
                        @endif
                    </td>
                    <td style="min-width: 4em;">
                        @lang('common.'.$recipe->status->lowerName())
                    </td>
                    <td style="min-width: 4em;">
                        {{ $recipe->translations_done ? trans('common.yes') : trans('common.no') }}
                    </td>
                    <td style="min-width: 4em;">
                        @if($recipe->seasons->count())
                            @foreach( $recipe->seasons as $season)
                                <span class="badge table-badge">{{$season->name}}</span>
                            @endforeach
                        @endif
                    </td>
                    <td style="min-width: 4em;">
                        @if($recipe->tags->count())
                            @foreach( $recipe->tags as $tag)
                                <span class="badge table-badge">{{$tag->title}}</span>
                            @endforeach
                        @endif
                    </td>
                    <td style="min-width: 4em;">
                        <a href="{{route('admin.model.edit', ['adminModel' => 'recipes', 'adminModelId' => $recipe->id])}}"
                           class="btn btn-xs btn-primary" title="Edit">
                            <i class="fas fa-pencil-alt" aria-hidden="true"></i>
                        </a>

                        <form action="{{route('admin.model.delete', ['adminModel' => 'recipes', 'adminModelId' => $recipe->id])}}"
                              method="POST" style="display:inline-block;">
                            {{ csrf_field() }}
                            <input type="hidden" name="_method" value="delete">
                            <button class="btn btn-xs btn-danger btn-delete" title="Delete">
                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="recipes-pagination d-flex justify-content-end">
            {{ $recipes->links() }}
        </div>
    @else
        @lang('common.nothing_found')
    @endif
</div>
