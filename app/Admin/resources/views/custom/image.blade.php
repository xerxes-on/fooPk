<!-- ColorBox -->
<link rel="stylesheet" type="text/css" href="{{ mix('vendor/colorbox/colorboxTheme.css') }}">

<!-- jQuery and jQuery UI (REQUIRED) -->
<!--[if lt IE 9]>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!--<![endif]-->

@php
    $staplerName = "{$name}_file_name";
    $staplerUrl = empty($model->{$staplerName}) ? $model->$staplerName: $model->$name->url();
@endphp

<div class="form-group form-element-customImage image"
     data-preview="#{{ $name }}"
     data-aspectRatio="{{ $aspect_ratio ?? 0 }}"
     data-crop="{{ $crop ?? false }}">

    <label for="{{ $name }}" class="control-label">
        {!! $label !!}

        @if($required)
            <span class="form-element-required">*</span>
        @endif
    </label>

    {{-- Wrap the image or canvas element with a block element (container) --}}
    <div class="row">
        <div class="col-sm-6 mb-4">
            <img id="mainImage"
                 width="200px"
                 height="200px"
                 alt=""
                 src="{{ url($model->{$name}->url('thumb')) }}">
        </div>
    </div>

    <div class="btn-group">
        <label class="btn btn-primary btn-file m-0">
            @lang('common.choose_file')
            <input type="text" id="uploadImage" data-inputid="uploadImage" class="hide">
            <input type="hidden" id="hiddenImage" name="{{ $name }}">
            <input type="hidden" id="oldImage" name="oldImage" value="{{ $staplerUrl }}">
        </label>

        <button class="btn btn-danger"
                id="remove"
                type="button"
                aria-label="Remove Image"
                @if(empty($model->{$staplerName})) style="display: none;" @endif>
            <i class="fa fa-trash" aria-hidden="true"></i>
        </button>
    </div>
    {{-- ERROR MESSAGES --}}
    @if ($errors->get($name))
        <div class="form-element-errors mt-2">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{!! $error !!}</li>
                @endforeach
            </ul>
        </div>
    @endif
    {{-- HINT --}}
    @if (isset($hint))
        <p class="help-block"><small class="form-element-helptext">{!! $hint !!}</small></p>
    @endif
</div>
