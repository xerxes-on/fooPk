<div id="seach_result">
    @if(count($recipeTags))
        <table class="table-primary table table-striped">
            <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:20%">@lang('common.slug')</th>
                <th style="width:20%">@lang('common.title')</th>
                <th style="width:40%">@lang('common.ingredients')</th>
                <th style="width:5%">@lang('admin.recipe_tag.publicFlag')</th>
                <th style="width:5%">@lang('admin.recipe_tag.internalFlag')</th>
                <th style="width:5%;"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($recipeTags as $recipeTag)
                <tr>
                    <td>{{$recipeTag->id}}</td>
                    <td>{{$recipeTag->slug}}</td>
                    <td>{{$recipeTag->title}}</td>
                    <td>
                        @include('admin::collapsedList', [
                                                             'id'          => "ingredient_tag_$recipeTag->id",
                                                             'titles'      => $recipeTag->recipes->pluck('id'),
                                                             'count'       => $recipeTag->recipes_count,
                                                         ])
                    </td>

                    <td>
                        {!!
                              $recipeTag->filter ?
                        '<span class="fas fa-check text-success" aria-hidden="true"></span>' :
                        '<span class="fas fa-times text-warning" aria-hidden="true"></span>'
                        !!}
                    </td>
                    <td>

                        {!!
                         $recipeTag->is_internal ?
                                                                       '<span class="fas fa-check text-success" aria-hidden="true"></span>' :
                                                                       '<span class="fas fa-times text-warning" aria-hidden="true"></span>'
                        !!}
                    </td>

                    <td>
                        <a href="recipe_tags/{{$recipeTag->id}}/edit" class="btn btn-xs btn-primary"
                           title="Edit"
                           data-toggle="tooltip">
                            <i class="fas fa-pencil-alt"></i>
                        </a>

                        <form action="recipe_tags/{{$recipeTag->id}}/delete" method="POST"
                              style="display:inline-block;">
                            {{ csrf_field() }}
                            <input type="hidden" name="_method" value="delete">
                            <button class="btn btn-xs btn-danger btn-delete" title="Delete" data-toggle="tooltip">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="recipe-tags-pagination d-flex justify-content-end">
            {{ $recipeTags->links() }}
        </div>

    @else
        @lang('common.nothing_found')
    @endif
</div>