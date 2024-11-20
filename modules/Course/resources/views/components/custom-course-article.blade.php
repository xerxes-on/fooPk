@if( !is_null($article) && $article->isNotEmpty() )
    <article>
        <div class="mini_title">
            <h2>@lang('common.news')</h2>
        </div>

        <div class="news-item">
            <div class="news-item_title">
                <a href="{{ route('articles.list','#7ea5bf5fcd39719db507dc8cc22d9115') }}"
                   class="search-recipes_list_item_info_title">
                    <h3>{{ trans('common.defaultlable') }}</h3>
                </a>
            </div>
            <div class="news-item_description">
                <p>{!! $article['post_excerpt'] !!}</p>
            </div>
            <div class="news-item_preview">
                <a href="{{ route('articles.list','#7ea5bf5fcd39719db507dc8cc22d9115') }}"
                   class="search-recipes_list_item_info_title" style="width: 100%;">
                    <img src="{{ $articleImg }}" alt="{!! $article['post_title'] !!}"/>
                </a>
            </div>
        </div>
    </article>
@endif
