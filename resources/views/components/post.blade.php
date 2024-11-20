<h2 class="diary_posts_title">{{ $post->created_at->format('l, j F') }}</h2>
<div class="diary_posts_item">
    <div class="diary_posts_item_title">{{ $post->created_at->format('H:i') }}</div>

    <div class="diary_posts_item_state">
        <div class="diary_posts_item_state_actions">
            <div class="diary_posts_item_state_actions_item">
                <form action="{{ route('post.destroy', $post->id) }}" method="POST">
                    {{ method_field('DELETE') }}
                    {{ csrf_field() }}
                    <button type="submit"
                            class="diary_posts_item_state_actions_item_btn btn-with-icon btn-with-icon-delete"
                            aria-label="Delete post {{ $post->id }}"></button>
                </form>
            </div>

            <button type="button"
                    class="btn diary_posts_item_state_actions_item edit-post btn-with-icon btn-with-icon-edit"
                    data-post="{{ $post->id }}"
                    aria-label="Edit post {{ $post->id }}"></button>
        </div>
    </div>

    @if(!empty($post->image_file_name))
        <div class="diary_posts_item_img">
            <img src="{{ route('post.image',['postId'=> $post->id]) }}"
                 alt="{{ $post->created_at->format('l j. F, H:i') }}"
                 loading="lazy">
        </div>
    @endif

    <p>{{ $post->content }}</p>
</div>
