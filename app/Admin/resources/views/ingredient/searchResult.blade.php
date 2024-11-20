<div id="seach_result">
    @if(count($ingredients))
        <table class="table-primary table table-striped">
            <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:10%">@lang('common.name')</th>
                <th style="width:10%">@lang('common.category')</th>
                <th style="width:15%">@lang('common.diets')</th>
                <th style="width:15%">@lang('admin.tags')</th>
                <th style="width:5%">@lang('common.proteins')</th>
                <th style="width:5%">@lang('common.fats')</th>
                <th style="width:5%">@lang('common.carbohydrates')</th>
                <th style="width:5%">@lang('common.calories')</th>
                <th style="width:10%">@lang('common.unit')</th>
                <th style="width:10%">@lang('common.season')</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($ingredients as $ingredient)
                <tr>
                    <td>{{$ingredient->id}}</td>
                    <td>{{$ingredient->name}}</td>
                    <td>{{$ingredient->category->name}}</td>
                    <td>
                        @if(count($ingredient->category->diets))
                            @foreach($ingredient->category->diets as $diet)
                                <span class="label label-info">{{$diet->name}}</span>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        @if(count($ingredient->tags))
                            @foreach($ingredient->tags as $tag)
                                <span class="label label-info">{{$tag->title}}</span>
                            @endforeach
                        @endif
                    </td>
                    <td>{{$ingredient->proteins}}</td>
                    <td>{{$ingredient->fats}}</td>
                    <td>{{$ingredient->carbohydrates}}</td>
                    <td>{{$ingredient->calories}}</td>
                    <td>{{$ingredient->unit->full_name}}</td>
                    <td>
                        @if(count($ingredient->seasons))
                            @foreach($ingredient->seasons as $season)
                                <span class="label label-info">{{$season->name}}</span>
                            @endforeach
                        @endif
                    </td>
                    <td>
                        <a href="/admin/ingredients/{{$ingredient->id}}/edit" class="btn btn-xs btn-primary"
                           title="Edit"
                           data-toggle="tooltip">
                            <i class="fas fa-pencil-alt"></i>
                        </a>

                        <form action="ingredients/{{$ingredient->id}}/delete" method="POST"
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

        <div class="ingredients-pagination d-flex justify-content-end">
            {{ $ingredients->links() }}
        </div>
    @else
        @lang('common.nothing_found')
    @endif
</div>