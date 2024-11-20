@php $staplerName = $name . '_file_name'; @endphp

<div class="form-group form-element-customImage create-new_image"
     data-preview="#{{ $name }}"
     data-aspectRatio="{{ isset($aspect_ratio) ? $aspect_ratio : 0 }}"
     data-crop="{{ isset($crop) ? $crop : false }}">

    <label for="{{ $name }}" class="control-label create-new_label" style="display: none;">
        {!! $label !!}

        @if($required)
            <span class="form-element-required">*</span>
        @endif
    </label>

    <!-- Wrap the image or canvas element with a block element (container) -->
    <img id="mainImage"
         class="create-new_selected-img"
         width="70px"
         height="50px"
         src="{{ asset('/images/select_img.png') }}"/>

    <div class="form-group">
        <label class="btn btn-pink-full btn-file">
            {{ trans('common.choose_file') }}
            <input type="file" accept="image/*" id="uploadImage" class="hide">
            <input type="hidden" id="hiddenImage" name="{{ $name }}">
        </label>

        <div class="clearfix"></div>
        <p class="create-new_text">{{ trans('or drag an image here to upload it') }}</p>
    </div>
</div>