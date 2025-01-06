<div id="seach_result">
    @if(count($ingredientTags))
        <table class="table-primary table table-striped">
            <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:20%">@lang('common.slug')</th>
                <th style="width:20%">@lang('common.title')</th>
                <th style="width:35%">@lang('ingredient::common.ingredients')</th>
                <th style="width:20%;"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($ingredientTags as $ingredientTag)
                <tr>
                    <td>{{$ingredientTag->id}}</td>
                    <td>{{$ingredientTag->slug}}</td>
                    <td>{{$ingredientTag->title}}</td>
                    <td>
                        @include('admin::collapsedList', [
                                                             'id'          => "ingredient_tag_$ingredientTag->id",
                                                             'titles'      => $ingredientTag->ingredients->pluck('name'),
                                                             'count'       => $ingredientTag->ingredients_count,
                                                         ])
                    </td>

                    <td>
                        <a href="ingredient_tags/{{$ingredientTag->id}}/edit" class="btn btn-xs btn-primary"
                           title="Edit"
                           data-toggle="tooltip">
                            <i class="fas fa-pencil-alt"></i>
                        </a>

                        <form action="ingredient_tags/{{$ingredientTag->id}}/delete" method="POST"
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

        <div class="ingredient-tags-pagination d-flex justify-content-end">
            {{ $ingredientTags->links() }}
        </div>

    @else
        @lang('common.nothing_found')
    @endif
</div>
