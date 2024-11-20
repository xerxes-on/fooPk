@php $staplerName = $name . '_file_name'; @endphp

<div class="form-group form-element-customImage create-new_image"
     data-preview="#{{ $name }}"
     data-aspectRatio="{{ $aspect_ratio ?? 0 }}"
     data-crop="{{ $crop ?? false }}">

    <label for="{{ $name }}" class="control-label create-new_label" style="display: none;">
        {!! $label !!}

        @if($required)
            <span class="form-element-required">*</span>
        @endif
    </label>

    {{-- Wrap the image or canvas element with a block element (container) --}}
    <img id="mainImage"
         class="create-new_selected-img"
         width="70px"
         height="50px"
         alt="Post image"
         src="{{ empty($post->{$staplerName}) ? asset("/images/icons/person.svg") : route('post.image',['postId'=> $post->id, 'style' =>'thumb']) }}"/>

    <div class="form-group">
        <label class="btn btn-pink-full btn-file">
            @lang('common.choose_file')
            <input type="file" accept="image/*" id="uploadImage" class="hide" name="{{ $name }}">
            <input type="hidden" id="hiddenImage" name="{{ $name }}">
            <input type="hidden" id="oldImage" name="oldImage" value="{{ $post->{$staplerName} }}">
        </label>

        <div class="clearfix"></div>
        <p class="create-new_text">@lang('common.upload_image_placeholder')</p>

        <button class="btn btn-gray btn-close"
                id="remove"
                type="button" @if(empty($post->{$staplerName})) style="display: none;" @endif
                aria-label="Remove image">
        </button>
    </div>
</div>
