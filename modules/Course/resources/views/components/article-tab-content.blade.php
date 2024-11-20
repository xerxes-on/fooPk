<div @class(['tab-pane', 'active' => $isActive]) id="{{ $id }}">
    @foreach($courseArticleData['articles'] as $day => $article)
        @php
            $articleImg = !empty($article['thumbnail_url_thumbnail']) ?
                            $article['thumbnail_url_thumbnail'] :
                            config('stapler.api_url') .'/150';
        @endphp
        <div class="search-recipes_list_item">
            @if($article['post_unlock'])
                <div class="search-recipes_list_item_img">
                    <a href="{{ route('articles.show', ['id' => $article['ID'], 'days' => $day]) }}"
                       class="article-image-link">
                        <img src="{{ $articleImg }}" alt="{!! $article['post_title'] !!}"/>
                    </a>
                </div>

                <div class="article-info">
                    <a href="{{ route('articles.show', ['id' => $article['ID'], 'days' => $day]) }}"
                       class="article-link">
                        {!! $article['post_title'] !!}
                    </a>
                    <div class="article-description">{!! $article['post_excerpt'] !!}</div>
                </div>
            @else
                <div class="search-recipes_list_item_img">
                    <div class="recipe-locked unlockable">
                        <div class="recipe-unlocked">
                            <span>@lang('course::common.day')</span> <b>{{ $day }}</b>
                        </div>
                    </div>
                    <img src="{{ $articleImg }}" alt="{!! $article['post_title'] !!}"/>
                </div>

                <div class="article-info">
                    <div class="article-link">{!! $article['post_title'] !!}</div>
                    <div class="article-description">{!! $article['post_excerpt'] !!}</div>
                </div>
            @endif
        </div>
    @endforeach
</div>
