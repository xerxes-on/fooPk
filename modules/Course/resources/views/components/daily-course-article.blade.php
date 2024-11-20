@if($canRender)
    <article>
        <div class="mini_title">
            <h2>@lang('course::common.current_course')</h2>
        </div>
        <div class="news-item">
            <div class="news-item_title">
                <h3>
                    <a href="{{ route('articles.show', ['id' => $article['ID'], 'days' => $article['days']]) }}"
                       class="search-recipes_list_item_info_title">
                        {!! $article['post_title'] !!}
                    </a>
                </h3>
            </div>
            <div class="news-item_description">
                <p>{!! $article['post_excerpt'] !!}</p>
            </div>
            <div class="news-item_preview">
                <a href="{{ route('articles.show', ['id' => $article['ID'], 'days' => $article['days']]) }}"
                   class="search-recipes_list_item_info_title" style="width: 100%;">
                    <img src="{{ $articleImg }}" alt="{{strip_tags($article['post_title'])}}"/>
                </a>
            </div>
        </div>
    </article>
@endif
